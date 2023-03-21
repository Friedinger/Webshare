<?php
// Load config
require_once($_SERVER["DOCUMENT_ROOT"] . "/../webshare/webshareConfig.php");


if (!WebshareConfig::adminPageAccess()) {
	WebshareConfig::adminPageAccessFailed();
	exit;
}

if (!empty($_POST["submit"]) && WebshareConfig::adminPageAccess()) {
	$status = addShare();
} else $status[] = "";
$shareList = listShares();
include(WebshareConfig::pathAdminPage($status, $shareList));

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
	if ($_FILES["file"]["name"] && !empty($_POST["link"])) return "errorBoth";
	// Add file
	if ($_FILES["file"]["name"]) {
		switch ($_FILES["file"]["error"]) {
			case 0:
				$type = "file";
				$value = mysqli_real_escape_string($db, htmlspecialchars($_FILES["file"]["name"]));
				move_uploaded_file($_FILES["file"]["tmp_name"], WebshareConfig::pathStorage() . $uri);
				break;
			case 1:
				return ["errorUploadSize"];
			default:
				return ["errorDefault"];
		}
	}
	// Add link
	if ($_POST["link"]) {
		$type = "link";
		$value = mysqli_real_escape_string($db, $_POST["link"]);
		if (!preg_match("/^https?:\/\//", $value)) $value = "https://" . $value;
	}
	// Add share to database
	if ($type) {
		$addShare = mysqli_prepare($db, "INSERT IGNORE INTO " . WebshareConfig::dbTableWebshare() . " (uri, type, value, password, expireDate) VALUES (?, ?, ?, ?, ?)");
		mysqli_stmt_bind_param($addShare, "sssss", $uri, $type, $value, $password, $expireDate);
		mysqli_stmt_execute($addShare);
		mysqli_close($db);
		if (mysqli_stmt_affected_rows($addShare)) {
			return ["success", linkToShare($uri, "long")];
		}
		return ["errorUri"];
	}
	return ["errorDefault"];
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
			<td>" . htmlspecialchars($shareContent["type"]) . "</td>
			<td>" . htmlspecialchars($shareContent["value"]) . "</td>
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
