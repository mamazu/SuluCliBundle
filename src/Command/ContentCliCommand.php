<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Command;

use Mamazu\SuluCliBundle\Object\Changes\ChangeSet;
use Mamazu\SuluCliBundle\Object\Changes\DeletePath;
use Mamazu\SuluCliBundle\Object\Changes\SetValue;
use Mamazu\SuluCliBundle\Object\ContentPath;
use Mamazu\SuluCliBundle\Services\ChangesetSaverInterface;
use Mamazu\SuluCliBundle\Services\ListHandlers\ConsoleContentLister;
use Mamazu\SuluCliBundle\Services\PathToNodeConverter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'sulu:content:cli', description: 'Provides an interactive command line to edit sulu content')]
class ContentCliCommand extends Command
{
    private const HELP_TEXT = <<<TXT
        ## Navigation

        <comment>ls [path]</comment> # Show content to navigate to (webspaces, paths and properties)
        <comment>cd <path></comment> # Change directory

        ## Editing

        <comment>set <path> <value></comment> # Updates the value (not yet saved)
        <comment>rm <path></comment> # Removes a value (not yet saved)
        <comment>save</comment> # Save changes to the database

        ## Leaving

        <comment>exit</comment> # Exit program
        <comment>exit!</comment> # Exit program without saving
        TXT;

    private string $stage;

    public function __construct(
        private readonly ConsoleContentLister $contentLister,
        private readonly ChangesetSaverInterface $changesetSaver,
        private readonly PathToNodeConverter $pathToNodeConverter,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addArgument('stage', InputArgument::REQUIRED, 'Stage of the content');
        $this->addOption('no-cache', null, InputOption::VALUE_NONE, 'No cache will be used when updating nodes');
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $this->stage = (string) $input->getArgument('stage');

        if (!$input->isInteractive()) {
            $output->writeln(
                'This is an interactive command line. Maybe there is going to be a script interface later',
            );
            return Command::SUCCESS;
        }

        $questionHelper = $this->getHelper('question');
        $path = new ContentPath();
        $shell = new Shell($output, $path);
        $changeSet = new ChangeSet($this->pathToNodeConverter);
        $style = new SymfonyStyle($input, $output);

        foreach ($shell->run() as $answer) {
            if ($answer === 'exit!') {
                break;
            }

            if ($answer === 'exit') {
                if ($changeSet->isEmpty()) {
                    $output->writeln(sprintf(
                        '<info>You have %d changes that were not yet saved.</info>',
                        count($changeSet),
                    ));
                    $output->writeln(
                        '<error>You can\'t leave with pending changes. Either "save" them or use "exit!"</error>',
                    );
                    continue;
                }

                break;
            }

            if ($answer === 'help') {
                $this->printHelp($output);
                continue;
            }

            if ($answer === 'dump') {
                dump($changeSet->getChanges());
                continue;
            }

            if ($answer === 'inspect') {
                if (!$this->pathToNodeConverter->getNodeId($path, $this->stage)) {
                    $output->writeln('<error>The current path does not point at a page with content</error>');
                    continue;
                }

                $path->toggleInspection();

                continue;
            }

            if (str_starts_with($answer, 'set')) {
                if (!str_contains($answer, ' ')) {
                    $output->writeln('<error>set requires an argument</error>');
                    continue;
                }

                [$_, $property, $value] = explode(' ', $answer, 3);

                $setPath = clone $path;
                $setPath->set($property);

                $changeSet->add($setPath, $this->stage, new SetValue($value));

                continue;
            }

            if (str_starts_with($answer, 'rm')) {
                if (!str_contains($answer, ' ')) {
                    $output->writeln('<error>set requires an argument</error>');
                    continue;
                }

                [$_, $property] = explode(' ', $answer, 2);

                $setPath = clone $path;
                $setPath->set($property);
                $changeSet->add($setPath, $this->stage, new DeletePath());

                continue;
            }

            if ($answer === 'save') {
                $output->writeln('Saving ' . count($changeSet) . ' change(s)');
                $this->changesetSaver->save($changeSet);

                $output->writeln('<success>Saved!</success>');
                continue;
            }

            if (str_starts_with($answer, 'cd')) {
                if (!str_contains($answer, ' ')) {
                    $output->writeln('<error>cd requires an argument</error>');
                    continue;
                }

                [$_, $subdirectory] = explode(' ', $answer, 2);

                $path->set($subdirectory);
                continue;
            }

            if (str_starts_with($answer, 'ls')) {
                $currentPath = clone $path;
                if (str_contains($answer, ' ')) {
                    [$_, $subdirectory] = explode(' ', $answer, 2);
                    $path->set($subdirectory);
                }

                $this->contentLister->listContent($style, $path, $this->stage);

                $path = $currentPath;
                continue;
            }

            $style->error('Unknown command: "' . $answer . '"');
        }

        return Command::SUCCESS;
    }

    public function printHelp(OutputInterface $output): void
    {
        $output->writeln(self::HELP_TEXT);
    }
}
