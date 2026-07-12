<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Services\ListHandlers;

use Doctrine\ORM\EntityManagerInterface;
use Mamazu\SuluCliBundle\Object\ContentPath;
use Sulu\Route\Domain\Model\Route;
use Webmozart\Assert\Assert;

class ListSubRoutesForContentPath implements ContentLister
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /** @return array<string> */
    public function listContent(ContentPath $path, string $stage): array
    {
        if ($path->isInspecting()) {
            return [];
        }

        /** @var array<array{slug: string}> $routeSlugs */
        $routeSlugs = $this->entityManager
            ->createQueryBuilder()
            ->select('r.slug')
            ->distinct()
            ->from(Route::class, 'r')
            ->where('r.webspace = :webspace')
            ->andWhere('r.locale = :locale')
            ->andWhere('r.slug LIKE :route')
            ->setParameter('webspace', $path->getWebspace())
            ->setParameter('route', '/' . ($path->getRoute() ?? '') . '_%')
            ->setParameter('locale', $path->getLocale())
            ->getQuery()
            ->getArrayResult();

        return array_column($routeSlugs, 'slug');
    }

    public function getHeadline(): string
    {
        return 'Routes';
    }
}
