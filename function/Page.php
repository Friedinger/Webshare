<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

*/

namespace Webshare;

/**
 * Class Page
 *
 * Contains functions to load and display pages.
 */
class Page
{
	/**
	 * Loads and displays the admin page, handling form submissions.
	 *
	 * @return bool True if the page was loaded successfully, false otherwise.
	 */
	public static function admin(): bool
	{
		// Check if user has admin access
		if (!Config::adminAccess()) {
			Config::noAdminAccess();
			return false; // Return false if user has no admin access
		}

		// Load admin page
		$output = new Output($_SERVER["DOCUMENT_ROOT"] . Config::PATH_ADMIN);
		$status = Config::TEXT_ADMIN["default"]; // Set default status message

		// Handle form submission
		if (Request::post("submit")) {
			$status = self::adminSubmit(); // Handle form submission and get status message
		}

		$output->replaceAll("share-status", $status); // Display status message

		// List shares based on sort parameter
		$shares = Share::list(Request::get("sort") ?? "createDate"); // Get share list from database
		$content = $output->getContent("share-list") . "<share-list />"; // Get share list template
		foreach ($shares as $share) { // Loop through share list to display all shares
			$output->replaceAll("share-list", $content); // Replace share list template with one share content
			$output = self::replaceCommon($output, $share); // Replace common placeholders with share data
		}
		$output->replaceAll("share-list", ""); // Remove last share list template
		$output = self::replaceCommon($output); // Replace common placeholders without share data

		$output->print(); // Print admin page
		return true; // Return true if page was loaded successfully
	}

	private static function adminSubmit(): string
	{
		// Validate URI
		if (!Request::post("uri")) return Config::TEXT_ADMIN["error_uri_empty"]; // Return error message if URI is empty
		$share = Share::get(Request::post("uri")); // Get share based on URI
		if (!is_null($share)) return Config::TEXT_ADMIN["error_uri_used"]; // Return error message if URI is already used

		// Validate type and set share type and value based on input
		if (Request::post("link") && !Request::file("file", "size")) { // Link input without file upload -> link share
			$type = "link";
			$value = Request::post("link");
		} elseif (Request::file("file", "size") && !Request::post("link")) { // File upload without link input -> file share
			$type = "file";
			$value = Request::file("file");
		} elseif (Request::post("link") && Request::file("file", "size")) { // Both link input and file upload -> error
			return Config::TEXT_ADMIN["error_type_both"];
		} else { // No link input and no file upload -> error
			return Config::TEXT_ADMIN["error_type_none"];
		}

		// Store share
		$share = new Share(Request::post("uri"), $type, $value, Request::post("password"), Request::post("expireDate")); // Create new share object
		$store = $share->store(); // Store share in database
		if (!$store) return Config::TEXT_ADMIN["error_store"]; // Return error message if share could not be stored

		// Return success message
		$output = new Output(Config::TEXT_ADMIN["success"], true); // Create new output object with success message
		$output = self::replaceCommon($output, $share); // Output and replace share data
		return $output->getContent();
	}

	/**
	 * Loads and displays the view page for a share.
	 *
	 * @param Share $share The share to display.
	 * @return bool True if the page was loaded successfully, false otherwise.
	 */
	public static function view($share): bool
	{
		// Load view page to output object
		$output = new Output($_SERVER["DOCUMENT_ROOT"] . Config::PATH_VIEW);

		// Replace share preview based on MIME type
		$mime = mime_content_type($share->filePath()); // Get MIME type of file
		$src = Config::INSTALL_PATH . $share->uri() . "/?action=view"; // Get source URL for file
		if (str_starts_with($mime, "text/")) {
			// Text file displayed as code
			$replace = "<code>" . str_replace("\n", "<br>", htmlspecialchars(file_get_contents($share->filePath()))) . "</code>";
		} elseif (str_starts_with($mime, "image/")) {
			// Image file
			$replace = "<img src='" . $src . "' alt='" . $share->value() . "' />";
		} elseif (str_starts_with($mime, "audio/")) {
			// Audio file
			$replace = "<audio controls><source src='" . $src . "' type='" . $mime . "' alt='" . $share->value() . "'></audio>";
		} elseif (str_starts_with($mime, "video/")) {
			// Video file
			$replace = "<video controls><source src='" . $src . "' type='" . $mime . "' alt='" . $share->value() . "'></video>";
		} else {
			// Default file displayed as iframe
			$replace = "<iframe src='" . $src . "' alt='" . $share->value() . "'></iframe>";
		}
		$output->replaceAll("share-preview", $replace); // Replace share preview with file content in html frame

		$output = self::replaceCommon($output, $share); // Output and replace share data
		$output->print(); // Print view page
		return true;
	}

