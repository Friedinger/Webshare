<?php
// Load config and connect to database
require_once($_SERVER["DOCUMENT_ROOT"] . "/../config/config.php");
$db = mysqli_connect(config::dbHost(), config::dbUsername(), config::dbPassword(), config::dbName());
if (!$db) die("Database connection failed.");

// Get request and exempt admin page
$request = mysqli_real_escape_string($db, $_SERVER["REQUEST_URI"]);
$request = substr($request, 1);
if (str_starts_with($request, "admin")) {
	header("Content-Type: text/html");
	require("admin.php");
	exit;
}
if (str_starts_with($request, "addShare.php")) {
	header("Content-Type: text/html");
	require("addShare.php");
	exit;
}

// Lookup request in database and redirect to link or file
$getShare = mysqli_prepare($db, "SELECT * FROM " . config::dbTableWebshare() . " WHERE uri=? LIMIT 1");
mysqli_stmt_bind_param($getShare, "s", $request);
mysqli_stmt_execute($getShare);
$share = mysqli_fetch_assoc(mysqli_stmt_get_result($getShare));
if (isset($share["expireDate"]) && strtotime($share["expireDate"]) < time()) {
	error404();
}
if (isset($share["link"])) {
	header("Location:" . $share["link"]);
	exit;
}
if (isset($share["fileName"]) && isset($share["fileName"])) {
	$file = $pathStorage . $share["uri"];
	if (!file_exists($file)) {
		error404();
	}
	header("Content-Disposition: attachment; filename=" . $share["fileName"]);
	header("Content-Type: " . $share["fileMime"]);
	require($file);
	exit;
}
error404();

// Throw error if request is not found
function error404()
{
	header("HTTP/1.0 404 Not Found");
	echo "Error 404";
	exit;
}
