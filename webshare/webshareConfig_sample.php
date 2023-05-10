<?php

namespace Friedinger\Webshare;

if (!isset($_SESSION)) session_start();
class Config
{
	public static function loadWebshare()
	{
		require_once($_SERVER["DOCUMENT_ROOT"] . "/../webshare/webshare.php");
		$webshare = new Webshare();
	}
	public static function pathStorage()
	{
		return $_SERVER["DOCUMENT_ROOT"] . "/../webshare/files/"; // Path to file storage, relativ from document root. Important: Trailing slash at the end
	}
	public static function pathAdminPage($status, $shareList)
	{
		return $_SERVER["DOCUMENT_ROOT"] . "/../webshare/adminPage_sample.php"; // Path to admin page which offers form to add shares
	}
	public static function pathViewPage($sharePreview, $shareFileName, $shareLink)
	{
		return $_SERVER["DOCUMENT_ROOT"] . "/../webshare/viewPage_sample.php"; // Path to view page which displays a preview of requested file
	}
	public static function pathPasswordPage($uri, $status)
	{
		return $_SERVER["DOCUMENT_ROOT"] . "/../webshare/passwordPage_sample.php"; // Path to password page for protected shares
	}
	public static function pathDeletePage($uri, $status)
	{
		return $_SERVER["DOCUMENT_ROOT"] . "/../webshare/deletePage_sample.php"; // Path to page to delete shares
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
	public static function adminPageAccess()
	{
		$login = true; // Just for development, should be set by login script
		if ($login) {
			return true; // Control authentication to protect admin page, return true if authenticated
		}
		echo "<h1>Forbidden</h1>No access to the requested page."; // Action if admin page was requested, but is not allowed
		return false;
	}
}
