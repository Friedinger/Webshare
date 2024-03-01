<?php

namespace Webshare;

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
		$this->assertIsString(Config::INSTALL_PATH);
		$this->assertIsString(Config::PATH_STORAGE);
		$this->assertIsString(Config::PATH_ADMIN);
		$this->assertIsString(Config::PATH_VIEW);
		$this->assertIsString(Config::PATH_PASSWORD);
		$this->assertIsString(Config::PATH_DELETE);
		$this->assertIsString(Config::DB_HOST);
		$this->assertIsString(Config::DB_USERNAME);
		$this->assertIsString(Config::DB_PASSWORD);
		$this->assertIsString(Config::DB_NAME);
		$this->assertIsString(Config::DB_TABLE);
	}
	public function testFunctions()
	{
		$this->assertNull(Config::error404());
		$this->assertIsBool(Config::adminAccess());
		$this->assertNull(Config::noAdminAccess());
	}
}
