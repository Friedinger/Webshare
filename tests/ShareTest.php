<?php

namespace Webshare;

use PHPUnit\Framework\TestCase;
use Webshare\Share;

class ShareTest extends TestCase
{
	protected function setUp(): void
	{
		$_SERVER["DOCUMENT_ROOT"] = __DIR__ . "/../home/";
		require_once($_SERVER["DOCUMENT_ROOT"] . "/../function/Share.php");
	}

	public function testConstructor()
	{
		// Test constructor with all parameters
		$share = new Share("test-uri", "test-type", "test-value", "test-password", "test-expireDate", "test-createDate");
		$this->assertInstanceOf(Share::class, $share);
		$this->assertEquals("test-uri", $share->uri());
		$this->assertEquals("test-type", $share->type());
		$this->assertEquals("test-value", $share->value());
		$this->assertTrue($share->password());
		$this->assertEquals("test-expireDate", $share->expireDate());
		$this->assertEquals("test-createDate", $share->createDate());

		// Test constructor with null values for password, expireDate, and createDate
		$share = new Share("test-uri", "test-type", "test-value", null, null, null);
		$this->assertInstanceOf(Share::class, $share);
		$this->assertEquals("test-uri", $share->uri());
		$this->assertEquals("test-type", $share->type());
		$this->assertEquals("test-value", $share->value());
		$this->assertFalse($share->password());
		$this->assertNull($share->expireDate());
		$this->assertNull($share->createDate());
	}
}
