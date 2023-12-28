<?php

use PHPUnit\Framework\TestCase;

final class DatabaseTest extends TestCase
{
	protected function setUp(): void
	{
		$_SERVER["DOCUMENT_ROOT"] = __DIR__ . "/../home/";
		require_once($_SERVER["DOCUMENT_ROOT"] . "/../webshare/webshareConfig.php");
		require_once($_SERVER["DOCUMENT_ROOT"] . "/../webshare/function/Database.php");
	}
	public function testConnection()
	{
		$database = new Webshare\Database();
		$this->assertObjectHasProperty("connection", $database);
	}
	public function testTable()
	{
		$database = new Webshare\Database();
		$query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :database AND TABLE_NAME = :table";
		$params = [":database" => Webshare\Config::DB_NAME, ":table" => Webshare\Config::DB_TABLE];
		$tableColumns = $database->query($query, $params)->fetchAll();
		$expectedColumns = ["uri", "type", "value", "password", "expireDate", "createDate"];
		$this->assertEquals($expectedColumns, array_column($tableColumns, "COLUMN_NAME"));
	}
}
