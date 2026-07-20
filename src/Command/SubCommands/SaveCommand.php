<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Command\SubCommands;

use Mamazu\SuluCliBundle\Object\Commands\CommandContext;
use Mamazu\SuluCliBundle\Services\ChangesetSaver\ChangesetSaverInterface;

class SaveCommand implements SubCommand
{
    public function __construct(
        private ChangesetSaverInterface $changesetSaver,
    ) {}

    public function run(CommandContext $context): bool
    {
        $changeSet = $context->getChangeSet();
        $style = $context->getStyle();

        $style->text('Saving ' . count($changeSet) . ' change(s)');
        $this->changesetSaver->save($changeSet, $context->getStage());

        $style->success('Saved!');

        return false;
    }

    public static function getCommand(): string
    {
        return 'save';
    }

    public static function getSubArguments(): array
    {
        return [];
    }

    public static function getDescription(): string
    {
        return 'Save unsaved changes';
    }
}
