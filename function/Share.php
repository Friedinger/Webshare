<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

*/

namespace Webshare;

final class Share
{
	private string $uri;
	private string $type;
	private string|array $value;
	private string|null $password;
	private string|null $expireDate;
	private string|null $createDate;

	public function __construct(string $uri, string $type, string|array $value, string|null $password, string|null $expireDate, string|null $createDate = null)
	{
		$this->uri = $uri;
		$this->type = $type;
		$this->value = $value;
		$this->password = $password;
		if ($password && !password_get_info($password)["algo"]) {
			$this->password = password_hash($password, PASSWORD_DEFAULT);
		}
		$this->expireDate = $expireDate;
		$this->createDate = $createDate;
	}

	public static function get(string $uri): Share|null
	{
		$query = "SELECT * FROM " . Config::DB_TABLE . " WHERE uri=:uri LIMIT 1";
		$params = ["uri" => $uri];
		$item = Database::query($query, $params)->fetch();
		if (!$item) return null;
		$share = new Share($item["uri"], $item["type"], $item["value"], $item["password"], $item["expireDate"], $item["createDate"]);
		if (isset($share->expireDate) && strtotime($share->expireDate) < time()) {
			$share->delete();
			return null;
		}
		return $share;
	}

	public function store(): Share|false
	{
		if ($this->type == "file") {
			if ($this->value["error"] != 0) return false;
			if (file_exists($this->filePath())) return false;
			try {
				move_uploaded_file($this->value["tmp_name"], $this->filePath());
			} catch (\Exception) {
				return false;
			}
			$this->value = $this->value["name"];
		}
		$query = "INSERT IGNORE INTO " . Config::DB_TABLE . " (uri, type, value, password, expireDate) VALUES (:uri, :type, :value, :password, :expireDate)";
		$params = [":uri" => $this->uri, ":type" => $this->type, ":value" => $this->value, ":password" => $this->password, ":expireDate" => $this->expireDate];
		$addShare = Database::query($query, $params);
		if ($addShare->rowCount() != 1) return false;
		return Share::get($this->uri) ?? false;
	}

	public function delete(): bool
	{
		if ($this->type == "file") {
			if (!file_exists($this->filePath())) return false;
			try {
				unlink($this->filePath());
			} catch (\Exception) {
				return false;
			}
		}
		$query = "DELETE FROM " . Config::DB_TABLE . " WHERE uri=:uri";
		$params = [":uri" => $this->uri];
		$deleteShare = Database::query($query, $params);
		return $deleteShare->rowCount() == 1;
	}

	public function redirect(): bool
	{
		if ($this->type == "link") {
			return $this->redirectLink();
		}
		if ($this->type == "file") {
			return $this->redirectFile();
		}
		return false;
	}

	private function redirectLink(): bool
	{
		// TODO: Add support for other protocols and default to https
		header("Location:" . $this->value);
		return true;
	}

	private function redirectFile(): bool
	{
		if (!file_exists($this->filePath())) return false;
		if (Request::get("action") == "view") {
			header("Content-Disposition: inline; filename=" . $this->value);
			header("Content-Type: " . mime_content_type($this->filePath()));
			header("Content-Length: " . filesize($this->filePath()));
			readfile($this->filePath());
		}
		if (Request::get("action") == "download") {
			header("Content-Disposition: attachment; filename=" . $this->value);
			header("Content-Type: " . mime_content_type($this->filePath()));
			header("Content-Length: " . filesize($this->filePath()));
			readfile($this->filePath());
		}
		return Page::view($this);
	}

	private function filePath(): string
	{
		return $_SERVER["DOCUMENT_ROOT"] . Config::PATH_STORAGE . $this->uri;
	}

	public static function list(string $sort): array
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
		$shareList = Database::query($query)->fetchAll();
		$shares = [];
		foreach ($shareList as $shareItem) {
			$share = new Share($shareItem["uri"], $shareItem["type"], $shareItem["value"], $shareItem["password"], $shareItem["expireDate"], $shareItem["createDate"]);
			array_push($shares, $share);
		}
		return $shares;
	}

	public function uri(): string
	{
		return $this->uri;
	}

	public function type(): string
	{
		return $this->type;
	}

	public function value(): string
	{
		return $this->value;
	}

	public function password($inputPassword = null): bool
	{
		if (isset($inputPassword)) {
			return password_verify($inputPassword, $this->password);
		}
		return isset($this->password);
	}

	public function expireDate(): string|null
	{
		return $this->expireDate;
	}

	public function createDate(): string|null
	{
		return $this->createDate;
	}
}
