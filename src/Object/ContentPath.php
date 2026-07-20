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

    private function append(string $string): void
    {
        if ($string === '') {
            return;
        }

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
        if ($this->inspecting === false) {
            $this->propertyParts = [];
        }
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

    public function getRoute(): ?string
    {
        if (!array_key_exists(2, $this->routeParts)) {
            return null;
        }

        return implode('/', array_slice($this->routeParts, 2));
    }

    public function getPropertyPath(): string
    {
        return implode('/', $this->propertyParts);
    }

    /** @return array<string> */
    public function getPropertyPathParts(): array
    {
        return $this->propertyParts;
    }

    public function set(string $path): void
    {
        $propertyPath = null;
        if (str_starts_with($path, '/')) {
            // Absolute path, reset the thing
            $this->routeParts = [];
            $this->stopInspecting();

            $path = ltrim($path, '/');
        }

        if (str_contains($path, '|')) {
            [$path, $propertyPath] = explode('|', $path, 2);
        }

        $this->append($path);
        if (null !== $propertyPath) {
            $this->inspecting = true;
            $this->append($propertyPath);
        }
    }

    public function __toString(): string
    {
        return '/' . implode('/', $this->routeParts) . ($this->inspecting ? '|' : '') . $this->getPropertyPath();
    }
}
