<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

Version: 3.0

*/

namespace Webshare;

final class Webshare
{
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
		if (Request::uri() == Config::ADMIN_LINK) {
			return Page::admin();
		}
		if (isset(Config::PATH_INCLUDES[Request::uri()])) {
			return Page::include(Config::PATH_INCLUDES[Request::uri()]);
		}
		try {
			$share = Share::get(Request::uri());
		} catch (ShareException) {
			return false;
		}
		if (Request::get("action") == "delete") {
			$delete = Page::delete($share);
			if ($delete) return true;
		}
		if ($share->password()) {
			$password = Page::password($share);
			if ($password) return true;
		}
		try {
			$share->redirect();
			return true;
		} catch (ShareException) {
			return false;
		}
	}

	private function autoload()
	{
		spl_autoload_register(function ($class) {
			if (str_starts_with($class, __NAMESPACE__ . "\\")) {
				$class = str_replace(__NAMESPACE__ . "\\", "", $class);
			}
			if (str_ends_with($class, "Exception")) {
				$class = "Exception";
			}
			require_once("{$class}.php");
		});
	}
}
