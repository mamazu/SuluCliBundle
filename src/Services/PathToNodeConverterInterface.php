<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Services;

use Mamazu\SuluCliBundle\Object\ContentPath;

interface PathToNodeConverterInterface
{
    public function getNodeId(ContentPath $path, string $stage): ?int;

    /**
     * @return array<string, mixed>|null
     */
    public function getNodeContent(ContentPath $path, string $stage): ?array;
}
