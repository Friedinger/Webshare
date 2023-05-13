<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

*/

namespace Friedinger\Webshare;

final class Share
{
	public $uri;
	public $type;
	public $value;
	public $password;
	public $expireDate;
	public $createDate;
	public $file;
	public function addShare(Request $request): string
	{
		$db = $this->database();
		$this->uri = mysqli_real_escape_string($db, htmlspecialchars(preg_replace("/[^a-z0-9_-]/", "", strtolower($request->post("uri")))));
		$this->expireDate = mysqli_real_escape_string($db, htmlspecialchars($request->post("expireDate")));
		if (empty($this->expireDate)) $this->expireDate = null;
		$this->password = mysqli_real_escape_string($db, htmlspecialchars($request->post("password")));
		if (!empty($password)) {
			$this->password = password_hash($password, PASSWORD_DEFAULT);
		} else $this->password = null;
		if (!empty($request->post("link")) && empty($request->file("file", "size"))) {
			$this->type = "link";
			$this->value = mysqli_real_escape_string($db, urldecode($request->post("link")));
			if (!preg_match("/^https?:\/\//", $this->value)) $this->value = "https://" . $this->value;
			return $this->addShareToDatabase($db);
		}
		if (!empty($request->file("file", "size")) && empty($request->post("link"))) {
			$fileUpload = $request->file("file");
			switch ($fileUpload["error"]) {
				case 0:
					$this->type = "file";
					$this->value = mysqli_real_escape_string($db, htmlspecialchars($fileUpload["name"]));
					move_uploaded_file($fileUpload["tmp_name"], Config::pathStorage . $this->uri);
					return $this->addShareToDatabase($db);
				case 1:
					return "errorUploadSize";
				default:
					return "errorDefault";
			}
		}
		mysqli_close($db);
		if (!empty($request->post("link")) && !empty($request->file("file", "size"))) return "errorBoth";
		return "errorDefault";
	}
	private function addShareToDatabase(\Mysqli $db): string
	{
		$addShare = mysqli_prepare($db, "INSERT IGNORE INTO " . Config::dbTableWebshare . " (uri, type, value, password, expireDate) VALUES (?, ?, ?, ?, ?)");
		mysqli_stmt_bind_param($addShare, "sssss", $this->uri, $this->type, $this->value, $this->password, $this->expireDate);
		mysqli_stmt_execute($addShare);
		mysqli_close($db);
		if (mysqli_stmt_affected_rows($addShare)) return "success";
		return "errorUri";
	}
	public function getShare(string $uri): bool
	{
		$db = $this->database();
		$uri = mysqli_real_escape_string($db, $uri);
		$getShare = mysqli_prepare($db, "SELECT * FROM " . Config::dbTableWebshare . " WHERE uri=? LIMIT 1");
		mysqli_stmt_bind_param($getShare, "s", $uri);
		mysqli_stmt_execute($getShare);
		$share = mysqli_fetch_assoc(mysqli_stmt_get_result($getShare));
		mysqli_close($db);
		if ($share == null) return false;
		$this->uri = $share["uri"];
		$this->type = $share["type"];
		$this->value = $share["value"];
		$this->password = $share["password"];
		$this->expireDate = $share["expireDate"];
		$this->createDate = $share["createDate"];
		if ($this->type == "file") $this->file = $_SERVER["DOCUMENT_ROOT"] . Config::pathStorage . $this->uri;
		if (isset($this->expireDate) && strtotime($this->expireDate) < time()) {
			$this->deleteShare();
			return false;
		}
		return true;
	}
	public function redirectShare(Request $request): bool
	{
		if ($this->type == "link") {
			header("Location:" . $this->value);
			return true;
		}
		if ($this->type == "file") {
			if (!file_exists($this->file)) return false;
			if ($request->get("action") == "view") {
				header("Content-Disposition: inline; filename=" . $this->value);
				header("Content-Type: " . mime_content_type($this->file));
				header("Content-Length: " . filesize($this->file));
				readfile($this->file);
				return true;
			}
			if ($request->get("action") == "download") {
				header("Content-Disposition: attachment; filename=" . $this->value);
				header("Content-Type: " . mime_content_type($this->file));
				header("Content-Length: " . filesize($this->file));
				readfile($this->file);
				return true;
			}
			$page = new Pages($request, $this);
			return $page->viewPage();
		}
		return false;
	}
	public function listShares(string $sort)
	{
		$sortOptions = array(
			"uri" => "uri ASC",
			"type" => "type ASC",
			"value" => "value ASC",
			"password" => "password DESC",
			"expireDate" => "expireDate DESC",
			"createDate" => "createDate DESC",
		);
		$shareSort = $sortOptions[$sort];
		$db = $this->database();
		$listShares = mysqli_prepare($db, "SELECT * FROM " . Config::dbTableWebshare . " ORDER BY " . Config::dbTableWebshare . "." . $shareSort);
		mysqli_stmt_execute($listShares);
		$shares = mysqli_fetch_all(mysqli_stmt_get_result($listShares), MYSQLI_ASSOC);
		$shareList = "";
		foreach ($shares as $shareContent) {
			if (!empty($shareContent["password"])) {
				$sharePassword = "True";
			} else $sharePassword = "";
			$shareList .= "
				<tr>
					<td>" . Output::link($shareContent["uri"]) . "</a></td>
					<td>" . htmlspecialchars($shareContent["type"]) . "</td>
					<td>" . htmlspecialchars($shareContent["value"]) . "</td>
					<td>" . $sharePassword . "</td>
					<td>" . htmlspecialchars($shareContent["expireDate"]) . "</td>
					<td>" . htmlspecialchars($shareContent["createDate"]) . "</td>
					<td>" . Output::link($shareContent["uri"] . "?action=delete", "Delete") . "</td>
				</tr>
			";
		}
		return $shareList;
	}
	public function deleteShare(): bool
	{
		if (!Config::adminPageAccess()) return false;
		if ($this->type == "file") {
			$fileDelete = unlink(Config::pathStorage() . $this->uri);
			if (!$fileDelete) return false;
		}
		$db = $this->database();
		$deleteShare = mysqli_prepare($db, "DELETE FROM " . Config::dbTableWebshare . " WHERE uri=?");
		mysqli_stmt_bind_param($deleteShare, "s", $this->uri);
		$dbDelete = mysqli_stmt_execute($deleteShare);
		mysqli_close($db);
		if (!$dbDelete) return false;
		return true;
	}
	private function database(): \mysqli|false
	{
		$db = mysqli_connect(Config::dbHost, Config::dbUsername, Config::dbPassword, config::dbName);
		if (!$db) die("Database connection failed.");
		return $db;
	}
}
