<?php
declare(strict_types=1);

use Mamazu\SuluCliBundle\Object\ContentPath;
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
}
