<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Command\SubCommands;

use Mamazu\SuluCliBundle\Object\Commands\CommandContext;
use Mamazu\SuluCliBundle\Services\ListHandlers\ConsoleContentLister;

class ListCommand implements SubCommand
{
    public function __construct(
        private ConsoleContentLister $contentLister
    ) {
    }

    public function run(CommandContext $context): bool
    {
        $currentPath = $context->getContentPath();
        if ($currentPath !== '') {
            $currentPath->set($context->getSubCommandArguments());
        }

        $this->contentLister->listContent($context->getStyle(), $currentPath, $context->getStage());

        return false;
    }

    public static function getCommand(): string
    {
        return 'ls';
    }

    public static function getSubArguments(): array
    {
        return ['[path]'];
    }

    public static function getDescription(): string
    {
        return 'Shows content to navigate to';
    }

}
