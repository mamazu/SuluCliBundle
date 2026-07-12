<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Services\ListHandlers;

use Doctrine\ORM\EntityManagerInterface;
use Mamazu\SuluCliBundle\Object\ContentPath;
use Sulu\Route\Domain\Model\Route;

class ListLocalesForContentPath implements ContentLister
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /** @return array<string> */
    public function listContent(ContentPath $path, string $stage): array
    {
        /** @var array<array{locale: string}> $locales */
        $locales = $this->entityManager
            ->createQueryBuilder()
            ->select('r.locale')
            ->distinct()
            ->from(Route::class, 'r')
            ->where('r.webspace = :webspace')
            ->setParameter('webspace', $path->getWebspace())
            ->getQuery()
            ->getArrayResult();

        return array_column($locales, 'locale');
    }

    public function getHeadline(): string
    {
        return 'Locales';
    }
}
