<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

*/

namespace Webshare;

final class Page
{
	public static function admin(): bool
	{
		if (!Config::adminAccess()) {
			Config::noAdminAccess();
			return false;
		}
		$output = new Output(Config::PATH_ADMIN);
		$status = "";
		if (Request::post("submit")) {
			if (Request::post("link") && !Request::file("file", "size")) {
				$type = "link";
				$value = Request::post("link");
			} elseif (Request::file("file", "size") && !Request::post("link")) {
				$type = "file";
				$value = Request::file("file");
			}
			if (isset($type) && isset($value)) {
				$share = new Share(Request::post("uri"), $type, $value, Request::post("password"), Request::post("expireDate"));
				try {
					$share->store();
				} catch (ShareException $exception) {
					$status = Config::TEXT_ADMIN["error"] . $exception->getMessage();
				}
			} else {
				$status = Config::TEXT_ADMIN["errorBoth"];
			}
		}
		$output->replace("share-status", $status, "xml");
		$shares = Share::list(Request::get("sort") ?? "createDate");
		$content = $output->getContent("share-list") . "<share-list />";
		foreach ($shares as $share) {
			$output->replace("share-list", $content, "xml");
			$output->replaceCommon($share);
		}
		$output->printPage();
		return true;
	}

	public static function view($share): bool
	{
		$output = new Output(Config::PATH_VIEW);
		$file = $_SERVER["DOCUMENT_ROOT"] . Config::PATH_STORAGE . $share->uri();
		$mime = mime_content_type($file);
		if (str_starts_with($mime, "text/")) {
			$output->replace("share-preview", htmlspecialchars(str_replace("\n", "<br>", file_get_contents($file))), "code");
		} elseif (str_starts_with($mime, "image/")) {
			$output->replace("share-preview", $share->value(), "img");
		} elseif (str_starts_with($mime, "audio/")) {
			$output->replace("share-preview", $share->value(), "audio");
		} elseif (str_starts_with($mime, "video/")) {
			$output->replace("share-preview", $share->value(), "video");
		} else {
			$output->replace("share-preview", $share->value(), "iframe");
		}
		$output->printPage($share);
		return true;
	}

	public static function password($share): bool
	{
		if (Request::session("webshare", $share->uri())) return false;
		if (Request::post("submit")) {
			$inputPassword = Request::post("password") ?? "";
			if ($share->password($inputPassword)) {
				$_SESSION["webshare"][$share->uri()] = true;
				Request::setSession("webshare", $share->uri(), true);
				return false;
			}
			$status = Config::TEXT_PASSWORD["incorrect"];
		} else $status = Config::TEXT_PASSWORD["default"];
		$output = new Output(Config::PATH_PASSWORD);
		$output->replace("share-status", $status, "xml");
		$output->printPage($share);
		return true;
	}

	public static function delete($share): bool
	{
		if (!Config::adminAccess()) return false;
		$status = "";
		$output = new Output(Config::PATH_DELETE);
		if (Request::post("submit")) {
			if (Request::post("uri") == $share->uri()) {
				try {
					$share->delete();
					$status = Config::TEXT_DELETE["success"];
					$output->replace("share-form", "");
				} catch (ShareException $exception) {
					$status = Config::TEXT_DELETE["error"] . " (" . $exception->getMessage() . ")";
				}
			} else {
				$status = Config::TEXT_DELETE["errorInput"];
			}
		}
		$output->replace("share-status", $status, "xml");
		$output->printPage($share);
		return true;
	}

	public static function include(string $path): bool
	{
		$file = $_SERVER["DOCUMENT_ROOT"] . $path;
		if (!file_exists($file)) return false;

		$mimeTypes = array(
			"php" => "text/html",
			"html" => "text/html",
			"css" => "text/css",
			"js" => "application/x-javascript",
			"ico" => "image/x-icon",
			"vbs" => "application/x-vbs",
		);
		$extension = pathinfo($file, PATHINFO_EXTENSION);
		$mime = $mimeTypes[$extension] ?? mime_content_type($file);

		header("Content-Type: " . $mime . "; charset=utf-8");
		if ($mime == "text/html") {
			require($file);
		} else {
			readfile($file);
		}
		return true;
	}
}
