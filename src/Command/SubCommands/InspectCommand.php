<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Command\SubCommands;

use Mamazu\SuluCliBundle\Object\Commands\CommandContext;
use Mamazu\SuluCliBundle\Services\PathToNodeConverterInterface;

class InspectCommand implements SubCommand
{
    public function __construct(
        private PathToNodeConverterInterface $pathToNodeConverter
    ) {
    }

    public function run(CommandContext $context): bool
    {
        if (!$this->pathToNodeConverter->getNodeId($context->getContentPath(), $context->getStage())) {
            $context->getStyle()->error('The current path does not point at a page with content');
            return false;
        }

        $context->getContentPath()->toggleInspection();

        return false;
    }


    public static function getCommand(): string
    {
        return 'inspect';
    }

    public static function getSubArguments(): array
    {
        return [];
    }

    public static function getDescription(): string
    {
        return 'Switches between navigating page tree to navigation content tree';
    }
}
