<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

Version: 2.0-beta2

*/

namespace Friedinger\Webshare;

spl_autoload_register(function ($className) {
	include $className . '.php';
});

if (!isset($_SESSION)) session_start();
class Config
{
	const PATH_STORAGE = "/../webshare/files/"; // Path to file storage, relativ from document root. Important: Trailing slash at the end
	const PATH_ADMIN = "/../webshare/adminPage_sample.php"; // Path to admin page which offers form to add shares
	const PATH_VIEW = "/../webshare/viewPage_sample.php"; // Path to view page which displays a preview of requested file
	const PATH_PASSWORD = "/../webshare/passwordPage_sample.php"; // Path to password page for protected shares
	const PATH_DELETE = "/../webshare/deletePage_sample.php"; // Path to page to delete shares
	const PATH_404 = "/../webshare/404Page_sample.php"; // Path to page for error 404
	const DB_HOST = "Database host server"; // Mysql database host server
	const DB_USERNAME = "Database username"; // Mysql database username
	const DB_PASSWORD = "Database password"; // Mysql database password
	const DB_NAME = "Database name"; // Mysql database name
	const DB_TABLE = "webshare"; // Mysql database table to store webshare data
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
