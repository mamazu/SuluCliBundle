<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Services\ChangesetSaver;

use Doctrine\ORM\EntityManagerInterface;
use Mamazu\SuluCliBundle\Object\Changes\ChangeSet;
use Mamazu\SuluCliBundle\Object\Changes\DeletePath;
use Mamazu\SuluCliBundle\Object\Changes\SetValue;
use Sulu\Page\Domain\Model\PageDimensionContent;

final class ChangesetSaver implements ChangesetSaverInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WebspaceRemoverInterface $webspaceRemover,
    ) {}

    public function save(ChangeSet $changeSet, string $stage): void
    {
        foreach ($changeSet->getChanges() as $pageId => $changes) {
            /** @var PageDimensionContent|null $dimensionContent */
            $dimensionContent = $this->entityManager->find(PageDimensionContent::class, $pageId);
            if ($dimensionContent === null) {
                continue;
            }

            if (is_array($changes)) {
                $this->applyChanges($dimensionContent, $changes);
                $this->entityManager->flush();
                continue;
            }

            if ($changes instanceof DeletePath) {
                $this->entityManager->remove($dimensionContent);
            }

            $this->entityManager->flush();
        }

        foreach ($changeSet->getWebspaces() as $webspace) {
            $this->webspaceRemover->removeWebspace($webspace, $stage);
        }

        $this->entityManager->flush();
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
