<?php
// Load config
require_once($_SERVER["DOCUMENT_ROOT"] . "/../webshare/config.php");

// Get request and exempt admin page
$request = str_replace(dirname($_SERVER["PHP_SELF"]), "", $_SERVER["REQUEST_URI"]);
if (str_starts_with($request, "/admin")) {
	if (WebshareConfig::adminPageProtection()) {
		header("Content-Type: text/html");
		require("webshareAdmin.php");
		exit;
	}
	error404();
}

$share = getShare($request);
if (isset($share["link"])) {
	redirectLink($share);
}
if (isset($share["fileName"])) {
	if (isset($_GET["action"])) {
		redirectFile($share);
	}
	viewPage($share);
}
error404();

function getShare($request)
{
	// Connect to database
	$db = mysqli_connect(WebshareConfig::dbHost(), WebshareConfig::dbUsername(), WebshareConfig::dbPassword(), WebshareConfig::dbName());
	if (!$db) die("Database connection failed.");
	// Lookup request in database and redirect to link or file
	$request = mysqli_real_escape_string($db, $request);
	$request = substr($request, 1);
	$request = explode("?", $request)[0];
	$getShare = mysqli_prepare($db, "SELECT * FROM " . WebshareConfig::dbTableWebshare() . " WHERE uri=? LIMIT 1");
	mysqli_stmt_bind_param($getShare, "s", $request);
	mysqli_stmt_execute($getShare);
	$share = mysqli_fetch_assoc(mysqli_stmt_get_result($getShare));
	if (isset($share["expireDate"]) && strtotime($share["expireDate"]) < time()) {
		if (isset($share["fileName"])) {
			unlink(WebshareConfig::pathStorage() . $share["uri"]);
		}
		$deleteShare = mysqli_prepare($db, "DELETE FROM " . WebshareConfig::dbTableWebshare() . " WHERE uri=?");
		mysqli_stmt_bind_param($deleteShare, "s", $share["uri"]);
		mysqli_stmt_execute($deleteShare);
		error404();
	}
	return $share;
}

function redirectLink($share)
{
	header("Location:" . $share["link"]);
	exit;
}

function redirectFile($share)
{
	if ($_GET["action"] == "show") {
		$file = WebshareConfig::pathStorage() . $share["uri"];
		if (!file_exists($file)) {
			error404();
		}
		header("Content-Disposition: inline; filename=" . $share["fileName"]);
		header("Content-Type: " . $share["fileMime"]);
		header("Content-Length: " . filesize($file));
		readfile($file);
		exit;
	} elseif ($_GET["action"] == "download") {
		$file = WebshareConfig::pathStorage() . $share["uri"];
		if (!file_exists($file)) {
			error404();
		}
		header("Content-Disposition: attachment; filename=" . $share["fileName"]);
		header("Content-Type: " . $share["fileMime"]);
		header("Content-Length: " . filesize($file));
		readfile($file);
		exit;
	}
	error404();
}

function viewPage($share)
{
	require(WebshareConfig::pathViewPage());
	exit;
}

// Throw error if request is not found
function error404()
{
	header("HTTP/1.0 404 Not Found");
	header("Content-Type: text/html");
	require(WebshareConfig::path404Page());
	exit;
}
