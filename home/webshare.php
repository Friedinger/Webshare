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
	}
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
		redirectFile($share);
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

function redirectFile($share)
{
	$file = WebshareConfig::pathStorage() . $share["uri"];
	if (!file_exists($file)) {
		error404();
	}
	if (isset($_GET["action"])) {
		if ($_GET["action"] == "view") {
			header("Content-Disposition: inline; filename=" . $share["value"]);
			header("Content-Type: " . mime_content_type($file));
			header("Content-Length: " . filesize($file));
			readfile($file);
			exit;
		} elseif ($_GET["action"] == "download") {

			header("Content-Disposition: attachment; filename=" . $share["value"]);
			header("Content-Type: " . mime_content_type($file));
			header("Content-Length: " . filesize($file));
			readfile($file);
			exit;
		}
	}
	$sharePreview = "<iframe src='?action=view' title='" . $share["value"] . "'></iframe>";
	$mime = mime_content_type($file);
	if (str_starts_with($mime, "text/")) {
		$sharePreview = "<code>" . str_replace("\n", "<br>", file_get_contents($file)) . "</code>";
	}
	if (str_starts_with($mime, "image/")) {
		$sharePreview = "<img src='?action=view' alt='" . $share["value"] . "'></img>";
	}
	if (str_starts_with($mime, "audio/")) {
		$sharePreview = "<audio controls src='?action=view'></audio>";
	}
	if (str_starts_with($mime, "video/")) {
		$sharePreview = "<video controls src='?action=view'></video>";
	}
	viewPage($share, $sharePreview);
}

function viewPage($share, $sharePreview)
{
	$shareLink = $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/" . $share["uri"];
	$shareFileName = $share["value"];
	require(WebshareConfig::pathViewPage($sharePreview, $shareFileName, $shareLink));
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
	} else $status = "default";
	$uri = $share["uri"];
	require(WebshareConfig::pathPasswordPage($uri, $status));
	exit;
}


function deletePage($share)
{
	$uri = $share["uri"];
	if (isset($_POST["share"]) && $_POST["share"] == $uri) {
		deleteShare($share);
		$status = "success";
	}
	if (!isset($status)) $status = "default";
	require(WebshareConfig::pathDeletePage($uri, $status));
	exit;
}

function deleteShare($share)
{
	if (!WebshareConfig::adminPageAccess()) return;
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
