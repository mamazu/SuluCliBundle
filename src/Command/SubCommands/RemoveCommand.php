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
        $style = $context->getStyle();

        if ($answer === '') {
            $context->getStyle()->error('rm requires an argument');
            return false;
        }

        $removePath = clone $context->getContentPath();
        $removePath->set($answer);

        if ($removePath->getRoute() === null
            && $removePath->getLocale() === null
            && $removePath->getWebspace() !== null
        ) {
            $confirm = $context->getStyle()->confirm('You\'re trying to remove a webspace. Are you sure', true);
            if (!$confirm) {
                $style->note('Skipping');
                return false;
            }
            $context->getChangeSet()->removeWebspace($removePath->getWebspace());
        }

        $context->getChangeSet()->add($removePath, $context->getStage(), new DeletePath());

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
