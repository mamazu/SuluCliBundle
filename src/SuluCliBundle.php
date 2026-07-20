<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle;

use Doctrine\ORM\EntityManagerInterface;
use Mamazu\SuluCliBundle\Command\ContentCliCommand;
use Mamazu\SuluCliBundle\Command\SubCommands\ChangeDirectoryCommand;
use Mamazu\SuluCliBundle\Command\SubCommands\ClearCommand;
use Mamazu\SuluCliBundle\Command\SubCommands\DumpCommand;
use Mamazu\SuluCliBundle\Command\SubCommands\ExitCommand;
use Mamazu\SuluCliBundle\Command\SubCommands\ExitWithoutChanges;
use Mamazu\SuluCliBundle\Command\SubCommands\HelpCommand;
use Mamazu\SuluCliBundle\Command\SubCommands\InspectCommand;
use Mamazu\SuluCliBundle\Command\SubCommands\RemoveCommand;
use Mamazu\SuluCliBundle\Command\SubCommands\SaveCommand;
use Mamazu\SuluCliBundle\Command\SubCommands\SetCommand;
use Mamazu\SuluCliBundle\Command\SubCommands\ListCommand;
use Mamazu\SuluCliBundle\Services\ChangesetSaver;
use Mamazu\SuluCliBundle\Services\ChangesetSaverInterface;
use Mamazu\SuluCliBundle\Services\ListHandlers\ConsoleContentLister;
use Mamazu\SuluCliBundle\Services\ListHandlers\ListContentAtPath;
use Mamazu\SuluCliBundle\Services\ListHandlers\ListLocalesForContentPath;
use Mamazu\SuluCliBundle\Services\ListHandlers\ListSubRoutesForContentPath;
use Mamazu\SuluCliBundle\Services\ListHandlers\ListWebspacesForContentPath;
use Mamazu\SuluCliBundle\Services\PathToNodeConverter;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

class SuluCliBundle extends AbstractBundle
{
    /** @param array{} $config */
    public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $container): void
    {
        $services = $configurator->services();

        // Commands
        $services->set(ListCommand::class)
            ->args([
                new Reference(ConsoleContentLister::class),
            ])
            ->tag('sulu_cli.command')
        ;

        $services->set(ChangeDirectoryCommand::class)->tag('sulu_cli.command');
        $services->set(DumpCommand::class)->tag('sulu_cli.command');
        $services->set(ExitCommand::class)->tag('sulu_cli.command');
        $services->set(ExitCommand::class)->tag('sulu_cli.command');
        $services->set(ExitWithoutChanges::class)->tag('sulu_cli.command');

        $services->set(HelpCommand::class)->lazy()
            ->args([
                tagged_iterator('sulu_cli.command'),
            ])
            ->tag('sulu_cli.command')
        ;

        $services->set(InspectCommand::class)
            ->args([
                new Reference(PathToNodeConverter::class),
            ])
            ->tag('sulu_cli.command');

        $services->set(SetCommand::class)->tag('sulu_cli.command');
        $services->set(RemoveCommand::class)->tag('sulu_cli.command');
        $services->set(ClearCommand::class)->tag('sulu_cli.command');
        $services->set(SaveCommand::class)
            ->args([
                new Reference(ChangesetSaverInterface::class),
            ])
            ->tag('sulu_cli.command')
        ;

        $services
            ->set(ContentCliCommand::class)
            ->args([
                tagged_locator('sulu_cli.command', defaultIndexMethod:'getCommand'),
                new Reference(PathToNodeConverter::class),
            ])
            ->tag('console.command');

        // Services
        $services->set(ChangesetSaverInterface::class, ChangesetSaver::class)->args([
            new Reference(EntityManagerInterface::class),
        ]);

        $services->set(PathToNodeConverter::class)->args([
            new Reference(EntityManagerInterface::class),
        ]);

        $services->set(ConsoleContentLister::class)->args([
            new Reference(ListContentAtPath::class),
            new Reference(ListLocalesForContentPath::class),
            new Reference(ListSubRoutesForContentPath::class),
            new Reference(ListWebspacesForContentPath::class),
        ]);

        $services->set(ListContentAtPath::class)->args([
            new Reference(PathToNodeConverter::class),
        ]);

        $services->set(ListLocalesForContentPath::class)->args([
            new Reference(EntityManagerInterface::class),
        ]);

        $services->set(ListSubRoutesForContentPath::class)->args([
            new Reference(EntityManagerInterface::class),
        ]);

        $services->set(ListWebspacesForContentPath::class)->args([
            new Reference(WebspaceManagerInterface::class),
        ]);
    }
}
