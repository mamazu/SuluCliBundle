<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle;

use Doctrine\ORM\EntityManagerInterface;
use Mamazu\SuluCliBundle\Command\ContentCliCommand;
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

class SuluCliBundle extends AbstractBundle
{
    /** @param array{} $config */
    public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $container): void
    {
        $services = $configurator->services();
        $services
            ->set(ContentCliCommand::class)
            ->args([
                new Reference(ConsoleContentLister::class),
                new Reference(ChangesetSaverInterface::class),
                new Reference(PathToNodeConverter::class),
            ])
            ->tag('console.command');

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
