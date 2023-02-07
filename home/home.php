<?php
require_once("main.php");

$pathStorage = $_SERVER["DOCUMENT_ROOT"] . "/../files/";
$pathConfig = $_SERVER["DOCUMENT_ROOT"] . "/../config/config.json";

$config = json_decode(file_get_contents($pathConfig), true); // Load config
if (!$config) die("Config loading failed."); // Config load error
$db = mysqli_connect($config["db_host"], $config["db_username"], $config["db_password"], $config["db_name"]); // Connect to database
if (!$db) die("Database connection failed."); // Database connection error

$request = mysqli_real_escape_string($db, $_SERVER["REQUEST_URI"]);
$request = substr($request, 1);
if (str_starts_with($request, "admin")) {
	header("Content-Type: text/html");
	require("admin.php");
	exit;
}
$getRedirect = mysqli_prepare($db, "SELECT * FROM webshare WHERE uri=? LIMIT 1");
mysqli_stmt_bind_param($getRedirect, "s", $request);
mysqli_stmt_execute($getRedirect);
$redirect = mysqli_fetch_assoc(mysqli_stmt_get_result($getRedirect));
if (isset($redirect["link"])) {
	header("Location:" . $redirect["link"]);
	exit;
}
if (isset($redirect["fileName"]) && isset($redirect["fileName"])) {
	$file = $pathStorage . $redirect["uri"];
	if (!file_exists($file)) {
		header("HTTP/1.0 404 Not Found");
		echo "Error";
		exit;
	}
	header("Content-Disposition: attachment; filename=" . $redirect["fileName"]);
	header("Content-Type: " . $redirect["fileMime"]);
	require($file);
	exit;
}
header("HTTP/1.0 404 Not Found");
echo "Error 404";
