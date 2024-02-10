<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

*/

namespace Webshare;

final class Share
{
	public string|null $uri;
	public string|null $type;
	public string|null $value;
	public string|null $password;
	public string|null $expireDate;
	public string|null $createDate;
	public string|null $file;
	private Database $db;
	public function __construct()
	{
		$this->db = new Database();
	}
	public function addShare(Request $request): string
	{
		$this->uri = htmlspecialchars(preg_replace("/[^a-z0-9_-]/", "", strtolower($request->post("uri"))));
		if ($this->uri == "admin" || strlen($this->uri) > 255) return "errorUri";

		$this->expireDate = htmlspecialchars($request->post("expireDate"));
		if (empty($this->expireDate)) $this->expireDate = null;

		$this->password = htmlspecialchars($request->post("password"));
		if (!empty($this->password)) {
			$this->password = password_hash($this->password, PASSWORD_DEFAULT);
		} else $this->password = null;

		if (!empty($request->post("link")) && empty($request->file("file", "size"))) {
			$this->type = "link";
			$this->value = htmlspecialchars(urldecode($request->post("link")));
			if (!preg_match("/^https?:\/\//", $this->value)) $this->value = "https://" . $this->value;
			return $this->addShareToDatabase();
		}
		if (!empty($request->file("file", "size")) && empty($request->post("link"))) {
			$fileUpload = $request->file("file");
			switch ($fileUpload["error"]) {
				case 0:
					$this->type = "file";
					$this->value = htmlspecialchars($fileUpload["name"]);
					move_uploaded_file($fileUpload["tmp_name"], $this->pathFile());
					return $this->addShareToDatabase();
				case 1:
					return "errorUploadSize";
				default:
					return "error";
			}
		}
		if (!empty($request->post("link")) && !empty($request->file("file", "size"))) return "errorBoth";
		return "error";
	}
	private function addShareToDatabase(): string
	{
		if (strlen($this->value) > 255) return "error";
		$query = "INSERT IGNORE INTO " . Config::DB_TABLE . " (uri, type, value, password, expireDate) VALUES (:uri, :type, :value, :password, :expireDate)";
		$params = [":uri" => $this->uri, ":type" => $this->type, ":value" => $this->value, ":password" => $this->password, ":expireDate" => $this->expireDate];
		$addShare = $this->db->query($query, $params)->fetch();
		if (!$addShare) return "success";
		return "errorUri";
	}
	public function getShare(string $uri): bool
	{
		$uri = htmlspecialchars($uri);
		$query = "SELECT * FROM " . Config::DB_TABLE . " WHERE uri=:uri LIMIT 1";
		$params = ["uri" => $uri];
		$share = $this->db->query($query, $params)->fetch();
		if (!$share) return false;
		$this->uri = $share["uri"];
		$this->type = $share["type"];
		$this->value = $share["value"];
		$this->password = $share["password"];
		$this->expireDate = $share["expireDate"];
		$this->createDate = $share["createDate"];
		if ($this->type == "file") $this->file = $this->pathFile();
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
		$query = "SELECT * FROM " . Config::DB_TABLE . " ORDER BY " . Config::DB_TABLE . "." . $shareSort;
		$shares = $this->db->query($query)->fetchAll();
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
	public function deleteShare(): string
	{
		if (!Config::adminAccess()) return "error";
		if ($this->type == "file") {
			if (!file_exists($this->pathFile())) return "error";
			unlink($this->pathFile());
		}
		$query = "DELETE FROM " . Config::DB_TABLE . " WHERE uri=:uri";
		$params = [":uri" => $this->uri];
		$deleteShare = $this->db->query($query, $params);
		if ($deleteShare->rowCount()) return "success";
		return "error";
	}
	private function pathFile(): string
	{
		return $_SERVER["DOCUMENT_ROOT"] . Config::PATH_STORAGE . $this->uri;
	}
}
