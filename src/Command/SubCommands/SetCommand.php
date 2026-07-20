<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Command\SubCommands;
use Mamazu\SuluCliBundle\Object\Changes\SetValue;

use Mamazu\SuluCliBundle\Object\Commands\CommandContext;

class SetCommand implements SubCommand
{
    public function run(CommandContext $context): bool
    {
        $args = $context->getSubCommandArguments();
        $style = $context->getStyle();
        if (substr_count($args, ' ') < 2) {
            $style->error('"set" requires two arguments');
            return false;
        }

        [$property, $value] = explode(' ', $args, 2);

        // Save the old path
        $setPath = $context->getContentPath();
        $revertingChanges = (string) $setPath;

        // Append the path you want to modify
        $setPath->set($property);

        // Check if the path is now pointing to a property path
        if (!$setPath->isInspecting()) {
            $style->error('Can not set pages');
            return false;
        }

        $value = trim($value);
        $jsonDecoded = json_decode($value);
        if ($jsonDecoded !== null || strtolower($value) === 'null') {
            $style->note('Updated value was json_decoded');
            $value = $jsonDecoded;
        }

        $context->getChangeSet()->add($setPath, $context->getStage(), new SetValue($value));

        // Reverting back to the path before the set command
        $setPath->set($revertingChanges);

        return false;
    }

    public static function getCommand(): string
    {
        return 'set';
    }

    public static function getSubArguments(): array
    {
        return ['<path>', '<value>'];
    }

    public static function getDescription(): string
    {
        return 'Set value in a structure';
    }
}
