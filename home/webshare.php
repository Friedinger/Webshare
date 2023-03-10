<?php
// Load config
require_once($_SERVER["DOCUMENT_ROOT"] . "/../webshare/webshareConfig.php");

// Get request and exempt admin page
$installPath = str_replace("\\", "", dirname($_SERVER["PHP_SELF"]) . "/");
$request = htmlspecialchars($_SERVER["REQUEST_URI"]); // Remove special chars from request
$request = str_replace($installPath, "", $request); // Remove install path from request
$request = explode("?", $request)[0]; // Remove parameters
$request = rtrim($request, "/"); // Remove trailing slash
if (str_starts_with($request, "admin")) {
	if (WebshareConfig::adminPageAccess()) {
		header("Content-Type: text/html");
		require("webshareAdmin.php");
		exit;
	}
	WebshareConfig::adminPageAccessFailed();
	exit;
}

$share = getShare($request);
if (isset($share["password"])) {
	passwordProtection($share);
}
if (isset($share["link"])) {
	redirectLink($share);
}
if (isset($share["file"])) {
	if (isset($_GET["action"])) {
		redirectFile($share);
	}
	viewPage($share, $installPath);
}
error404();

function getShare($request)
{
	// Connect to database
	$db = mysqli_connect(WebshareConfig::dbHost(), WebshareConfig::dbUsername(), WebshareConfig::dbPassword(), WebshareConfig::dbName());
	if (!$db) die("Database connection failed.");
	// Lookup request in database and redirect to link or file
	$request = mysqli_real_escape_string($db, $request);
	$getShare = mysqli_prepare($db, "SELECT * FROM " . WebshareConfig::dbTableWebshare() . " WHERE uri=? LIMIT 1");
	mysqli_stmt_bind_param($getShare, "s", $request);
	mysqli_stmt_execute($getShare);
	$share = mysqli_fetch_assoc(mysqli_stmt_get_result($getShare));
	if (isset($share["expireDate"]) && strtotime($share["expireDate"]) < time()) {
		if (isset($share["file"])) {
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
		header("Content-Disposition: inline; filename=" . $share["file"]);
		header("Content-Type: " . getMime($share["file"]));
		header("Content-Length: " . filesize($file));
		readfile($file);
		exit;
	} elseif ($_GET["action"] == "download") {
		$file = WebshareConfig::pathStorage() . $share["uri"];
		if (!file_exists($file)) {
			error404();
		}
		header("Content-Disposition: attachment; filename=" . $share["file"]);
		header("Content-Type: " . getMime($share["file"]));
		header("Content-Length: " . filesize($file));
		readfile($file);
		exit;
	}
	error404();
}

function passwordProtection($share)
{
	if (isset($_POST["password"])) {
		if (password_verify(htmlspecialchars($_POST["password"]), $share["password"])) {
			return;
		}
		$message = WebshareConfig::passwordMessages("incorrect");
		require(WebshareConfig::pathPasswordPage($message));
		exit;
	}
	$message = WebshareConfig::passwordMessages("standard");
	require(WebshareConfig::pathPasswordPage($message));
	exit;
}

function viewPage($share, $installPath)
{
	$iframeSrc = $installPath . $share["uri"];
	$iframeTitle = $share["file"];
	require(WebshareConfig::pathViewPage($iframeSrc, $iframeTitle));
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

// Get mime type of file
function getMime($file)
{
	$mimeTypes = array(
		// List of mime types depending on file extension
		"php" => "text/html",
		"html" => "text/html",
		"css" => "text/css",
		"scss" => "text/css",
		"js" => "application/x-javascript",
		"vbs" => "application/x-vbs",
		"ico" => "image/x-icon",
		"png" => "image/png",
		"jpg" => "image/jpeg",
		"jpeg" => "image/jpeg",
		"pdf" => "application/pdf",
	);
	$extension = pathinfo($file, PATHINFO_EXTENSION); // Get file extension
	return $mimeTypes[$extension] ?? "text/plain"; // Chose mime type depending on file extension, default value "text/plain"
}
