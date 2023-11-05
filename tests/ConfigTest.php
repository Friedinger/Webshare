<?php

use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
	protected function setUp(): void
	{
		$_SERVER["DOCUMENT_ROOT"] = __DIR__ . "/../home/";
		require_once($_SERVER["DOCUMENT_ROOT"] . "/../webshare/webshareConfig.php");
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
	public function testArrayTextForm()
	{
		$this->assertIsArray(Webshare\Config::TEXT_FORM);
		$keys = [
			"labelUri", "labelFile", "labelExpireDate", "labelPassword", "labelSeparator", "buttonAdd", "buttonDelete", "buttonPassword",
		];
		foreach ($keys as $key) {
			$this->assertArrayHasKey($key, Webshare\Config::TEXT_FORM);
		}
	}
	public function testArrayTextAdd()
	{
		$this->assertIsArray(Webshare\Config::TEXT_ADD);
		$keys = [
			"success", "errorUri", "errorBoth", "errorUploadSize", "error",
		];
		foreach ($keys as $key) {
			$this->assertArrayHasKey($key, Webshare\Config::TEXT_ADD);
		}
	}
	public function testArrayTextDelete()
	{
		$this->assertIsArray(Webshare\Config::TEXT_DELETE);
		$keys = [
			"heading", "default", "success", "error",
		];
		foreach ($keys as $key) {
			$this->assertArrayHasKey($key, Webshare\Config::TEXT_DELETE);
		}
	}
	public function testArrayTextPassword()
	{
		$this->assertIsArray(Webshare\Config::TEXT_PASSWORD);
		$keys = [
			"heading", "default", "incorrect",
		];
		foreach ($keys as $key) {
			$this->assertArrayHasKey($key, Webshare\Config::TEXT_PASSWORD);
		}
	}
	public function testFunctions()
	{
		$this->assertNull(Webshare\Config::error404());
		$this->assertIsBool(Webshare\Config::adminAccess());
		$this->assertNull(Webshare\Config::noAdminAccess());
	}
}
