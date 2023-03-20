<?php
// Load config
require_once($_SERVER["DOCUMENT_ROOT"] . "/../webshare/webshareConfig.php");


if (!WebshareConfig::adminPageAccess()) {
	WebshareConfig::adminPageAccessFailed();
	exit;
}

$message = "";
if (!empty($_POST["submit"]) && WebshareConfig::adminPageAccess()) {
	$message = addShare();
	if (isset($db)) mysqli_close($db);
}
$shareList = listShares();
include(WebshareConfig::pathAdminPage($message, $shareList));

// Add a new share
function addShare()
{
	$db = mysqli_connect(WebshareConfig::dbHost(), WebshareConfig::dbUsername(), WebshareConfig::dbPassword(), WebshareConfig::dbName());
	if (!$db) die("Database connection failed."); // Database connection error
	$uri = mysqli_real_escape_string($db, htmlspecialchars($_POST["uri"]));
	$expireDate = mysqli_real_escape_string($db, htmlspecialchars($_POST["expireDate"]));
	$password = mysqli_real_escape_string($db, htmlspecialchars($_POST["password"]));
	if (empty($password)) {
		$password = null;
	} else {
		$password = password_hash($password, PASSWORD_DEFAULT); // Hash password
	}
	if (empty($expireDate)) $expireDate = null;
	if ($_FILES["file"]["name"] && !empty($_POST["link"])) return WebshareConfig::addingMessages("errorBoth");
	// Add file
	if ($_FILES["file"]["name"]) {
		switch ($_FILES["file"]["error"]) {
			case 0:
				$file = mysqli_real_escape_string($db, $_FILES["file"]["name"]);
				$addShare = mysqli_prepare($db, "INSERT IGNORE INTO " . WebshareConfig::dbTableWebshare() . " (uri, file, password, expireDate) VALUES (?, ?, ?, ?)");
				mysqli_stmt_bind_param($addShare, "ssss", $uri, $file, $password, $expireDate);
				mysqli_stmt_execute($addShare);
				if (mysqli_stmt_affected_rows($addShare)) {
					move_uploaded_file($_FILES["file"]["tmp_name"], WebshareConfig::pathStorage() . $uri);
					return WebshareConfig::addingMessages("success") . " " . linkToShare($uri, "long");
				}
				return WebshareConfig::addingMessages("errorUri");
			case 1:
				return WebshareConfig::addingMessages("errorUploadSize");
			default:
				return WebshareConfig::addingMessages("error");
		}
	}
	// Add link
	if (!empty($_POST["link"])) {
		$link = mysqli_real_escape_string($db, $_POST["link"]);
		if (!preg_match("/^https?:\/\//", $link)) $link = "https://" . $link;
		$addShare = mysqli_prepare($db, "INSERT IGNORE INTO " . WebshareConfig::dbTableWebshare() . " (uri, link, password, expireDate) VALUES (?, ?, ?, ?)");
		mysqli_stmt_bind_param($addShare, "ssss", $uri, $link, $password, $expireDate);
		mysqli_stmt_execute($addShare);
		if (mysqli_stmt_affected_rows($addShare)) {
			return WebshareConfig::addingMessages("success") . " " . linkToShare($uri, "long");
		}
		return WebshareConfig::addingMessages("errorUri");
	}
	return WebshareConfig::addingMessages("error");
}

// List shares in table
function listShares()
{
	$db = mysqli_connect(WebshareConfig::dbHost(), WebshareConfig::dbUsername(), WebshareConfig::dbPassword(), WebshareConfig::dbName());
	if (!$db) die("Database connection failed."); // Database connection error
	$listShares = mysqli_prepare($db, "SELECT * FROM " . WebshareConfig::dbTableWebshare());
	mysqli_stmt_execute($listShares);
	$shares = mysqli_fetch_all(mysqli_stmt_get_result($listShares), MYSQLI_ASSOC);
	$shareList = "";
	foreach ($shares as $shareContent) {
		$sharePassword = "";
		if (!empty($shareContent["password"])) {
			$sharePassword = "True";
		}
		$shareList .= "
		<tr>
			<td>" . linkToShare(htmlspecialchars($shareContent["uri"]), "short") . "</a></td>
			<td>" . htmlspecialchars($shareContent["file"]) . "</td>
			<td>" . htmlspecialchars($shareContent["link"]) . "</td>
			<td>" . $sharePassword . "</td>
			<td>" . htmlspecialchars($shareContent["expireDate"]) . "</td>
			<td>" . htmlspecialchars($shareContent["createDate"]) . "</td>
			<td>" . linkToShare(htmlspecialchars($shareContent["uri"]) . "?action=delete", "short", "Delete") . "</td>
		</tr>
		";
	}
	return $shareList;
}

function linkToShare($uri, $type, $text = false)
{
	if (!$text) $text = $uri;
	$shareLink =  $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/" . $uri;
	$shareLink = str_replace("\\", "", $shareLink);
	if ($type == "short") {
		return "<a href='//" . $shareLink . "'>" . $text . "</a> ";
	}
	if ($type == "long") {
		$copyShareLink = "
		<a href='javascript:void(0);' onclick='navigator.clipboard.writeText(`https://" . $shareLink . "`);'>
			<span class='copy-icon'></span>
		</a>
		";
		return "<a href='//" . $shareLink . "'>https://" . $shareLink . "</a> " . $copyShareLink;
	}
}
