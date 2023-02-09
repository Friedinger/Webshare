<?php
if (!isset($_SESSION)) session_start();
class config
{
	public static function pathStorage()
	{
		return $_SERVER["DOCUMENT_ROOT"] . "/../files/"; // Path to file storage, relativ from document root. Important: Trailing slash at the end
	}
	public static function pathAdminPage()
	{
		return $_SERVER["DOCUMENT_ROOT"] . "/../config/adminPage_sample.php"; // Path to admin page which offers form to add shares
	}
	public static function dbHost()
	{
		return "Database host server"; // Database host server
	}
	public static function dbUsername()
	{
		return "username"; // Mysql database username
	}
	public static function dbPassword()
	{
		return "password"; // Mysql database password
	}
	public static function dbName()
	{
		return "name"; // Mysql database name
	}
	public static function dbTableWebshare()
	{
		return "webshare"; // Mysql database table to store webshare data
	}
	public static function addingMessages($message)
	{
		$messages = [
			"success" => "Share added successfully.", // Message if share added successfully
			"errorUri" => "Share adding failed: URI invalid, please chose a different one.", // Message if chosen URI is invalid
			"error" => "Share adding failed.", // Message at other errors
		];
		return $messages[$message];
	}
}
