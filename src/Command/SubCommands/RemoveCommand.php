<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Command\SubCommands;

use Mamazu\SuluCliBundle\Object\Changes\DeletePath;
use Mamazu\SuluCliBundle\Object\Commands\CommandContext;

class RemoveCommand implements SubCommand
{
    public function run(CommandContext $context): bool
    {
        $answer = $context->getSubCommandArguments();
        if ($answer === '') {
            $context->getStyle()->error('rm requires an argument');
            return false;
        }

        $setPath = clone $context->getContentPath();
        $setPath->set($answer);
        $context->getChangeSet()->add($setPath, $context->getStage(), new DeletePath());

        return false;
    }

    public static function getCommand(): string
    {
        return 'rm';
    }

    public static function getSubArguments(): array
    {
        return ['<path>'];
    }

    public static function getDescription(): string
    {
        return 'Removes a value or a node';
    }
}
