<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Mamazu\SuluCliBundle\Object\ContentPath;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\Webspace;
use Sulu\Page\Domain\Model\PageDimensionContent;
use Sulu\Route\Domain\Model\Route;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'sulu:content:cli', description: 'Provides an interactive command line to edit sulu content')]
class ContentCliCommand extends Command
{
/**
    private const HELP_TEXT = <<<TXT
        ## Navigation

        <comment>ls [path]</comment> # Show content to navigate to (webspaces, paths and properties)
        <comment>cd <path></comment> # Change directory

        ## Editing

        <comment>set <path> <value></comment> # Updates the value (not yet saved)
        <comment>set <path></comment> # Removes a value (not yet saved)
        <comment>save</comment> # Save changes to the database

        ## Leaving

        <comment>exit</comment> # Exit program
        <comment>exit!</comment> # Exit program without saving
        TXT;
*/

    private string $stage;

    public function __construct(
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addArgument('stage', InputArgument::REQUIRED, 'Stage of the content');
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $this->stage = (string) $input->getArgument('stage');

        if (!$input->isInteractive()) {
            $output->writeln('This is an interactive command line. Maybe there is going to be a script interface later');
            return Command::SUCCESS;
        }

        $questionHelper = $this->getHelper('question');
        $path = new ContentPath();
        $shell = new Shell($output);
        $changes = [];

        foreach ($shell->run() as $answer) {
            if ($answer === '' || $answer === 'exit!') {
                break;
            }

            if ($answer === 'exit') {
                if ([] !== $changes) {
                    $output->writeln('<error>You can\'t leave with pending changes. Either "save" them or use "exit!"</error>');
                    continue;
                }
                break;
            }

            if ($answer === 'help') {
                $this->printHelp($output);
                continue;
            }

            if ($answer === 'inspect') {
                // TODO: check if page exists

                $path->toggleInspection();
                $shell->setPrompt((string) $path);

                continue;
            }

            if (str_starts_with($answer, 'set')) {
                if (!str_contains($answer, ' ')) {
                    $output->writeln('<error>set requires an argument</error>');
                    continue;
                }

                [$_, $property, $value] = explode(' ', $answer, 3);
                $changes[$path->__toString().'/'.$property] = $value;
            }

            if (str_starts_with($answer, 'rm')) {
                if (!str_contains($answer, ' ')) {
                    $output->writeln('<error>set requires an argument</error>');
                    continue;
                }

                // todo: implement this
                $output->writeln('<error>Removing things is currently not implemented');
            }

            if ($answer === 'save') {
                // todo: Save changes to the disk
                $output->writeln('<error>Not implemented</error>');
                continue;
            }

            if (str_starts_with($answer, 'cd')) {
                if (!str_contains($answer, ' ')) {
                    $output->writeln('<error>cd requires an argument</error>');
                    continue;
                }

                [$_, $subdirectory] = explode(' ', $answer, 2);

                $path->set($subdirectory);
                $shell->setPrompt((string) $path);
                continue;
            }

            if (str_starts_with($answer, 'ls')) {
                $currentPath = clone $path;
                if (str_contains($answer, ' ')) {
                    [$_, $subdirectory] = explode(' ', $answer, 2);
                    $path->set($subdirectory);
                }

                if ($path->getWebspace() === null) {
                    $this->listWebspaces($output);
                } else if ($path->getLocale() === null) {
                    $this->listLocales($output, $path);
                } else {
                    $this->listRoutes($output, $path);
                    $this->listContent($output, $path);
                }
                $path = $currentPath;
                continue;
            }

            $output->writeln('<error>Unknown command: "'.$answer.'"</error>');
        }

        return Command::SUCCESS;
    }

    private function listWebspaces(OutputInterface $output): void
    {
        $output->writeln('<comment>== Webspaces ==</comment>');
        $webspaceKeys = array_map(
            fn (Webspace $webspace) => $webspace->getKey(),
            $this->webspaceManager->getWebspaceCollection()->getWebspaces(),
        );
        foreach ($webspaceKeys as $webspaceKey) {
            $output->writeln('* '.$webspaceKey);
        }
    }

    private function listContent(OutputInterface $output, ContentPath $path): void
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->distinct()
            ->select('p.templateData')
            ->from(PageDimensionContent::class, 'p')
            ->join('p.route', 'r')
            ->where('r.webspace = :webspace')
            ->andWhere('r.locale = :locale')
            ->andWhere('r.slug = :route')
            ->andWhere('p.stage = :stage')
            ->setParameter('webspace', $path->getWebspace())
            ->setParameter('route', '/'.$path->getRoute())
            ->setParameter('locale', $path->getLocale())
            ->setParameter('stage', $this->stage);

        if (!$path->isInspecting()) {
            $count = $queryBuilder->select('p.id') ->getQuery() ->getSingleScalarResult() ;
            $output->writeln('<comment>There are also properties. To see them use "inspect"</comment>');

            return;
        }

        /** @var array{templateData: array<mixed>} $array */
        $array = $queryBuilder->getQuery() ->getSingleResult();
        $templateData = $this->iteratePath($array['templateData'], $path);

        if (is_array($templateData)) {
            $output->writeln('<comment>== Properties ==</comment>');
            foreach ($templateData as $key => $value) {
                $value = is_array($value) ? '<comment>..Expand for val..Expand for value..</comment>' : var_export($value, true);
                $output->writeln('* '.$key .' = '. $value);
            }
        } else {
            $output->writeln('<comment>== Value ==</comment>');
            $output->writeln(var_export($templateData, true));
        }
        $output->writeln('');

        $output->writeln('<info>When you are done inspecting, run "inspect" to get back to the route selection.</info>');
    }

    private function iteratePath(array $data, ContentPath $path): mixed
    {
        $currentData = $data;
        foreach ($path->getContentPath() as $part) {
            $currentData = $currentData[$part];
        }
        return $currentData;
    }

    private function listRoutes(OutputInterface $output, ContentPath $path): void
    {
        if ($path->isInspecting()) {
            return;
        }

        $routeSlugs = $this->entityManager->createQueryBuilder()
            ->select('r.slug')
            ->distinct()
            ->from(Route::class, 'r')
            ->where('r.webspace = :webspace')
            ->andWhere('r.locale = :locale')
            ->andWhere('r.slug LIKE :route')
            ->setParameter('webspace', $path->getWebspace())
            ->setParameter('route', '/'.$path->getRoute().'_%')
            ->setParameter('locale', $path->getLocale())
            ->getQuery()
            ->getArrayResult()
        ;

        if ([] !== $routeSlugs) {
            $output->writeln('<comment>== Routes ==</comment>');
            foreach ($routeSlugs as $routeSlug) {
                $output->writeln('* '.ltrim($routeSlug['slug'], '/'));
            }
        }
    }

    private function listLocales(OutputInterface $output, ContentPath $path): void {
        $output->writeln('<comment>== Locales in '.$path->getWebspace() .' ==</comment>');

        $routeSlugs = $this->entityManager->createQueryBuilder()
            ->select('r.locale')
            ->distinct()
            ->from(Route::class, 'r')
            ->where('r.webspace = :webspace')
            ->setParameter('webspace', $path->getWebspace())
            ->getQuery()
            ->getArrayResult()
        ;

        foreach ($routeSlugs as $routeSlug) {
            $output->writeln('* '.$routeSlug['locale']);
        }
    }

    public function printHelp(OutputInterface $output): void
    {
    //    $output->writeln(self::HELP_TEXT);
    }
}

