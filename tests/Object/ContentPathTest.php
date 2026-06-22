<?php
declare(strict_types=1);

use Mamazu\SuluCliBundle\Object\ContentPath;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ContentPathTest extends TestCase
{
    private ContentPath $path;

    public function setUp():void
    {
        $this->path = new ContentPath();
    }

    public function testToString(): void
    {
        $this->assertSame('/', $this->path->__toString());
    }

    #[DataProvider('dataPrintItself')]
    public function testPrintItself(string $path): void
    {
        $this->path->set($path);
        $this->assertSame($path, $this->path->__toString());
    }

    /**
    * @return \Generator<string, array{string}>
    */
    public static function dataPrintItself(): \Generator
    {
        yield 'only webspace' => ['/test-webspace'];
        yield 'german webspace' => ['/test-webspace/de'];
        yield 'some route' => ['/test-webspace/de/homepage'];
    }

    public function testInspection(): void
    {
        $this->path->set('/test-webspace/de/some-page');
        $this->path->toggleInspection();

        $this->assertSame('/test-webspace/de/some-page|', $this->path->__toString());
        $this->assertTrue($this->path->isInspecting());
    }

    public function testStopInspection(): void
    {
        $this->path->set('/test-webspace/de/some-page|property-path');
        $this->path->toggleInspection();

        $this->assertSame('/test-webspace/de/some-page', $this->path->__toString());
        $this->assertFalse($this->path->isInspecting());
    }
}
