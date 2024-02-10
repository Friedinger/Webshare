<?php

use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
	protected function setUp(): void
	{
		$_SERVER["DOCUMENT_ROOT"] = __DIR__ . "/../home/";
		require_once($_SERVER["DOCUMENT_ROOT"] . "/../config/webshareConfig.php");
	}
	public function testStrings()
	{
		$this->assertIsString(Webshare\Config::INSTALL_PATH);
		$this->assertIsString(Webshare\Config::PATH_STORAGE);
		$this->assertIsString(Webshare\Config::PATH_ADMIN);
		$this->assertIsString(Webshare\Config::PATH_VIEW);
		$this->assertIsString(Webshare\Config::PATH_PASSWORD);
		$this->assertIsString(Webshare\Config::PATH_DELETE);
		$this->assertIsString(Webshare\Config::DB_HOST);
		$this->assertIsString(Webshare\Config::DB_USERNAME);
		$this->assertIsString(Webshare\Config::DB_PASSWORD);
		$this->assertIsString(Webshare\Config::DB_NAME);
		$this->assertIsString(Webshare\Config::DB_TABLE);
	}
	public function testFunctions()
	{
		$this->assertNull(Webshare\Config::error404());
		$this->assertIsBool(Webshare\Config::adminAccess());
		$this->assertNull(Webshare\Config::noAdminAccess());
	}
}
