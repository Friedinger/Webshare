<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

Version: 2.3

*/

namespace Webshare;

final class Webshare
{
	public function __construct()
	{
		$this->autoload();
		$handle = $this->handleRequest();
		if (!$handle) Config::error404();
	}
	private function handleRequest()
	{
		if (Request::uri() == "admin") {
			return (new Pages())->adminPage();
		}
		$share = new Share();
		$getShare = $share->getShare(Request::uri());
		if (!$getShare) return false;
		if (Request::get("action") == "delete") {
			$deletePage =  (new Pages($share))->deletePage();
			if ($deletePage) return true;
		}
		if ($share->password) {
			$passwordPage = (new Pages($share))->passwordPage();
			if (!$passwordPage) return true;
		}
		return $share->redirectShare();
	}
	private function autoload()
	{
		spl_autoload_register(function ($class) {
			if (str_starts_with($class, __NAMESPACE__ . "\\")) {
				$class = str_replace(__NAMESPACE__ . "\\", "", $class);
				require_once("{$class}.php");
			}
		});
	}
}
