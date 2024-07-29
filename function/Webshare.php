<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

Version: 3.0.2

*/

namespace Webshare;

/**
 * Class Webshare
 *
 * Initializes the application and handles the request.
 */
class Webshare
{
	/**
	 * Webshare constructor.
	 * Autoloads classes and handles the request.
	 */
	public function __construct()
	{
		$this->autoload();
		$handle = $this->handleRequest();
		if (!$handle) {
			Config::error404();
		}
	}

	private function handleRequest(): bool
	{
		// Load admin page if link is admin link
		if (Request::uri() == Config::ADMIN_LINK) {
			return Page::admin();
		}

		// Load additional file if URI is in includes array
		if (isset(Config::PATH_INCLUDES[Request::uri()])) {
			return Page::include(Config::PATH_INCLUDES[Request::uri()]);
		}

		$share = Share::get(Request::uri()); // Get share based on URI
		if (!$share) return false; // Return 404 if share does not exist

		if (Request::get("action") == "delete") {
			$delete = Page::delete($share); // Load delete page if action is delete
			if ($delete) return true; // Return true if request was handled by delete page
		}
		if ($share->password()) {
			$password = Page::password($share); // Load password page if share has password
			if ($password) return true; // Return true if request was handled by password page
		}
		return $share->redirect(); // Redirect to share value
	}

	private function autoload()
	{
		// Autoload classes from the Webshare namespace
		spl_autoload_register(function ($class) {
			if (str_starts_with($class, __NAMESPACE__ . "\\")) {
				$class = str_replace(__NAMESPACE__ . "\\", "", $class);
			}
			require_once "{$class}.php";
		});
	}
}
