<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Mamazu\SuluCliBundle\Object\ContentPath;
use Sulu\Page\Domain\Model\PageDimensionContent;

final class PathToNodeConverter implements PathToNodeConverterInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function getNodeId(ContentPath $path, string $stage): ?int
    {
        try {
            /** @var int $id */
            $id = $this->entityManager
                ->createQueryBuilder()
                ->distinct()
                ->select('p.id')
                ->from(PageDimensionContent::class, 'p')
                ->join('p.route', 'r')
                ->where('r.webspace = :webspace')
                ->andWhere('r.locale = :locale')
                ->andWhere('r.slug = :route')
                ->andWhere('p.stage = :stage')
                ->setParameter('webspace', $path->getWebspace())
                ->setParameter('route', '/' . ($path->getRoute() ?? ''))
                ->setParameter('locale', $path->getLocale())
                ->setParameter('stage', $stage)
                ->getQuery()
                ->getSingleScalarResult();

            return $id;
        } catch (NoResultException) {
            return null;
        }
    }

    public function getNodeContent(ContentPath $path, string $stage): ?array
    {
        try {
            /** @var array<mixed> $templateData */
            $templateData = $this->entityManager
                ->createQueryBuilder()
                ->distinct()
                ->select('p.templateData')
                ->from(PageDimensionContent::class, 'p')
                ->join('p.route', 'r')
                ->where('r.webspace = :webspace')
                ->andWhere('r.locale = :locale')
                ->andWhere('r.slug = :route')
                ->andWhere('p.stage = :stage')
                ->setParameter('webspace', $path->getWebspace())
                ->setParameter('route', '/' . ($path->getRoute() ?? ''))
                ->setParameter('locale', $path->getLocale())
                ->setParameter('stage', $stage)
                ->getQuery()
                ->getSingleResult();

            return $templateData;
        } catch (NoResultException) {
            return null;
        }
    }
}
