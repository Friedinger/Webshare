<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

Version: 2.2

*/

namespace Webshare;

final class Webshare
{
	public function __construct()
	{
		$this->autoload();
		$request = new Request();
		$handle = $this->handleRequest($request);
		if (!$handle) Config::error404();
	}
	private function handleRequest(Request $request)
	{
		if ($request->uri() == "admin") {
			return (new Pages($request))->adminPage();
		}
		$share = new Share();
		$getShare = $share->getShare($request->uri());
		if (!$getShare) return false;
		if ($request->get("action") == "delete") {
			$deletePage =  (new Pages($request, $share))->deletePage();
			if ($deletePage) return true;
		}
		if ($share->password) {
			$passwordPage = (new Pages($request, $share))->passwordPage();
			if (!$passwordPage) return true;
		}
		return $share->redirectShare($request);
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
