<?php

declare(strict_types=1);

namespace Tests\Mamazu\SuluCliBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Mamazu\SuluCliBundle\Object\Changes\ChangeSet;
use Mamazu\SuluCliBundle\Object\Changes\DeletePath;
use Mamazu\SuluCliBundle\Object\Changes\SetValue;
use Mamazu\SuluCliBundle\Services\ChangesetSaver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Page\Domain\Model\PageDimensionContent;

final class ChangesetSaverTest extends TestCase
{
    private const PAGE_ID = 1122;

    private MockObject&EntityManagerInterface $entityManager;

    private ChangesetSaver $changeSetSaver;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->changeSetSaver = new ChangesetSaver($this->entityManager);
    }

    public function testSavingNoChangesChangesNothing(): void
    {
        $changeSet = $this->createConfiguredStub(ChangeSet::class, ['getChanges' => []]);
        $this->entityManager->expects(self::never())->method('find');
        $this->entityManager->expects(self::never())->method('flush');

        $this->changeSetSaver->save($changeSet);
    }

    #[DataProvider('dataChanges')]
    public function testSavingChanges(ChangeSet $changeSet, array $before, array $after): void
    {
        $page = $this->createMock(PageDimensionContent::class);
        $page->expects($this->once())->method('getTemplateData')->willReturn($before);
        $page->expects($this->once())
            ->method('setTemplateData')
            ->with(new Callback(function (array $array) use ($after) {
                return var_export($array, true) === var_export($after, true);
            }));

        $this->entityManager->expects(self::once())
            ->method('find')
            ->with(PageDimensionContent::class, self::PAGE_ID)
            ->willReturn($page);
        $this->entityManager->expects(self::once())->method('flush');

        $this->changeSetSaver->save($changeSet);
    }

    public static function dataChanges(): Generator
    {
        yield 'delete changes' => [
            self::createChangeSet([
                'foo' => new DeletePath(),
            ]),
            ['test' => 'Something', 'foo' => 'other'],
            ['test' => 'Something'],
        ];

        yield 'update changes' => [
            self::createChangeSet([
                'test' => new SetValue(122),
            ]),
            ['test' => 'Something'],
            ['test' => 122],
        ];

        yield 'update nested changes' => [
            self::createChangeSet([
                'test/foo' => new SetValue(123),
            ]),
            ['test' => ['foo' => 'Something']],
            ['test' => ['foo' => 123]],
        ];

        yield 'create changes' => [
            self::createChangeSet([
                'hallo' => new SetValue(true),
            ]),
            ['test' => 12],
            ['test' => 12, 'hallo' => true],
        ];

        yield 'create changes (with existing path)' => [
            self::createChangeSet([
                'hallo/test' => new SetValue(true),
            ]),
            ['hallo' => '123'],
            ['hallo' => ['test' => true]],
        ];

        yield 'create changes (with recursive path)' => [
            self::createChangeSet([
                'hallo/test/banana/wurst' => new SetValue(true),
            ]),
            ['hallo' => '123'],
            ['hallo' => ['test' => ['banana' => ['wurst' => true]]]],
        ];
    }

    private static function createChangeSet(array $changes): ChangeSet
    {
        return self::createConfiguredStub(ChangeSet::class, ['getChanges' => [
            self::PAGE_ID => $changes
        ]]);
    }
}
