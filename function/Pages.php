<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

*/

namespace Webshare;

final class Pages
{
	private Share $share;
	public function __construct(Share $share = null)
	{
		$this->share = $share ?? new Share();
		$this->outputShareInfo();
	}
	public function adminPage(): bool
	{
		if (!Config::adminAccess()) {
			Config::noAdminAccess();
			return true;
		}
		if (Request::post("submit")) {
			Output::$status = $this->share->addShare();
			$this->outputShareInfo();
		}
		Output::$shareList = $this->share->listShares(Request::get("sort") ?? "createDate");
		$this->loadPage("admin");
		return true;
	}
	public function viewPage(): bool
	{
		$mime = mime_content_type($this->share->file);
		Output::$sharePreview = "<iframe src='?action=view' title='" . $this->share->value . "'></iframe>";
		if (str_starts_with($mime, "text/")) {
			Output::$sharePreview  = "<code>" . str_replace("\n", "<br>", file_get_contents($this->share->file)) . "</code>";
		}
		if (str_starts_with($mime, "image/")) {
			Output::$sharePreview  = "<img src='?action=view' alt='" . $this->share->value . "'></img>";
		}
		if (str_starts_with($mime, "audio/")) {
			Output::$sharePreview  = "<audio controls src='?action=view'></audio>";
		}
		if (str_starts_with($mime, "video/")) {
			Output::$sharePreview  = "<video controls src='?action=view'></video>";
		}
		$this->loadPage("view");
		return true;
	}
	public function passwordPage(): bool
	{
		if (Request::session("webshare", $this->share->uri)) return true;
		$inputPassword = Request::post("password");
		if ($inputPassword) {
			if (password_verify($inputPassword, $this->share->password)) {
				$_SESSION["webshare"][$this->share->uri] = true;
				return true;
			}
			Output::$status = "incorrect";
		} else Output::$status = "default";
		$this->loadPage("password");
		return false;
	}
	public function deletePage(): bool
	{
		if (!Config::adminAccess()) return false;
		if (Request::post("deleteShare")) {
			Output::$status = $this->share->deleteShare();
		}
		$this->loadPage("delete");
		return true;
	}
	private function loadPage(string $page): bool
	{
		switch ($page) {
			case "admin":
				$path = Config::PATH_ADMIN;
				break;
			case "view":
				$path = Config::PATH_VIEW;
				break;
			case "password":
				$path = Config::PATH_PASSWORD;
				break;
			case "delete":
				$path = Config::PATH_DELETE;
				break;
			default:
				return false;
		}
		header("Content-Type: text/html");
		require($_SERVER["DOCUMENT_ROOT"] . $path);
		return true;
	}
	private function outputShareInfo(): void
	{
		Output::$uri = $this->share->uri ?? null;
		Output::$value = $this->share->value ?? null;
		Output::$expireDate = $this->share->expireDate ?? null;
		Output::$createDate = $this->share->createDate ?? null;
	}
}
