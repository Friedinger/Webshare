<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

https://github.com/Friedinger/Webshare

by Friedinger (friedinger.org)

*/

namespace Webshare;

/**
 * Class Share
 *
 * Represents a share object and provides functions to interact with it.
 */
class Share
{
	private string $uri;
	private string $type;
	private string|array $value;
	private string|null $password;
	private string|null $expireDate;
	private string|null $createDate;

	/**
	 * Share constructor.
	 * Creates a new share object with the given parameters.
	 *
	 * @param string $uri The URI of the share.
	 * @param string $type The type of the share (link or file).
	 * @param string|array $value The value of the share (link URL or file data).
	 * @param string|null $password The password of the share.
	 * @param string|null $expireDate The expiration date of the share.
	 * @param string|null $createDate The creation date of the share.
	 */
	public function __construct(string $uri, string $type, string|array $value, string|null $password, string|null $expireDate, string|null $createDate = null)
	{
		// Set properties based on constructor parameters
		$this->uri = $uri;
		$this->type = $type;
		$this->expireDate = $expireDate;
		$this->createDate = $createDate;

		// Add https:// to link value if not present
		if ($this->type == "link" && !preg_match("/^[a-zA-Z]+:\/\//", $value)) {
			$this->value = "https://$value";
		} else {
			$this->value = $value;
		}

		// Hash password if not already hashed
		if ($password && !password_get_info($password)["algo"]) {
			$this->password = password_hash($password, PASSWORD_DEFAULT);
		} else {
			$this->password = $password;
		}
	}

	/**
	 * Returns a share object based on the given URI.
	 *
	 * @param string $uri The URI of the share to get.
	 * @return Share|null The share object if it exists, null otherwise.
	 */
	public static function get(string $uri): Share|null
	{
		// Get share from database
		$query = "SELECT * FROM " . Config::DB_TABLE . " WHERE uri=:uri LIMIT 1";
		$params = ["uri" => $uri];
		$item = Database::query($query, $params)->fetch();
		if (!$item) return null; // Return null if share does not exist

		$share = new Share($item["uri"], $item["type"], $item["value"], $item["password"], $item["expireDate"], $item["createDate"]); // Create share object
		if (isset($share->expireDate) && strtotime($share->expireDate) < time()) {
			$share->delete(); // Delete share if expired
			return null;
		}
		return $share; // Return share object
	}

	/**
	 * Stores the share object in the database.
	 *
	 * @return Share|false The stored share object if successful, false otherwise.
	 */
	public function store(): Share|false
	{
		// Store file if share type is file
		if ($this->type == "file") {
			if ($this->value["error"] != 0) return false; // Return false on upload errors
			if (file_exists($this->filePath())) return false; // Return false if file already exists
			try {
				move_uploaded_file($this->value["tmp_name"], $this->filePath()); // Move uploaded file to storage
			} catch (\Exception) {
				return false; // Return false on file move error
			}
			$this->value = $this->value["name"]; // Set file name as share value
		}

		// Store share in database
		$query = "INSERT IGNORE INTO " . Config::DB_TABLE . " (uri, type, value, password, expireDate) VALUES (:uri, :type, :value, :password, :expireDate)";
		$params = [":uri" => $this->uri, ":type" => $this->type, ":value" => $this->value, ":password" => $this->password, ":expireDate" => $this->expireDate];
		$addShare = Database::query($query, $params);
		if ($addShare->rowCount() != 1) return false; // Return false if share was not added to database

		return Share::get($this->uri) ?? false; // Return share object if it exists, false otherwise
	}

	/**
	 * Deletes the share object from the database and storage.
	 *
	 * @return bool True if the share was deleted successfully, false otherwise.
	 */
	public function delete(): bool
	{
		// Delete file if share type is file
		if ($this->type == "file") {
			if (!file_exists($this->filePath())) return false; // Return false if file does not exist
			try {
				unlink($this->filePath()); // Delete file from storage
			} catch (\Exception) {
				return false; // Return false on file delete error
			}
		}

		// Delete share from database
		$query = "DELETE FROM " . Config::DB_TABLE . " WHERE uri=:uri";
		$params = [":uri" => $this->uri];
		$deleteShare = Database::query($query, $params);

		return $deleteShare->rowCount() == 1; // Return if share was deleted successfully or not
	}

