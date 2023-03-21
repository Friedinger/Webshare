<?php
// Load config
require_once($_SERVER["DOCUMENT_ROOT"] . "/../webshare/webshareConfig.php");

// Get request and exempt admin page
$installPath = str_replace("\\", "", dirname($_SERVER["PHP_SELF"]) . "/");
$request = htmlspecialchars(strtolower($_SERVER["REQUEST_URI"])); // Remove special chars from request
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
if (isset($share)) {
	if (isset($_GET["action"]) && $_GET["action"] == "delete" && WebshareConfig::adminPageAccess()) {
		deletePage($share);
	}
	if (isset($share["password"])) {
		passwordProtection($share);
	}
	if ($share["type"] == "link") {
		redirectLink($share);
	}
	if ($share["type"] == "file") {
		redirectFile($share, $installPath);
	}
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
		deleteShare($share);
		error404();
	}
	mysqli_close($db);
	return $share;
}

function redirectLink($share)
{
	header("Location:" . $share["value"]);
	exit;
}

function redirectFile($share, $installPath)
{
	if (isset($_GET["action"])) {
		if ($_GET["action"] == "view") {
			$file = WebshareConfig::pathStorage() . $share["uri"];
			if (!file_exists($file)) {
				error404();
			}
			header("Content-Disposition: inline; filename=" . $share["value"]);
			header("Content-Type: " . getMime($share["value"]));
			header("Content-Length: " . filesize($file));
			readfile($file);
			exit;
		} elseif ($_GET["action"] == "download") {
			$file = WebshareConfig::pathStorage() . $share["uri"];
			if (!file_exists($file)) {
				error404();
			}
			header("Content-Disposition: attachment; filename=" . $share["value"]);
			header("Content-Type: " . getMime($share["value"]));
			header("Content-Length: " . filesize($file));
			readfile($file);
			exit;
		}
	}
	viewPage($share);
}

function viewPage($share)
{
	$shareLink = $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/" . $share["uri"];
	$shareFileName = $share["value"];
	require(WebshareConfig::pathViewPage($shareFileName, $shareLink));
	exit;
}

function passwordProtection($share)
{
	if (!empty($_SESSION["webshare"][$share["uri"]])) return;
	if (isset($_POST["password"])) {
		if (password_verify(htmlspecialchars($_POST["password"]), $share["password"])) {
			$_SESSION["webshare"][$share["uri"]] = true;
			return;
		}
		$status = "incorrect";
		require(WebshareConfig::pathPasswordPage($status));
		exit;
	}
	$status = "default";
	require(WebshareConfig::pathPasswordPage($status));
	exit;
}


function deletePage($share)
{
	$uri = $share["uri"];
	if (isset($_POST["share"]) && $_POST["share"] == $share["uri"]) {
		deleteShare($share);
		$status = "success";
	}
	if (!isset($status)) $status = "default";
	require(WebshareConfig::pathDeletePage($uri, $status));
	exit;
}

function deleteShare($share)
{
	if ($share["type"] == "file") unlink(WebshareConfig::pathStorage() . $share["uri"]);
	$db = mysqli_connect(WebshareConfig::dbHost(), WebshareConfig::dbUsername(), WebshareConfig::dbPassword(), WebshareConfig::dbName());
	$deleteShare = mysqli_prepare($db, "DELETE FROM " . WebshareConfig::dbTableWebshare() . " WHERE uri=?");
	mysqli_stmt_bind_param($deleteShare, "s", $share["uri"]);
	mysqli_stmt_execute($deleteShare);
	mysqli_close($db);
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
