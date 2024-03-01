<?php

namespace Webshare;

use PHPUnit\Framework\TestCase;

final class IndexTest extends TestCase
{
	protected function setUp(): void
	{
		$_SERVER["DOCUMENT_ROOT"] = __DIR__ . "/../home/";
		$_SERVER["REQUEST_URI"] = "/";
	}
	public function testIndex()
	{
		require_once($_SERVER["DOCUMENT_ROOT"] . "index.php");
		$this->assertIsObject($webshare);
	}
}
