<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

Version: 3.0

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
	const PATH_INCLUDES = [
		// Path to additional files that shall be accessible. Link is relative from install path, server path is relative from document root.
		// Warning: Mind making files accessible that are not intended to be public like configuration files or source code.
		// "link" => "/server path"
		"style.css" => "/../config/style_sample.css"
	];
	const ADMIN_LINK = "admin"; // Uri to access admin page. No leading and trailing slash

	const DB_HOST = "Database host server"; // Mysql database host server
	const DB_USERNAME = "Database username"; // Mysql database username
	const DB_PASSWORD = "Database password"; // Mysql database password
	const DB_NAME = "Database name"; // Mysql database name
	const DB_TABLE = "webshare"; // Mysql database table to store webshare data

	const TEXT_OUTPUT = [
		// Text that is outputted if certain values can not be displayed
		"passwordIsSet" => "yes",
		"passwordNotSet" => "no",
		"noExpireDate" => "never"
	];
	const TEXT_ADMIN = [
		// Output texts for admin page
		"success" => "Share added successfully: Webshare\Output::link(null, null, true)",
		"errorUri" => "Share adding failed: The entered uri is invalid, please try another one.",
		"errorBoth" => "Share adding failed: File and link offered, please only choose one.",
		"errorUploadSize" => "Share adding failed: File size limit exceeded.",
		"error" => "Share adding failed. Please contact webmaster.",
		"default" => "",
	];
	const TEXT_PASSWORD = [
		// Output texts for password page
		"incorrect" => "The entered password is incorrect, please try again.",
		"default" => "Please enter the password to access the share <i><share-uri /></i>."
	];
	const TEXT_DELETE = [
		// Output texts for delete page
		"success" => "Share <i><share-uri /></i> successfully deleted.",
		"errorInput" => "Please enter the correct uri to delete the share.",
		"error" => "Deleting share <i><share-uri /></i> failed. Please contact webmaster."
	];

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
