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

	public function store(): void
	{
		if ($this->type == "file") {
			if ($this->value["error"] == 1) throw new ShareException("File size limit exceeded");
			if ($this->value["error"] != 0) throw new ShareException("Error while uploading file");
			if (file_exists($this->filePath())) throw new ShareException("File already exists");
			$move = move_uploaded_file($this->value["tmp_name"], $this->filePath());
			if (!$move) throw new ShareException("Error while storing file");
			$this->value = $this->value["name"];
		}
		$query = "INSERT IGNORE INTO " . Config::DB_TABLE . " (uri, type, value, password, expireDate) VALUES (:uri, :type, :value, :password, :expireDate)";
		$params = [":uri" => $this->uri, ":type" => $this->type, ":value" => $this->value, ":password" => $this->password, ":expireDate" => $this->expireDate];
		$addShare = Database::query($query, $params);
		if ($addShare->rowCount() != 1) {
			throw new ShareException("Error while storing share to database");
		}
	}

	public function delete(): void
	{
		if ($this->type == "file") {
			if (!file_exists($this->filePath())) {
				throw new ShareException("File not found");
			}
			unlink($this->filePath());
		}
		$query = "DELETE FROM " . Config::DB_TABLE . " WHERE uri=:uri";
		$params = [":uri" => $this->uri];
		$deleteShare = Database::query($query, $params);
		if ($deleteShare->rowCount() != 1) {
			throw new ShareException("Error while deleting share from database");
		}
	}

	public function redirect(): void
	{
		if ($this->type == "link") {
			$this->redirectLink();
		}
		if ($this->type == "file") {
			$this->redirectFile();
		}
	}

	private function redirectLink(): void
	{
		header("Location:" . $this->value);
	}

	private function redirectFile(): void
	{
		if (!file_exists($this->filePath())) {
			throw new ShareException("File not found");
		}
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
		Page::view($this);
	}

	private function filePath(): string
	{
		if ($this->type == "file") {
			return $_SERVER["DOCUMENT_ROOT"] . Config::PATH_STORAGE . $this->uri;
		} else {
			throw new ShareException("Share is not a file");
		}
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

	public static function get(string $uri): Share
	{
		$query = "SELECT * FROM " . Config::DB_TABLE . " WHERE uri=:uri LIMIT 1";
		$params = ["uri" => $uri];
		$item = Database::query($query, $params)->fetch();
		if (!$item) {
			throw new ShareException("Share not found");
		}
		$share = new Share($item["uri"], $item["type"], $item["value"], $item["password"], $item["expireDate"], $item["createDate"]);
		if (isset($share->expireDate) && strtotime($share->expireDate) < time()) {
			$share->delete();
			throw new ShareException("Share expired");
		}
		return $share;
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
}
