<?php
// Load config
require_once($_SERVER["DOCUMENT_ROOT"] . "/../config/config.php");

if (!empty($_POST["submit"])) {
	addShare();
}

// Add a new share
function addShare()
{
	$db = mysqli_connect(config::dbHost(), config::dbUsername(), config::dbPassword(), config::dbName());
	if (!$db) die("Database connection failed."); // Database connection error
	$uri = mysqli_real_escape_string($db, $_POST["uri"]);
	$expireDate = mysqli_real_escape_string($db, $_POST["expireDate"]);
	if ($_FILES["file"]["name"]) {
		$fileName = mysqli_real_escape_string($db, $_FILES["file"]["name"]);
		$fileMime = getMime(mysqli_real_escape_string($db, $fileName));
		$addShare = mysqli_prepare($db, "INSERT IGNORE INTO " . config::dbTableWebshare() . " (uri, fileName, fileMime, expireDate) VALUES (?, ?, ?, ?)");
		mysqli_stmt_bind_param($addShare, "ssss", $uri, $fileName, $fileMime, $expireDate);
		mysqli_stmt_execute($addShare);
		if (mysqli_stmt_affected_rows($addShare)) {
			move_uploaded_file($_FILES["file"]["tmp_name"], config::dbTableWebshare() . $uri);
			mysqli_close($db);
			return ("Share erfolgreich hinzugefügt.");
		}
		mysqli_close($db);
		return ("Share hinzufügen fehlgeschlagen: URI kann nicht benutzt werden, bitte andere wählen.");
	}
	if (!empty($_POST["link"])) {
		$link = mysqli_real_escape_string($db, $_POST["link"]);
		$addShare = mysqli_prepare($db, "INSERT IGNORE INTO " . config::dbTableWebshare() . " (uri, link, expireDate) VALUES (?, ?, ?)");
		mysqli_stmt_bind_param($addShare, "sss", $uri, $link, $expireDate);
		mysqli_stmt_execute($addShare);
		if (mysqli_stmt_affected_rows($addShare)) {
			mysqli_close($db);
			return ("Share erfolgreich hinzugefügt.");
		}
		mysqli_close($db);
		return ("Share hinzufügen fehlgeschlagen: URI kann nicht benutzt werden, bitte andere wählen.");
	}
	mysqli_close($db);
	return ("Share hinzufügen fehlgeschlagen.");
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
	);
	$extension = pathinfo($file, PATHINFO_EXTENSION); // Get file extension
	$mime = $mimeTypes[$extension] ?? "text/plain"; // Chose mime type depending on file extension, default value "text/plain"
	return $mime; // Return mime type
}

?>

<!DOCTYPE html>
<html>

<body>
	<h2>Upload</h2>
	<form method="post" enctype="multipart/form-data">
		URI: <input type="text" name="uri" required><br>
		File: <input type="file" name="file"><br>
		Link: <input type="text" name="link"><br>
		Expire Date: <input type="datetime-local" name="expireDate"><br>
		<input type="submit" value="Submit" name="submit"><br>
	</form>
</body>

</html>