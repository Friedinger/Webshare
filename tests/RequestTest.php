<?php

namespace Webshare;

use \PHPUnit\Framework\TestCase;

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
		// Test with single key
		Request::setSession("value1", "test");
		$this->assertEquals("value1", $_SESSION["test"]);
		$this->assertEquals("value1", Request::session("test"));

		// Test with multiple keys
		Request::setSession("value2", "test1", "test2");
		$this->assertEquals("value2", $_SESSION["test1"]["test2"]);
		$this->assertEquals("value2", Request::session("test1", "test2"));
	}

	public function testProtocol()
	{
		// Test HTTP protocol
		$_SERVER["REQUEST_SCHEME"] = "http";
		$this->assertEquals("http", Request::protocol());

		// Test HTTPS protocol
		$_SERVER["REQUEST_SCHEME"] = "https";
		$this->assertEquals("https", Request::protocol());
	}

	public function testHttpHost()
	{
		// Test HTTP Host
		$_SERVER["HTTP_HOST"] = "localhost";
		$this->assertEquals("localhost", Request::httpHost());
	}

	public function testBaseUrl()
	{
		// Test Base URL with HTTP
		$_SERVER["REQUEST_SCHEME"] = "http";
		$_SERVER["HTTP_HOST"] = "localhost";
		$this->assertEquals("http://localhost/install/", Request::baseUrl());

		// Test Base URL with HTTPS
		$_SERVER["REQUEST_SCHEME"] = "https";
		$_SERVER["HTTP_HOST"] = "localhost";
		$this->assertEquals("https://localhost/install/", Request::baseUrl());
	}
}
