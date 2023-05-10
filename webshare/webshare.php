<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

Version: 2.0-beta1

*/

namespace Friedinger\Webshare;

class Webshare extends Share
{
	public function __construct()
	{
		$status = $this->handleAction($_SERVER["REQUEST_URI"]);
		if (!$status) $this->error404();
	}
	private function handleAction($requestUri)
	{
		$request = $this->prepareRequest($requestUri);
		if (str_starts_with($request, "admin")) {
			if (Config::adminPageAccess()) {
				header("Content-Type: text/html");
				$webshareAdmin = new webshareAdmin();
			}
			exit;
		}
		$share = $this->getShare($request);
		if (!$share) return false;
		if (isset($_GET["action"]) && $_GET["action"] == "delete" && Config::adminPageAccess()) {
			$this->deletePage($share["uri"], $_POST["share"]);
		}
		if (isset($share->password)) {
			$this->passwordProtection($share->uri, $share->password, $_POST["password"] ?? null);
		}
		if ($share->type == "link") {
			return $this->redirectLink($share->value);
		}
		if ($share->type == "file") {
			return $this->redirectFile($share->uri, $share->value);
		}
	}
	private function prepareRequest(string $requestUri): string
	{
		$installPath = str_replace("\\", "", dirname($_SERVER["PHP_SELF"]) . "/");
		$request = htmlspecialchars(strtolower(urldecode($requestUri))); // Remove special chars from request
		$request = str_replace($installPath, "", $request); // Remove install path from request
		$request = explode("?", $request)[0]; // Remove parameters
		$request = rtrim($request, "/"); // Remove trailing slash
		return $request;
	}
	public function redirectLink($value): bool
	{
		header("Location:" . $value);
		return true;
	}
	public function redirectFile(string $uri, string $value): bool
	{
		$file = Config::pathStorage() . $uri;
		if (!file_exists($file)) {
			self::error404();
		}
		if (isset($_GET["action"])) {
			if ($_GET["action"] == "view") {
				header("Content-Disposition: inline; filename=" . $value);
			}
			if ($_GET["action"] == "download") {
				header("Content-Disposition: attachment; filename=" . $value);
			}
			header("Content-Type: " . mime_content_type($file));
			header("Content-Length: " . filesize($file));
			readfile($file);
			return true;
		}
		return $this->viewPage($uri, $value, $file);
	}
	private function viewPage(string $uri, string $value, string $filePath): bool
	{
		$mime = mime_content_type($filePath);
		$sharePreview = "<iframe src='?action=view' title='" . $value . "'></iframe>";
		if (str_starts_with($mime, "text/")) {
			$sharePreview = "<code>" . str_replace("\n", "<br>", file_get_contents($filePath)) . "</code>";
		}
		if (str_starts_with($mime, "image/")) {
			$sharePreview = "<img src='?action=view' alt='" . $value . "'></img>";
		}
		if (str_starts_with($mime, "audio/")) {
			$sharePreview = "<audio controls src='?action=view'></audio>";
		}
		if (str_starts_with($mime, "video/")) {
			$sharePreview = "<video controls src='?action=view'></video>";
		}
		$shareLink = $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/" . $uri;
		$shareFileName = $value;
		require(Config::pathViewPage($sharePreview, $shareFileName, $shareLink));
		return true;
	}
	private function passwordProtection(string $uri, string $sharePassword, string|null $inputPassword): bool
	{
		if (isset($_SESSION["webshare"][$uri]) && $_SESSION["webshare"][$uri]) return true;
		if (isset($inputPassword)) {
			if (password_verify(htmlspecialchars($inputPassword), $sharePassword)) {
				$_SESSION["webshare"][$uri] = true;
				return true;
			}
			$status = "incorrect";
		} else $status = "default";
		$shareUri = $uri;
		require(Config::pathPasswordPage($shareUri, $status));
		exit;
	}
	private function deletePage(string $uri, string $inputDelete)
	{
		if (isset($inputDelete) && $inputDelete == $uri) {
			$status = $this->deleteShare();
		} else $status = false;
		require(Config::pathDeletePage($uri, $status));
		exit;
	}
	private function error404()
	{
		header("HTTP/1.0 404 Not Found");
		header("Content-Type: text/html");
		require(Config::path404Page());
		exit;
	}
}

