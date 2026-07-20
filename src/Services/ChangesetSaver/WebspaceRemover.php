<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Services\ChangesetSaver;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Article\Domain\Model\Article;
use Sulu\Article\Infrastructure\Doctrine\Repository\ArticleRepository;
use Sulu\Snippet\Domain\Model\SnippetArea;
use Webmozart\Assert\Assert;

class WebspaceRemover implements WebspaceRemoverInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ArticleRepository $articleRepository,
    ) {
    }

    public function removeWebspace(string $webspace, string $stage): void
    {
        // Deleting page content
        $ids = $this->entityManager->createQueryBuilder()
            ->distinct()
            ->select('article.uuid')
            ->from(Article::class, 'article')
            ->leftJoin('article.dimensionContents', 'dimensionContent', 'WITH', 'dimensionContent.stage = :stage')
            ->leftJoin('dimensionContent.additionalWebspaces', 'additionalWebspace')
            ->andWhere('dimensionContent.mainWebspace = :webspaceKey OR additionalWebspace.additionalWebspace = :webspaceKey')
            ->setParameter('stage', $stage)
            ->setParameter('webspaceKey', $webspace)
            ->getQuery()
            ->getArrayResult()
        ;
        foreach ($ids as $id) {
            $ref = $this->entityManager->getReference(Article::class, $id);
            Assert::notNull($ref);

            $this->articleRepository->remove($ref);
        }

        // Deleting the snippet areas
        $this->entityManager->createQueryBuilder()
            ->delete(SnippetArea::class, 'area')
            ->where('area.webspaceKey', ':webspace')
            ->setParameter('webspace', $webspace)
            ->getQuery()
            ->execute()
        ;
    }
}
