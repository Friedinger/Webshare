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
	const INSTALL_PATH = "/"; // Set the path in which the index.php file is located or at which uri it's content is executed. Must start and end with a slash
	const PATH_STORAGE = "/../webshare/files/"; // Path to file storage, relativ from document root. Important: Trailing slash at the end
	const PATH_ADMIN = "/../webshare/adminPage_sample.php"; // Path to admin page which offers form to add shares
	const PATH_VIEW = "/../webshare/viewPage_sample.php"; // Path to view page which displays a preview of requested file
	const PATH_PASSWORD = "/../webshare/passwordPage_sample.php"; // Path to password page for protected shares
	const PATH_DELETE = "/../webshare/deletePage_sample.php"; // Path to page to delete shares
	const DB_HOST = "Database host server"; // Mysql database host server
	const DB_USERNAME = "Database username"; // Mysql database username
	const DB_PASSWORD = "Database password"; // Mysql database password
	const DB_NAME = "Database name"; // Mysql database name
	const DB_TABLE = "webshare"; // Mysql database table to store webshare data
	const TEXT_FORM = [
		"labelUri" => "Uri",
		"labelFile" => "File",
		"labelExpireDate" => "Expire Date",
		"labelPassword" => "Password",
		"labelSeparator" => ": ",
		"buttonAdd" => "Add share",
		"buttonDelete" => "Delete share",
		"buttonPassword" => "Submit password",
	];
	const TEXT_ADD = [
		"success" => "Share added successfully: LINK",
		// "success" => "Share added successfully: " . Output::link(null, null, true),
		"errorUri" => "Share adding failed: The entered uri is invalid, please try another one.",
		"errorBoth" => "Share adding failed: File and link offered, please only choose one.",
		"errorUploadSize" => "Share adding failed: File size limit exceeded.",
		"error" => "Share adding failed. Please contact webmaster.",
	];
	const TEXT_DELETE = [
		"heading" => "Webshare Delete",
		"default" => "Do you really want to delete the share <i>URI</i>?",
		// "default" => "Do you really want to delete the share <i>" . Output::$uri . "</i>?",
		"success" => "Share successfully deleted.",
		"error" => "Share deleting failed. Please contact webmaster.",
	];
	const TEXT_PASSWORD = [
		"heading" => "The share <i>URI</i> is password protected",
		// "heading" => "The share <i>" . Output::$uri . "</i> is password protected",
		"default" => "Please enter the password to access the share <i>URI</i>.",
		// "default" => "Please enter the password to access the share <i>" . Output::$uri . "</i>.",
		"incorrect" => "The entered password is incorrect, please try again.",
	];
	public static function error404(): void
	{
		// Action if requested share doesn't exist
		header("HTTP/1.0 404 Not Found");
		header("Content-Type: text/html");
		require($_SERVER["DOCUMENT_ROOT"] . "/../webshare/404Page_sample.php");
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