class Share extends Config
{
	public $uri;
	public $type;
	public $value;
	public $expireDate;
	public $password;

	public function addShare(string $uri, string $type, array $value, string $expireDate, string $password)
	{
		$db = $this->database();
		$this->uri = mysqli_real_escape_string($db, htmlspecialchars(preg_replace("/[^a-z0-9_-]/", "", strtolower($uri))));
		$this->type = $type;
		$this->$expireDate = mysqli_real_escape_string($db, htmlspecialchars($expireDate));
		if (empty($expireDate)) $this->$expireDate = null;
		$password = mysqli_real_escape_string($db, htmlspecialchars($password));
		if (!empty($password)) {
			$this->password = password_hash($password, PASSWORD_DEFAULT); // Hash password
		} else $this->password = null;

		// Add file
		if ($this->type == "file") {
			switch ($value["file"]["error"]) {
				case 0:
					$this->type = "file";
					$this->value = mysqli_real_escape_string($db, htmlspecialchars($value["file"]["name"]));
					move_uploaded_file($value["file"]["tmp_name"], Config::pathStorage() . $this->uri);
					break;
				case 1:
					return ["errorUploadSize"];
				default:
					return ["errorDefault"];
			}
		}
		// Add link
		if ($this->type == "link") {
			$this->type = "link";
			$this->value = mysqli_real_escape_string($db, urldecode($_POST["link"]));
			if (!preg_match("/^https?:\/\//", $this->value)) $this->value = "https://" . $this->value;
		}
		// Add share to database
		$addShare = mysqli_prepare($db, "INSERT IGNORE INTO " . Config::dbTableWebshare() . " (uri, type, value, password, expireDate) VALUES (?, ?, ?, ?, ?)");
		mysqli_stmt_bind_param($addShare, "sssss", $uri, $type, $value, $password, $expireDate);
		mysqli_stmt_execute($addShare);
		mysqli_close($db);
		if (mysqli_stmt_affected_rows($addShare)) {
			return ["success"];
		}
		return ["errorUri"];
	}
	public function getShare(string $request): object|bool
	{
		$db = $this->database();
		$request = mysqli_real_escape_string($db, $request);
		$getShare = mysqli_prepare($db, "SELECT * FROM " . Config::dbTableWebshare() . " WHERE uri=? LIMIT 1");
		mysqli_stmt_bind_param($getShare, "s", $request);
		mysqli_stmt_execute($getShare);
		$share = mysqli_fetch_assoc(mysqli_stmt_get_result($getShare));
		mysqli_close($db);
		if ($share == null) return false;
		$this->uri = $share["uri"];
		$this->type = $share["type"];
		$this->value = $share["value"];
		$this->expireDate = $share["expireDate"];
		$this->password = $share["password"];
		if (isset($this->expireDate) && strtotime($this->expireDate) < time()) {
			$this->deleteShare();
			return false;
		}
		return $this;
	}
	public function deleteShare(): bool
	{
		if (!Config::adminPageAccess()) return false; // Check delete permission
		if ($this->type == "file") { // Delete file if share type is file
			$fileDelete = unlink(Config::pathStorage() . $this->uri);
			if (!$fileDelete) return false; // Error handling
		}
		$db = $this->database();
		$deleteShare = mysqli_prepare($db, "DELETE FROM " . Config::dbTableWebshare() . " WHERE uri=?"); // Delete share from database
		mysqli_stmt_bind_param($deleteShare, "s", $this->uri);
		$dbDelete = mysqli_stmt_execute($deleteShare);
		mysqli_close($db);
		if (!$dbDelete) return false; // Error handling
		return true;
	}
	private function database(): \mysqli|false
	{
		$db = mysqli_connect(Config::dbHost(), Config::dbUsername(), Config::dbPassword(), config::dbName());
		if (!$db) die("Database connection failed.");
		return $db;
	}
}

