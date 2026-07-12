<?php

declare(strict_types=1);

namespace Tests\Mamazu\SuluCliBundle\Services;

use Mamazu\SuluCliBundle\Object\ContentPath;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ContentPathTest extends TestCase
{
	private ContentPath $path;

	public function setUp(): void
	{
		$this->path = new ContentPath();
	}

	public function testToString(): void
	{
		$this->assertSame('/', $this->path->__toString());

		$this->path->set('..');
		$this->assertSame('/', $this->path->__toString());
	}

	public function testAppendRoute(): void
	{
		$this->path->set('/webspace/es/los-homepage');
		$this->path->set('..');

		$this->assertNull($this->path->getRoute());
		$this->assertSame('es', $this->path->getLocale());
		$this->assertSame('webspace', $this->path->getWebspace());
		$this->assertSame('', $this->path->getPropertyPath());
	}

	public function testAppendContent(): void
	{
		$this->path->set('/webspace/es/test-page|content-block');

		$this->assertSame('test-page', $this->path->getRoute());
		$this->assertSame('es', $this->path->getLocale());
		$this->assertSame('webspace', $this->path->getWebspace());
		$this->assertSame('content-block', $this->path->getPropertyPath());
		$this->assertTrue($this->path->isInspecting());
	}

	public function testSetWithAppend(): void
	{
		$this->path->set('/webspace/es/test-page');
		$this->path->set('some-site');

		$this->assertSame('test-page/some-site', $this->path->getRoute());
		$this->assertSame('es', $this->path->getLocale());
		$this->assertSame('webspace', $this->path->getWebspace());
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
