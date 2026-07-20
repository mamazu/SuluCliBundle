<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Object\Commands;

use Mamazu\SuluCliBundle\Object\Changes\ChangeSet;
use Mamazu\SuluCliBundle\Object\ContentPath;
use Mamazu\SuluCliBundle\Services\PathToNodeConverterInterface;
use Symfony\Component\Console\Style\StyleInterface;

class CommandContext
{
    private ContentPath $contentPath;
    private ChangeSet $changeSet;
    private string $subCommandArguments;

    public function __construct(
        private string $stage,
        private StyleInterface $style,
        PathToNodeConverterInterface $pathToNodeId,
    ) {
        $this->contentPath = new ContentPath();
        $this->changeSet = new ChangeSet($pathToNodeId);
    }

    public function getStage(): string
    {
        return $this->stage;
    }

    public function setSubCommandArguments(string $args): void
    {
        $this->subCommandArguments = $args;
    }

    public function getSubCommandArguments(): string
    {
        return $this->subCommandArguments;
    }

    public function getContentPath(): ContentPath
    {
        return $this->contentPath;
    }

    public function getChangeSet(): ChangeSet
    {
        return $this->changeSet;
    }

    public function getStyle(): StyleInterface
    {
        return $this->style;
    }
}
