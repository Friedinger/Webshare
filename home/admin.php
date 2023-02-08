<?php
if (!isset($_SESSION)) session_start();

$pathStorage = $_SERVER["DOCUMENT_ROOT"] . "/../files/";
$pathConfig = $_SERVER["DOCUMENT_ROOT"] . "/../config/config.json";

$config = json_decode(file_get_contents($pathConfig), true); // Load config
if (!$config) die("Config loading failed."); // Config load error
$db = mysqli_connect($config["db_host"], $config["db_username"], $config["db_password"], $config["db_name"]); // Connect to database
if (!$db) die("Database connection failed."); // Database connection error

if (!empty($_POST["submit"])) {
	$uri = mysqli_real_escape_string($db, $_POST["uri"]);
	$expireDate = mysqli_real_escape_string($db, $_POST["expireDate"]);
	if ($_FILES["file"]["name"]) {
		echo "Uploading...<br>";
		$fileName = mysqli_real_escape_string($db, $_FILES["file"]["name"]);
		$fileMime = getMime(mysqli_real_escape_string($db, $fileName));
		$addToDatabase = mysqli_prepare($db, "INSERT IGNORE INTO webshare (uri, fileName, fileMime, expireDate) VALUES (?, ?, ?, ?)");
		mysqli_stmt_bind_param($addToDatabase, "ssss", $uri, $fileName, $fileMime, $expireDate);
		mysqli_stmt_execute($addToDatabase);
		if (mysqli_stmt_affected_rows($addToDatabase)) {
			move_uploaded_file($_FILES["file"]["tmp_name"], $pathStorage . $uri);
			echo "Upload Successful<br>";
		} else {
			echo "Error<br>";
		}
	} else if (!empty($_POST["link"])) {
		echo "Adding...<br>";
		$link = mysqli_real_escape_string($db, $_POST["link"]);
		$addToDatabase = mysqli_prepare($db, "INSERT IGNORE INTO webshare (uri, link, expireDate) VALUES (?, ?, ?)");
		mysqli_stmt_bind_param($addToDatabase, "sss", $uri, $link, $expireDate);
		mysqli_stmt_execute($addToDatabase);
		if (mysqli_stmt_affected_rows($addToDatabase)) {
			echo "Adding Successful<br>";
		} else {
			echo "Error<br>";
		}
	} else {
		echo "Error";
	}
}
mysqli_close($db);

function getMime($file)
{
	$mimeTypes = array( // List of mime types depending on file extension
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