class WebshareAdmin extends Config
{
	public function __construct()
	{
		if (!Config::adminPageAccess()) exit;

		if (!empty($_POST["submit"]) && Config::adminPageAccess()) {
			$status = $this->addShare();
		} else $status[] = "";
		$shareList = $this->listShares();
		include(Config::pathAdminPage($status, $shareList));
	}
	private function addShare()
	{
		$db = mysqli_connect(Config::dbHost(), Config::dbUsername(), Config::dbPassword(), Config::dbName());
		if (!$db) die("Database connection failed."); // Database connection error
		$uri = mysqli_real_escape_string($db, htmlspecialchars(preg_replace("/[^a-z0-9_-]/", "", strtolower($_POST["uri"]))));
		$expireDate = mysqli_real_escape_string($db, htmlspecialchars($_POST["expireDate"]));
		$password = mysqli_real_escape_string($db, htmlspecialchars($_POST["password"]));
		if (empty($password)) $password = null;
		else $password = password_hash($password, PASSWORD_DEFAULT); // Hash password
		if (empty($expireDate)) $expireDate = null;
		if ($_FILES["file"]["name"] && !empty($_POST["link"])) return "errorBoth";
		// Add file
		if ($_FILES["file"]["name"]) {
			switch ($_FILES["file"]["error"]) {
				case 0:
					$type = "file";
					$value = mysqli_real_escape_string($db, htmlspecialchars($_FILES["file"]["name"]));
					move_uploaded_file($_FILES["file"]["tmp_name"], Config::pathStorage() . $uri);
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
			$value = mysqli_real_escape_string($db, urldecode($_POST["link"]));
			if (!preg_match("/^https?:\/\//", $value)) $value = "https://" . $value;
		}
		// Add share to database
		if (!empty($type)) {
			$addShare = mysqli_prepare($db, "INSERT IGNORE INTO " . Config::dbTableWebshare() . " (uri, type, value, password, expireDate) VALUES (?, ?, ?, ?, ?)");
			mysqli_stmt_bind_param($addShare, "sssss", $uri, $type, $value, $password, $expireDate);
			mysqli_stmt_execute($addShare);
			mysqli_close($db);
			if (mysqli_stmt_affected_rows($addShare)) {
				return ["success", $this->linkToShare($uri, "long")];
			}
			return ["errorUri"];
		}
		return ["errorDefault"];
	}

	// List shares in table
	private function listShares()
	{
		$sort = array(
			"uri" => "uri ASC",
			"type" => "type ASC",
			"value" => "value ASC",
			"password" => "password DESC",
			"expireDate" => "expireDate DESC",
			"createDate" => "createDate DESC",
		);
		$shareSort = $sort[$_GET["sort"] ?? "createDate"] ?? "createDate DESC";

		$db = mysqli_connect(Config::dbHost(), Config::dbUsername(), Config::dbPassword(), Config::dbName());
		if (!$db) die("Database connection failed."); // Database connection error
		$listShares = mysqli_prepare($db, "SELECT * FROM " . Config::dbTableWebshare() . " ORDER BY " . Config::dbTableWebshare() . "." . $shareSort);
		mysqli_stmt_execute($listShares);
		$shares = mysqli_fetch_all(mysqli_stmt_get_result($listShares), MYSQLI_ASSOC);
		$shareList = "";
		foreach ($shares as $shareContent) {
			if (!empty($shareContent["password"])) {
				$sharePassword = "True";
			} else $sharePassword = "";
			$shareList .= "
		<tr>
			<td>" . $this->linkToShare(htmlspecialchars($shareContent["uri"]), "short") . "</a></td>
			<td>" . htmlspecialchars($shareContent["type"]) . "</td>
			<td>" . htmlspecialchars($shareContent["value"]) . "</td>
			<td>" . $sharePassword . "</td>
			<td>" . htmlspecialchars($shareContent["expireDate"]) . "</td>
			<td>" . htmlspecialchars($shareContent["createDate"]) . "</td>
			<td>" . $this->linkToShare(htmlspecialchars($shareContent["uri"]) . "?action=delete", "short", "Delete") . "</td>
		</tr>
		";
		}
		return $shareList;
	}

	private function linkToShare($uri, $type, $text = false)
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
}
