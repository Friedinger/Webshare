<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

Version: 2.3

*/

namespace Webshare;

class Config
{
	const INSTALL_PATH = "/"; // Set the path in which the index.php file is located or at which uri it's content is executed. Must start and end with a slash
	const PATH_STORAGE = "/../config/files/"; // Path to file storage, relativ from document root. Important: Trailing slash at the end
	const PATH_ADMIN = "/../config/adminPage_sample.php"; // Path to admin page which offers form to add shares
	const PATH_VIEW = "/../config/viewPage_sample.php"; // Path to view page which displays a preview of requested file
	const PATH_PASSWORD = "/../config/passwordPage_sample.php"; // Path to password page for protected shares
	const PATH_DELETE = "/../config/deletePage_sample.php"; // Path to page to delete shares
	const DB_HOST = "Database host server"; // Mysql database host server
	const DB_USERNAME = "Database username"; // Mysql database username
	const DB_PASSWORD = "Database password"; // Mysql database password
	const DB_NAME = "Database name"; // Mysql database name
	const DB_TABLE = "webshare"; // Mysql database table to store webshare data
	public static function error404(): void
	{
		// Action if requested share doesn't exist
		header("HTTP/1.0 404 Not Found");
		header("Content-Type: text/html");
		require($_SERVER["DOCUMENT_ROOT"] . "/../config/404Page_sample.php");
	}
	public static function adminAccess(): bool
	{
		// Control authentication to protect admin page, return true if authenticated
		return true; // Just for development, should be set by login script
	}
	public static function noAdminAccess(): void
	{
		// Action if admin page was requested, but is not allowed
		echo "<h1>Forbidden</h1>No access to the requested page.";
	}
}
