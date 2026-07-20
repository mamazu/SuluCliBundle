<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Mamazu\SuluCliBundle\Object\Changes\ChangeSet;
use Mamazu\SuluCliBundle\Object\Changes\DeletePath;
use Mamazu\SuluCliBundle\Object\Changes\SetValue;
use Sulu\Page\Domain\Model\PageDimensionContent;

class ChangesetSaver implements ChangesetSaverInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function save(ChangeSet $changeSet): void
    {
        foreach ($changeSet->getChanges() as $pageId => $changes) {
            $page = $this->entityManager->find(PageDimensionContent::class, $pageId);
            if ($page === null)
                continue;

            if (is_array($changes)) {
                $this->applyChanges($page, $changes);
            } else {
                $this->entityManager->remove($page);
            }

            $this->entityManager->flush();
        }
    }

    /**
     * @param array<string,mixed> $changes
     */
    private function applyChanges(PageDimensionContent $page, array $changes): void
    {
        $data = $page->getTemplateData();

        foreach ($changes as $path => $change) {
            $current = &$data;
            $pathParts = explode('/', $path);
            $lastPathPart = array_pop($pathParts);
            foreach ($pathParts as $pathPart) {
                if (!is_array($current[$pathPart] ?? null)) {
                    $current[$pathPart] = [];
                }
                $current = &$current[$pathPart];
            }

            if ($change instanceof DeletePath) {
                unset($current[$lastPathPart]);
            } else if ($change instanceof SetValue) {
                $current[$lastPathPart] = $change->value;
            }
        }

        $page->setTemplateData($data);
    }
}