	/**
	 * Redirects the user to the share value.
	 *
	 * @return bool True if the user was redirected successfully, false otherwise.
	 */
	public function redirect(): bool
	{
		// Redirect based on share type
		if ($this->type == "link") {
			return $this->redirectLink();
		} elseif ($this->type == "file") {
			return $this->redirectFile();
		}
		return false; // Return false if share type is invalid
	}

	private function redirectLink(): bool
	{
		header("Location: $this->value/"); // Redirect to link value
		return true;
	}

	private function redirectFile(): bool
	{
		if (!file_exists($this->filePath())) return false; // Return false if file does not exist

		// Display or download file based on action
		if (Request::get("action") == "view") {
			header("Content-Disposition: inline; filename=$this->value"); // Display file in browser
		} elseif (Request::get("action") == "download") {
			header("Content-Disposition: attachment; filename=$this->value"); // Download file
		}
		if (Request::get("action") == "view" || Request::get("action") == "download") {
			header("Content-Type: " . mime_content_type($this->filePath())); // Set content type based on file mime type
			header("Content-Length: " . filesize($this->filePath())); // Set content length based on file size
			readfile($this->filePath()); // Output file content
			return true; // Return true if file was displayed or downloaded
		}

		return Page::view($this); // Display view page if no action is set
	}

	/**
	 * Gets the file path of the share.
	 *
	 * @return string The file path of the share.
	 */
	public function filePath(): string
	{
		if ($this->type != "file") return ""; // Return empty string if share type is not file
		return $_SERVER["DOCUMENT_ROOT"] . Config::PATH_STORAGE . $this->uri; // Get file path based on share URI
	}

	/**
	 * Lists all shares based on the given sort option.
	 *
	 * @param string $sort The sort option to use.
	 * @return array The list of share objects.
	 */
	public static function list(string $sort): array
	{
		$sortOptions = [
			// List of sort options, if column is sorted ascending or descending
			"uri" => "uri ASC",
			"type" => "type ASC",
			"value" => "value ASC",
			"password" => "password DESC",
			"expireDate" => "expireDate DESC",
			"createDate" => "createDate DESC",
		];
		$shareSort = $sortOptions[$sort]; // Get sort option based on input

		// Get all shares from database
		$query = "SELECT * FROM " . Config::DB_TABLE . " ORDER BY " . Config::DB_TABLE . "." . $shareSort;
		$shareList = Database::query($query)->fetchAll();

		// Create list of share objects based on database results
		$shares = [];
		foreach ($shareList as $shareItem) {
			$share = new Share($shareItem["uri"], $shareItem["type"], $shareItem["value"], $shareItem["password"], $shareItem["expireDate"], $shareItem["createDate"]);
			array_push($shares, $share);
		}

		return $shares; // Return list of share objects
	}

	/**
	 * Gets the URI of the share.
	 *
	 * @return string The URI of the share.
	 */
	public function uri(): string
	{
		return htmlspecialchars($this->uri);
	}

	/**
	 * Gets the type of the share.
	 *
	 * @return string The type of the share.
	 */
	public function type(): string
	{
		return htmlspecialchars($this->type);
	}

	/**
	 * Gets the value of the share.
	 *
	 * @return string The value of the share.
	 */
	public function value(): string
	{
		return htmlspecialchars($this->value);
	}

	/**
	 * Checks if the share has a password set or verifies the entered password.
	 *
	 * @param string|null $inputPassword The password to verify.
	 * @return bool True if the share has a password set or the entered password is correct, false otherwise.
	 */
	public function password($inputPassword = null): bool
	{
		if (!is_null($inputPassword) && !is_null($this->password)) {
			// Verify entered password
			return password_verify($inputPassword, $this->password);
		} else {
			// Check if share has password set
			return isset($this->password);
		}
	}

	/**
	 * Gets the expiration date of the share.
	 * If no expiration date is set, null is returned.
	 *
	 * @return string|null The expiration date of the share.
	 */
	public function expireDate(): string|null
	{
		if (is_null($this->expireDate)) return null; // Return null if no expiration date is set
		return htmlspecialchars($this->expireDate); // Get expiration date of share
	}

	/**
	 * Gets the creation date of the share.
	 * If no creation date is set, null is returned.
	 *
	 * @return string|null The creation date of the share.
	 */
	public function createDate(): string|null
	{
		if (is_null($this->createDate)) return null; // Return null if no creation date is set
		return htmlspecialchars($this->createDate); // Get creation date of share
	}
}
