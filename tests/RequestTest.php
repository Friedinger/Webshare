<?php

use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
	protected function setUp(): void
	{
		$_SERVER["DOCUMENT_ROOT"] = __DIR__ . "/../home/";
		require_once($_SERVER["DOCUMENT_ROOT"] . "/../webshare/webshareConfig.php");
		require_once($_SERVER["DOCUMENT_ROOT"] . "/../webshare/function/Request.php");
		$_SERVER["REQUEST_URI"] = "/";
		$_SESSION = [];
	}
	public function testUri()
	{
		$this->assertEquals("", (new Webshare\Request("/"))->uri());
		$this->assertEquals("test", (new Webshare\Request("/test//"))->uri());
		$this->assertEquals("test", (new Webshare\Request("/test/index.php///"))->uri());
		$this->assertEquals("test/test", (new Webshare\Request("/test/test"))->uri());
	}
	public function testSession()
	{
		$request = new Webshare\Request("/");
		$this->assertEquals(null, $request->session("test1"));
		$this->assertEquals(null, $request->session("test2", "test3"));
		$request->setSession("test1", "test1result");
		$request->setSession("test2", ["test2" => "test2result"]);
		$this->assertEquals("test1result", $request->session("test1"));
		$this->assertEquals("test2result", $request->session("test2", "test2"));
	}
}
