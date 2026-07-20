<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Object\Changes;

use Countable;
use Mamazu\SuluCliBundle\Object\ContentPath;
use Mamazu\SuluCliBundle\Services\PathToNodeConverterInterface;

class ChangeSet implements Countable
{
    /** @var array<int, array<string, Change>|Change> $changes */
    private array $changes = [];

    /** @var array<string> $webspacesToBeRemoved */
    private array $webspacesToBeRemoved = [];

    public function __construct(
        private readonly PathToNodeConverterInterface $pathToNodeId,
    ) {}

    public function isEmpty(): bool
    {
        return [] === $this->changes && [] === $this->webspacesToBeRemoved;
    }

    public function clear(): void
    {
        $this->changes = [];
        $this->webspacesToBeRemoved = [];
    }

    public function add(ContentPath $path, string $stage, Change $change): void
    {
        $contentId = $this->pathToNodeId->getNodeId($path, $stage);
        if ($contentId === null) {
            return;
        }

        if (!$path->isInspecting()) {
            $this->changes[$contentId] = $change;
        } else if (!is_array($this->changes[$contentId] ?? null)) {
            // Page is already scheduled for deletion or move skip anything else
        } else {
            $this->changes[$contentId][$path->getPropertyPath()] = $change;
        }
    }

    public function removeWebspace(string $webspace): void
    {
        $this->webspacesToBeRemoved[] = $webspace;
    }

    /** @return array<string> */
    public function getWebspaces(): array
    {
        return $this->webspacesToBeRemoved;
    }

    /**
     * @return array<int,array<string,Change>|Change>
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    public function count(): int
    {
        return count($this->changes);
    }
}
