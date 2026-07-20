<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Command\SubCommands;

use Mamazu\SuluCliBundle\Object\Commands\CommandContext;

class ChangeDirectoryCommand implements SubCommand
{
    public function run(CommandContext $context): bool
    {
        $args = $context->getSubCommandArguments();
        if ($args === '') {
            $context->getStyle()->error('"cd" requires an argument');

            return false;
        }

        $context->getContentPath()->set($args);

        return false;
    }

    public static function getCommand(): string
    {
        return 'cd';
    }

    public static function getSubArguments(): array
    {
        return ['<path>'];
    }

    public static function getDescription(): string
    {
        return 'Navigate current tree (content or page tree)';
    }

}
