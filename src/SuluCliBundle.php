<?php
declare(strict_types=1);

namespace Mamazu\SuluCliBundle;

use Doctrine\ORM\EntityManagerInterface;
use Mamazu\SuluCliBundle\Command\ContentCliCommand;
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
                new Reference(WebspaceManagerInterface::class),
                new Reference(EntityManagerInterface::class),
            ])
            ->tag('console.command')
        ;
    }
}

