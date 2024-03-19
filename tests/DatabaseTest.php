<?php

namespace Webshare;

use PHPUnit\Framework\TestCase;

final class DatabaseTest extends TestCase
{
	protected function setUp(): void
	{
		$_SERVER["DOCUMENT_ROOT"] = __DIR__ . "/../home/";
		require_once($_SERVER["DOCUMENT_ROOT"] . "/../config/webshareConfig.php");
		require_once($_SERVER["DOCUMENT_ROOT"] . "/../function/Database.php");
		require_once($_SERVER["DOCUMENT_ROOT"] . "/../function/Exception.php");
	}
	public function testTable()
	{
		$query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :database AND TABLE_NAME = :table";
		$params = [":database" => Config::DB_NAME, ":table" => Config::DB_TABLE];
		$tableColumns = Database::query($query, $params)->fetchAll();
		$expectedColumns = ["uri", "type", "value", "password", "expireDate", "createDate"];
		$this->assertEquals($expectedColumns, array_column($tableColumns, "COLUMN_NAME"));
	}
}
