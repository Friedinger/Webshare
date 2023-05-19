<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

Version: 2.0.2

*/

namespace Friedinger\Webshare;

require_once("Request.php");
require_once("Share.php");
require_once("Pages.php");
require_once("Output.php");

final class Webshare
{
	public function __construct()
	{
		$request = new Request($_SERVER["REQUEST_URI"]);
		$handle = $this->handleRequest($request);
		if (!$handle) Config::error404();
	}
	private function handleRequest(Request $request)
	{
		if ($request->uri() == "admin") {
			$page = new Pages($request);
			return $page->adminPage();
		}
		$share = new Share();
		$getShare = $share->getShare($request->uri());
		if (!$getShare) return false;
		if ($request->get("action") == "delete") {
			$page = new Pages($request, $share);
			$deletePage = $page->deletePage();
			if ($deletePage) return true;
		}
		if ($share->password) {
			$page = new Pages($request, $share);
			$passwordPage = $page->passwordPage();
			if (!$passwordPage) return true;
		}
		return $share->redirectShare($request);
	}
}
