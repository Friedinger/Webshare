<?php
if (!isset($_SESSION)) session_start();
class config
{
	public static function pathStorage()
	{
		return $_SERVER["DOCUMENT_ROOT"] . "/../webshare/files/"; // Path to file storage, relativ from document root. Important: Trailing slash at the end
	}
	public static function pathAdminPage()
	{
		return $_SERVER["DOCUMENT_ROOT"] . "/../webshare/adminPage_sample.php"; // Path to admin page which offers form to add shares
	}
	public static function pathViewPage()
	{
		return $_SERVER["DOCUMENT_ROOT"] . "/../webshare/viewPage_sample.php"; // Path to view page which displays a preview of requested file
	}
	public static function path404Page()
	{
		return $_SERVER["DOCUMENT_ROOT"] . "/../webshare/404Page_sample.php"; // Path to page for error 404
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
			"success" => "Share added successfully: ", // Message if share added successfully
			"errorBoth" => "Share adding failed: File and link offered, please only choose one.", // Message if file and link options were set 
			"errorUri" => "Share adding failed: URI invalid, please chose a different one.", // Message if chosen URI is invalid
			"errorUploadSize" => "Share adding failed: File size limit exceeded.", // Message if file size limit is exceeded
			"error" => "Share adding failed.", // Message at other errors
		];
		return $messages[$message];
	}
}
