<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Command\SubCommands;

use Mamazu\SuluCliBundle\Object\Commands\CommandContext;

class ExitCommand implements SubCommand
{
    public function run(CommandContext $context): bool
    {
        if ($context->getChangeSet()->isEmpty()) {
            $style = $context->getStyle();
            $style->warning(sprintf('You have %d changes that were not yet saved.', count($context->getChangeSet())));
            $style->error('You can\'t leave with pending changes. Either "save" them or use "exit!"');

            return false;
        }

        return true;
    }

    public static function getCommand(): string
    {
        return 'exit';
    }

    public static function getSubArguments(): array
    {
        return [];
    }

    public static function getDescription(): string
    {
        return 'Exit program';
    }
}
