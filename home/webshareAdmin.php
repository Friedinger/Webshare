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

include(WebshareConfig::pathAdminPage($message));
listShares();

// Add a new share
function addShare()
{
	$db = mysqli_connect(WebshareConfig::dbHost(), WebshareConfig::dbUsername(), WebshareConfig::dbPassword(), WebshareConfig::dbName());
	if (!$db) die("Database connection failed."); // Database connection error
	$uri = mysqli_real_escape_string($db, $_POST["uri"]);
	$expireDate = mysqli_real_escape_string($db, $_POST["expireDate"]);
	$password = mysqli_real_escape_string($db, $_POST["password"]);
	$password = password_hash($password, PASSWORD_DEFAULT); // Hash password
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
					$shareLink =  "https://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/" . $uri;
					$shareLink = str_replace("\\", "", $shareLink);
					$copyShareLink = "
						<a href='javascript:void(0);' onclick='navigator.clipboard.writeText(`" . $shareLink . "`);'>
							<div class='copy-icon'>
						</div></a>
					";
					return WebshareConfig::addingMessages("success") . " <a href='" . $uri . "'>" . $shareLink . "</a> " . $copyShareLink;
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
			$shareLink =  $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/" . $uri;
			$shareLink = str_replace("\\", "", $shareLink);
			$copyShareLink = "
				<a href='javascript:void(0);' onclick='navigator.clipboard.writeText(`https://" . $shareLink . "`);'>
					<div class='copy-icon'>
				</div></a>
			";
			return WebshareConfig::addingMessages("success") . " <a href='//" . $shareLink . "'>https://" . $shareLink . "</a> " . $copyShareLink;
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
	$shareList = mysqli_fetch_all(mysqli_stmt_get_result($listShares), MYSQLI_ASSOC);
	print("<table><th>uri</th><th>file</th><th>link</th><th>password</th><th>expireDate</th><th>createDate</th><br>");
	foreach ($shareList as $shareContent) {
		print("<tr>");
		foreach ($shareContent as $shareValue) {
			print("<td>" . $shareValue . "</td>");
		}
		print("</tr>");
	}
	print("</table>");
}
