<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Services\ListHandlers;

use Mamazu\SuluCliBundle\Object\ContentPath;
use Symfony\Component\Console\Style\StyleInterface;

class ConsoleContentLister
{
    public function __construct(
        private ListContentAtPath $listContent,
        private ListLocalesForContentPath $listLocales,
        private ListSubRoutesForContentPath $listRoutes,
        private ListWebspacesForContentPath $listWebspaces,
    ) {}

    public function listContent(StyleInterface $output, ContentPath $path, string $stage): void
    {
        if ($path->getWebspace() === null) {
            $this->printSection($output, $this->listWebspaces, $path, $stage);
        } else if ($path->getLocale() === null) {
            $this->printSection($output, $this->listLocales, $path, $stage);
        } else {
            $this->printSection($output, $this->listRoutes, $path, $stage);

            $this->printSection($output, $this->listContent, $path, $stage);
        }
    }

    private function printSection(StyleInterface $style, ContentLister $lister, ContentPath $path, string $stage): void
    {
        $content = $lister->listContent($path, $stage);
        if ($content === []) {
            return;
        }

        $style->title($lister->getHeadline());
        if (is_array($content)) {
            $style->listing($content);
        } else {
            $style->text($content);
        }
    }
}
