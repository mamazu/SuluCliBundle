<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Services\ListHandlers;

use Mamazu\SuluCliBundle\Object\ContentPath;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\Webspace;

class ListWebspacesForContentPath implements ContentLister
{
    public function __construct(
        private readonly WebspaceManagerInterface $webspaceManager,
    ) {}

    public function getHeadline(): string
    {
        return 'Webspaces';
    }

    /** @return array<string> */
    public function listContent(ContentPath $path, string $stage): array
    {
        return array_map(
            fn(Webspace $webspace) => $webspace->getKey(),
            $this->webspaceManager->getWebspaceCollection()->getWebspaces(),
        );
    }
}
