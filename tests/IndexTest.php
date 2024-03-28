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
		ob_start();
		require_once($_SERVER["DOCUMENT_ROOT"] . "index.php");
		ob_end_clean();
		$this->assertIsObject($webshare);
	}
}
