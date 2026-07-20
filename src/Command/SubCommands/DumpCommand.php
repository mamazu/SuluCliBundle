<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Command\SubCommands;

use Mamazu\SuluCliBundle\Object\Commands\CommandContext;

class DumpCommand implements SubCommand
{
    public function run(CommandContext $context): bool
    {
        $context->getStyle()->title('Changes');
        dump($context->getChangeSet()->getChanges());

        $context->getStyle()->title('Webspaces to delete');
        dump($context->getChangeSet()->getWebspaces());
        return false;
    }

    public static function getCommand(): string
    {
        return 'dump';
    }

    public static function getSubArguments(): array
    {
        return [];
    }

    public static function getDescription(): string
    {
        return 'Dumps unsaved changes';
    }
}
