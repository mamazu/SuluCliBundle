<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Object\Changes;

use Countable;
use Mamazu\SuluCliBundle\Object\ContentPath;
use Mamazu\SuluCliBundle\Services\PathToNodeConverter;

class ChangeSet implements Countable
{
    /** @var array<int, array<string, Change>|DeletePath> $changes */
    private array $changes = [];

    public function __construct(
        private readonly PathToNodeConverter $pathToNodeId,
    ) {}

    public function isEmpty(): bool
    {
        return [] === $this->changes;
    }

    public function clear(): void
    {
        $this->changes = [];
    }

    public function add(ContentPath $path, string $stage, Change $change): void
    {
        $contentId = $this->pathToNodeId->getNodeId($path, $stage);
        if ($contentId === null) {
            return;
        }

        if (!$path->isInspecting()) {
            $this->changes[$contentId] = new DeletePath();
        } else if ($this->changes[$contentId] instanceof DeletePath) {
            // Page is already scheduled for deletion skip anything else
        } else {
            $this->changes[$contentId][$path->getPropertyPath()] = $change;
        }
    }

    /**
     * @return array<int,array<string,Change>|DeletePath>
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
