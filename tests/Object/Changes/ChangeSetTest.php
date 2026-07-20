<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Object\Changes;

use Mamazu\SuluCliBundle\Object\ContentPath;
use Mamazu\SuluCliBundle\Services\PathToNodeConverterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChangeSetTest extends TestCase
{
    private ChangeSet $changeSet;

    private MockObject&PathToNodeConverterInterface $nodePathConverter;


    public function setUp(): void
    {
        $this->nodePathConverter = $this->createMock(PathToNodeConverterInterface::class);
        $this->changeSet = new ChangeSet($this->nodePathConverter);
    }

    public function testClearedSetIsEmpty(): void
    {
        $this->changeSet->clear();
        $this->assertTrue($this->changeSet->isEmpty());
    }

    public function testSkipAddingInvalidContentPath(): void
    {
        $contentPath = new ContentPath('/webspace/de/test');

        $this->nodePathConverter
            ->expects(self::once())
            ->method('getNodeId')->with($contentPath, 'stage')
            ->willReturn(null);

        $this->changeSet->add($contentPath, 'stage', new SetValue('hallo'));
        $this->assertCount(0, $this->changeSet);
    }

    public function testIgnoreSetOnDeletedNode(): void
    {
        $contentPath = new ContentPath('/webspace/de/test');
        $contentPathWithProperties = new ContentPath('/webspace/de/test|title');

        $this->nodePathConverter
            ->expects(self::exactly(2))
            ->method('getNodeId')
            ->willReturnMap([
                [$contentPath, 'stage', 12],
                [$contentPathWithProperties, 'stage', 12],
            ]);

        $this->changeSet->add($contentPathWithProperties, 'stage', new SetValue('somevalue'));
        $this->changeSet->add($contentPath, 'stage', new DeletePath());

        $this->assertCount(1, $this->changeSet);
    }
}
