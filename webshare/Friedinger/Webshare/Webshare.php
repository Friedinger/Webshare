<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

*/

namespace Friedinger\Webshare;

final class Webshare
{
	public function __construct()
	{
		$request = new Request($_SERVER["REQUEST_URI"]);
		$handle = $this->handleRequest($request);
		if (!$handle) {
			$page = new Pages($request);
			$page->error404Page();
		}
	}
	private function handleRequest(Request $request)
	{
		if (str_starts_with($request->uri(), "admin")) {
			$page = new Pages($request);
			return $page->adminPage();
		}
		$share = new Share();
		$getShare = $share->getShare($request->uri());
		if (!$getShare) return false;
		if ($share->password) {
			$page = new Pages($request, $share);
			$passwordPage = $page->passwordPage();
			if (!$passwordPage) return true;
		}
		if ($request->get("action") == "delete") {
			$page = new Pages($request, $share);
			return $page->deletePage();
		}
		return $share->redirectShare($request);
	}
}
