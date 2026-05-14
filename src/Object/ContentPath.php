<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Object;

class ContentPath
{
    /** @var array<string> $routeParts */
    private array $routeParts = [];

    private bool $inspecting = false;

    /** @var array<string> $propertyParts */
    private array $propertyParts = [];

    public function append(string $string): void
    {
        $parts = explode('/', $string);

        if ($this->inspecting) {
            $target = &$this->propertyParts;
        } else {
            $target = &$this->routeParts;
        }
        foreach ($parts as $part) {
            if ($part === '..') {
                array_pop($target);
            } else {
                $target[] = $part;
            }
        }
    }

    public function toggleInspection(): void
    {
        $this->inspecting = !$this->inspecting;
    }

    public function stopInspecting(): void
    {
        $this->inspecting = false;
        $this->propertyParts = [];
    }

    public function isInspecting(): bool
    {
        return $this->inspecting;
    }

    public function getWebspace(): ?string
    {
        return $this->routeParts[0] ?? null;
    }

    public function getLocale(): ?string
    {
        return $this->routeParts[1] ?? null;
    }

    public function getContentPath(): array
    {
        return $this->propertyParts;
    }

    public function getRoute(): ?string
    {
        if (!array_key_exists(2, $this->routeParts)) {
            return null;
        }

        return implode('/', array_slice($this->routeParts, 2));
    }

    public function set(string $path): void
    {
        if (str_starts_with($path, '/')) {
            // Absolute path, reset the thing
            $this->routeParts = [];
            $this->stopInspecting();

            $path = ltrim($path, '/');
        }

        $this->append($path);
    }

    public function __toString(): string
    {
        return '/'.implode('/', $this->routeParts) . ($this->inspecting ? '|' :''). implode('/', $this->propertyParts);
    }
}
