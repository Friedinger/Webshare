<?php
if (!isset($_SESSION)) session_start();
class config
{
	public static function pathStorage()
	{
		return $_SERVER["DOCUMENT_ROOT"] . "Path to file storage, relativ from document root";
	}
	public static function dbHost()
	{
		return "Database host server";
	}
	public static function dbUsername()
	{
		return "Database username";
	}
	public static function dbPassword()
	{
		return "Database password";
	}
	public static function dbName()
	{
		return "Database name";
	}
	public static function dbTableWebshare()
	{
		"Webshare table";
	}
}
