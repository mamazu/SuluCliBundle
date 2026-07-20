<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Command\SubCommands;

use Mamazu\SuluCliBundle\Object\Commands\CommandContext;

class ExitWithoutChanges implements SubCommand
{
    public function run(CommandContext $context): bool
    {
        return true;
    }

    public static function getCommand(): string
    {
        return 'exit!';
    }

    public static function getSubArguments(): array
    {
        return [];
    }

    public static function getDescription(): string
    {
        return 'Exits without saving changes';
    }
}
