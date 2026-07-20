<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Command;

use Mamazu\SuluCliBundle\Command\SubCommands\SubCommand;
use Mamazu\SuluCliBundle\Object\Commands\CommandContext;
use Mamazu\SuluCliBundle\Services\PathToNodeConverter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Webmozart\Assert\Assert;

#[AsCommand(name: 'sulu:content:cli', description: 'Provides an interactive command line to edit sulu content')]
class ContentCliCommand extends Command
{

    private string $stage;

    /**
    * @param ServiceLocator<SubCommand> $subCommands
    */
    public function __construct(
        private readonly ServiceLocator $subCommands,
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
        $style = new SymfonyStyle($input, $output);
        $context = new CommandContext(
            $this->stage,
            $style,
            $this->pathToNodeConverter,
        );
        $shell = new Shell($output, $context->getContentPath());

        foreach ($shell->run() as $answer) {
            $command = $answer;
            $subCommands = '';
            $position = strpos($answer, ' ');
            if (is_int($position)){
                $command = substr($command, 0, $position);
                $subCommands = substr($answer, $position + 1);
            }

            if (!$this->subCommands->has($command)) {
                $style->error('Unknown command: "' . $command. '"');
                continue;
            }

            $context->setSubCommandArguments($subCommands);
            $subCommand = $this->subCommands->get($command);
            Assert::isInstanceOf($subCommand, SubCommand::class);
            $subCommand->run($context);
        }

        return Command::SUCCESS;
    }
}
