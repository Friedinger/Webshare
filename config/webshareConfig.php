<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

Version: 2.2

*/

namespace Webshare;

class Config
{
	const INSTALL_PATH = "/";
	const PATH_STORAGE = "/../../_webshareFiles/";
	const PATH_ADMIN = "/../config/adminPage_sample.php";
	const PATH_VIEW = "/../config/viewPage_sample.php";
	const PATH_PASSWORD = "/../config/passwordPage_sample.php";
	const PATH_DELETE = "/../config/deletePage_sample.php";
	const DB_HOST = "localhost";
	const DB_USERNAME = "root";
	const DB_PASSWORD = "";
	const DB_NAME = "friedinger";
	const DB_TABLE = "webshare";
	public static function error404(): void
	{
		header("HTTP/1.0 404 Not Found");
		header("Content-Type: text/html");
		require($_SERVER["DOCUMENT_ROOT"] . "/../config/404Page_sample.php");
	}
	public static function adminAccess(): bool
	{
		return true;
	}
	public static function noAdminAccess(): void
	{
		echo "<h1>Forbidden</h1>No access to the requested page.";
	}
}
