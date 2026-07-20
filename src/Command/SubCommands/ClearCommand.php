<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Command\SubCommands;

use Mamazu\SuluCliBundle\Object\Commands\CommandContext;

class ClearCommand implements SubCommand
{
    public function run(CommandContext $context): bool
    {
        $context->getChangeSet()->clear();

        return false;
    }

    public static function getCommand(): string
    {
        return 'clear';
    }

    public static function getSubArguments(): array
    {
        return [];
    }

    public static function getDescription(): string
    {
        return 'Clears unsaved changes that have been made';
    }

}
