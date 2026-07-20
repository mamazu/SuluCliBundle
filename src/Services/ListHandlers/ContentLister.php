<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Services\ListHandlers;

use Mamazu\SuluCliBundle\Object\ContentPath;

interface ContentLister
{
    public function getHeadline(): string;

    /** @return array<string>|string */
    public function listContent(ContentPath $path, string $stage): array|string;
}
