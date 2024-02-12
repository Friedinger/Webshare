<?php

namespace Webshare;

use \PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;

class Config
{
	const INSTALL_PATH = "/install/";
}

final class RequestTest extends TestCase
{
	protected function setUp(): void
	{
		$_SERVER["DOCUMENT_ROOT"] = __DIR__ . "/../home/";
		require_once($_SERVER["DOCUMENT_ROOT"] . "/../function/Request.php");
		$_SERVER["REQUEST_URI"] = "";
		$_SESSION = [];
	}

	public function testUri()
	{
		// Test Empty URI
		$_SERVER["REQUEST_URI"] = "/install/";
		$this->assertEquals("", Request::uri());

		// Test uri with trailing slash
		$_SERVER["REQUEST_URI"] = "/install/test/";
		$this->assertEquals("test", Request::uri());

		// Test uri without trailing slash
		$_SERVER["REQUEST_URI"] = "/install/test/";
		$this->assertEquals("test", Request::uri());

		// Test uri with index.php
		$_SERVER["REQUEST_URI"] = "/install/test/index.php/";
		$this->assertEquals("test", Request::uri());

		// Test URI with parameters
		$_SERVER["REQUEST_URI"] = "/install/test/?param=value/";
		$this->assertEquals("test", Request::uri());

		// Test URI with special characters
		$_SERVER["REQUEST_URI"] = "/install/test%20uri/";
		$this->assertEquals("test uri", Request::uri());
	}

	public function testGet()
	{
		// Test accessing non-existent GET parameter
		$_GET = [];
		$this->assertNull(Request::get("param1"));

		// Test accessing existing GET parameter
		$_GET = ["param1" => "value1"];
		$this->assertEquals("value1", Request::get("param1"));

		// Test accessing existing GET parameter with special characters
		$_GET = ["param1" => "<script>alert('XSS');</script>"];
		$this->assertEquals("&lt;script&gt;alert(&#039;XSS&#039;);&lt;/script&gt;", Request::get("param1"));
	}

	public function testPost()
	{
		// Test accessing non-existent POST parameter
		$_POST = [];
		$this->assertNull(Request::post("param1"));

		// Test accessing existing POST parameter
		$_POST = ["param1" => "value1"];
		$this->assertEquals("value1", Request::post("param1"));

		// Test accessing existing POST parameter with special characters
		$_POST = ["param1" => "<script>alert('XSS');</script>"];
		$this->assertEquals("&lt;script&gt;alert(&#039;XSS&#039;);&lt;/script&gt;", Request::post("param1"));
	}

	public function testSession()
	{
		// Test accessing non-existent session key
		$this->assertNull(Request::session("test1"));

		// Test accessing non-existent nested session key
		$this->assertNull(Request::session("test1", "test2"));

		// Test accessing existing session key
		$_SESSION["test1"] = "test2";
		$this->assertEquals("test2", Request::session("test1"));

		// Test accessing existing nested session key
		$_SESSION["test1"] = ["test2" => "test3"];
		$this->assertEquals("test3", Request::session("test1", "test2"));
	}

	public function testSetSession()
	{
		$request = new Request();

		// Test setting session with string value
		$key = "test1";
		$value = "value1";
		$this->assertTrue($request->setSession($key, $value));
		$this->assertEquals($value, $_SESSION[$key]);
		$this->assertEquals($value, $request->session($key));

		// Test setting session with integer value
		$key = "test2";
		$value = 123;
		$this->assertTrue($request->setSession($key, $value));
		$this->assertEquals($value, $_SESSION[$key]);
		$this->assertEquals($value, $request->session($key));

		// Test setting session with array value
		$key = "test3";
		$value = ["value1", "value2"];
		$this->assertTrue($request->setSession($key, $value));
		$this->assertEquals($value, $_SESSION[$key]);
		$this->assertEquals($value, $request->session($key));
	}
}