	/**
	 * Loads and displays the password page for a share.
	 *
	 * @param Share $share The share to display.
	 * @return bool True if the page was loaded successfully, false otherwise.
	 */
	public static function password($share): bool
	{
		if (Request::session("webshare", $share->uri())) return false; // If password was already entered, redirect directly without password page
		$status = Config::TEXT_PASSWORD["default"]; // Set default status message

		// Handle form submission
		if (Request::post("submit")) {
			$inputPassword = Request::post("password") ?? ""; // Get input password
			if ($share->password($inputPassword)) { // Check if input password is correct
				Request::setSession(true, "webshare", $share->uri()); // Set session to remember password
				return false; // Return false to redirect to share
			} else {
				$status = Config::TEXT_PASSWORD["incorrect"]; // Set incorrect password status message
			}
		}

		// Load password page
		$output = new Output($_SERVER["DOCUMENT_ROOT"] . Config::PATH_PASSWORD);
		$output->replaceAll("share-status", $status); // Display status message
		$output = self::replaceCommon($output, $share); // Output and replace share data
		$output->print(); // Print password page
		return true;
	}

	/**
	 * Loads and displays the delete page for a share.
	 *
	 * @param Share $share The share to delete.
	 * @return bool True if the page was loaded successfully, false otherwise.
	 */
	public static function delete($share): bool
	{
		if (!Config::adminAccess()) return false; // Don't show page if user has no admin access
		$status = Config::TEXT_DELETE["default"]; // Set default status message

		$output = new Output($_SERVER["DOCUMENT_ROOT"] . Config::PATH_DELETE); // Load delete page

		// Handle form submission
		if (Request::post("submit")) {
			if (Request::post("uri") == $share->uri()) { // Check if entered URI matches share URI
				$delete = $share->delete(); // Delete share from database
				if ($delete) {
					$status = Config::TEXT_DELETE["success"]; // Set success status message
					$output->replaceAll("share-form", ""); // Remove form after successful deletion
				} else {
					$status = Config::TEXT_DELETE["error"]; // Set error status message
				}
			} else {
				$status = Config::TEXT_DELETE["error_input"]; // Set error status message if entered URI does not match share URI
			}
		}

		$output->replaceAll("share-status", $status); // Display status message
		$output = self::replaceCommon($output, $share); // Output and replace share data
		$output->print(); // Print delete page
		return true;
	}

	/**
	 * Includes a file from the given path and outputs it.
	 *
	 * @param string $path The path to the file to include.
	 * @return bool True if the file was included successfully, false otherwise.
	 */
	public static function include(string $path): bool
	{
		$file = $_SERVER["DOCUMENT_ROOT"] . $path; // Get full path to file
		if (!file_exists($file)) return false; // Return false if file does not exist

		$mime = Misc::getMime($file); // Get MIME type of file
		header("Content-Type: " . $mime . "; charset=utf-8"); // Set content type header based on MIME type
		if ($mime == "text/html" && Config::ALLOW_PAGE_PHP) {
			require $file; // Include file if it is a PHP file and PHP pages are allowed
		} else {
			readfile($file); // Output file content
		}
		return true; // Return true if file was included successfully
	}

	private static function replaceCommon(Output $output, Share $share = null): Output
	{
		// Replace placeholders that can be used without share data
		$output->replaceAllSafe("share-installPath", Config::INSTALL_PATH);
		$output->replaceAllSafe("share-adminLink", Config::ADMIN_LINK);
		if (is_null($share)) return $output; // Return output object with replaced placeholders if no share data is given

		// Replace placeholders that need share data
		$output->replaceAllSafe("share-uri", $share->uri());
		$output->replaceAllSafe("share-type", $share->type());
		$output->replaceAllSafe("share-value", $share->value());
		$output->replaceAllSafe("share-password", $share->password() ? Config::TEXT_OUTPUT["passwordIsSet"] : Config::TEXT_OUTPUT["passwordNotSet"]);
		$output->replaceAllSafe("share-expire", $share->expireDate() ?? Config::TEXT_OUTPUT["noExpireDate"]);
		$output->replaceAllSafe("share-create", $share->createDate() ?? "");
		$output->replaceAllSafe("share-url", Config::INSTALL_PATH . $share->uri());
		$output->replaceAllSafe("share-urlFull", Misc::baseUrl() . $share->uri());
		return $output; // Return output object with replaced placeholders
	}
}
