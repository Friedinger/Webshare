<?php
// Load config
require_once($_SERVER["DOCUMENT_ROOT"] . "/../webshare/config.php");
include(webshareConfig::pathAdminPage());

if (!webshareConfig::adminPageProtection()) {
	header("Location: /");
	exit;
}

if (!empty($_POST["submit"]) && webshareConfig::adminPageProtection()) {
	print(addShare());
}

// Add a new share
function addShare()
{
	$db = mysqli_connect(webshareConfig::dbHost(), webshareConfig::dbUsername(), webshareConfig::dbPassword(), webshareConfig::dbName());
	if (!$db) die("Database connection failed."); // Database connection error
	$uri = mysqli_real_escape_string($db, $_POST["uri"]);
	$expireDate = mysqli_real_escape_string($db, $_POST["expireDate"]);
	if (empty($expireDate)) $expireDate = null;
	if ($_FILES["file"]["name"] && !empty($_POST["link"])) return webshareConfig::addingMessages("errorBoth");
	// Add file
	if ($_FILES["file"]["name"]) {
		switch ($_FILES["file"]["error"]) {
			case 0:
				$fileName = mysqli_real_escape_string($db, $_FILES["file"]["name"]);
				$fileMime = getMime(mysqli_real_escape_string($db, $fileName));
				$addShare = mysqli_prepare($db, "INSERT IGNORE INTO " . webshareConfig::dbTableWebshare() . " (uri, fileName, fileMime, expireDate) VALUES (?, ?, ?, ?)");
				mysqli_stmt_bind_param($addShare, "ssss", $uri, $fileName, $fileMime, $expireDate);
				mysqli_stmt_execute($addShare);
				if (mysqli_stmt_affected_rows($addShare)) {
					move_uploaded_file($_FILES["file"]["tmp_name"], webshareConfig::pathStorage() . $uri);
					mysqli_close($db);
					return webshareConfig::addingMessages("success") . "<a href='" . $uri . "'>" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . $uri . "</a>";
				}
				mysqli_close($db);
				return webshareConfig::addingMessages("errorUri");
			case 1:
				return webshareConfig::addingMessages("errorUploadSize");
			default:
				return webshareConfig::addingMessages("error");
		}
	}
	// Add link
	if (!empty($_POST["link"])) {
		$link = mysqli_real_escape_string($db, $_POST["link"]);
		if (!preg_match("/^https?:\/\//", $link)) $link = "https://" . $link;
		$addShare = mysqli_prepare($db, "INSERT IGNORE INTO " . webshareConfig::dbTableWebshare() . " (uri, link, expireDate) VALUES (?, ?, ?)");
		mysqli_stmt_bind_param($addShare, "sss", $uri, $link, $expireDate);
		mysqli_stmt_execute($addShare);
		if (mysqli_stmt_affected_rows($addShare)) {
			mysqli_close($db);
			$shareLink =  "https://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/" . $uri;
			$shareLink = str_replace("\\", "", $shareLink);
			$copyShareLink = "
				<a href='javascript:void(0);' onclick='navigator.clipboard.writeText(`" . $shareLink . "`);'>
					<div class='copy-icon'>
				</div></a>
			";
			$hi = webshareConfig::addingMessages("success") . " <a href='" . $uri . "'>" . $shareLink . "</a> " . $copyShareLink;
			return $hi;
		}
		mysqli_close($db);
		return webshareConfig::addingMessages("errorUri");
	}
	mysqli_close($db);
	return webshareConfig::addingMessages("error");
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
	$mime = $mimeTypes[$extension] ?? "text/plain"; // Chose mime type depending on file extension, default value "text/plain"
	return $mime; // Return mime type
}
