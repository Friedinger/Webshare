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
		$output = new Output($_SERVER["DOCUMENT_ROOT"] . Config::PATH_ADMIN);
		$status = Config::TEXT_ADMIN["default"];
		if (Request::post("submit")) {
			$status = self::adminSubmit();
		}
		$output->replaceAll("share-status", $status);
		$shares = Share::list(Request::get("sort") ?? "createDate");
		$content = $output->getContent("share-list") . "<share-list />";
		foreach ($shares as $share) {
			$output->replaceAll("share-list", $content);
			$output = self::replaceCommon($output, $share);
		}
		$output->replaceAll("share-list", "");
		$output = self::replaceCommon($output);
		$output->print();
		return true;
	}

	private static function adminSubmit(): string
	{
		// Validate URI
		if (!Request::post("uri")) return Config::TEXT_ADMIN["error_uri_empty"];
		$share = Share::get(Request::post("uri"));
		if (!is_null($share)) return Config::TEXT_ADMIN["error_uri_used"];

		// Validate type
		if (Request::post("link") && !Request::file("file", "size")) {
			$type = "link";
			$value = Request::post("link");
		} elseif (Request::file("file", "size") && !Request::post("link")) {
			$type = "file";
			$value = Request::file("file");
		} elseif (Request::post("link") && Request::file("file", "size")) {
			return Config::TEXT_ADMIN["error_type_both"];
		} else {
			return Config::TEXT_ADMIN["error_type_none"];
		}

		// Store share
		$share = new Share(Request::post("uri"), $type, $value, Request::post("password"), Request::post("expireDate"));
		$store = $share->store();
		if (!$store) return Config::TEXT_ADMIN["error_store"];
		$output = new Output(Config::TEXT_ADMIN["success"], true);
		$output = self::replaceCommon($output, $share);
		return $output->getContent();
	}

	public static function view($share): bool
	{
		$output = new Output($_SERVER["DOCUMENT_ROOT"] . Config::PATH_VIEW);
		$file = $_SERVER["DOCUMENT_ROOT"] . Config::PATH_STORAGE . $share->uri();
		$mime = mime_content_type($file);
		if (str_starts_with($mime, "text/")) {
			$replace = "<code>" . str_replace("\n", "<br>", htmlspecialchars(file_get_contents($file))) . "</code>";
		} elseif (str_starts_with($mime, "image/")) {
			$replace = "<img src='" . $share->uri() . "?action=view' alt='" . $share->value() . "' />";
		} elseif (str_starts_with($mime, "audio/")) {
			$replace = "<audio controls><source src='" . $share->uri() . "?action=view' type='" . $mime . "' alt='" . $share->value() . "'></audio>";
		} elseif (str_starts_with($mime, "video/")) {
			$replace = "<video controls><source src='" . $share->uri() . "?action=view' type='" . $mime . "' alt='" . $share->value() . "'></video>";
		} else {
			$replace = "<iframe src='" . $share->uri() . "?action=view' alt='" . $share->value() . "'></iframe>";
		}
		$output->replaceAll("share-preview", $replace);
		$output = self::replaceCommon($output, $share);
		$output->print();
		return true;
	}

	public static function password($share): bool
	{
		if (Request::session("webshare", $share->uri())) return false;
		$status = Config::TEXT_PASSWORD["default"];
		if (Request::post("submit")) {
			$inputPassword = Request::post("password") ?? "";
			if ($share->password($inputPassword)) {
				$_SESSION["webshare"][$share->uri()] = true;
				Request::setSession("webshare", $share->uri(), true);
				return false;
			}
			$status = Config::TEXT_PASSWORD["incorrect"];
		}
		$output = new Output($_SERVER["DOCUMENT_ROOT"] . Config::PATH_PASSWORD);
		$output->replaceAll("share-status", $status);
		$output = self::replaceCommon($output, $share);
		$output->print();
		return true;
	}

	public static function delete($share): bool
	{
		if (!Config::adminAccess()) return false;
		$status = "";
		$output = new Output($_SERVER["DOCUMENT_ROOT"] . Config::PATH_DELETE);
		if (Request::post("submit")) {
			if (Request::post("uri") == $share->uri()) {
				$delete = $share->delete();
				if ($delete) {
					$status = Config::TEXT_DELETE["success"];
					$output->replaceAll("share-form", "");
				} else {
					$status = Config::TEXT_DELETE["error"];
				}
			} else {
				$status = Config::TEXT_DELETE["error_input"];
			}
		}
		$output->replaceAll("share-status", $status);
		$output = self::replaceCommon($output, $share);
		$output->print();
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

	private static function replaceCommon(Output $output, Share $share = null): Output
	{
		$output->replaceAllSafe("share-installPath", Config::INSTALL_PATH);
		$output->replaceAllSafe("share-adminLink", Config::ADMIN_LINK);
		if (is_null($share)) return $output;

		$output->replaceAllSafe("share-uri", $share->uri());
		$output->replaceAllSafe("share-type", $share->type());
		$output->replaceAllSafe("share-value", $share->value());
		$output->replaceAllSafe("share-password", $share->password() ? Config::TEXT_OUTPUT["passwordIsSet"] : Config::TEXT_OUTPUT["passwordNotSet"]);
		$output->replaceAllSafe("share-expire", $share->expireDate() ?? Config::TEXT_OUTPUT["noExpireDate"]);
		$output->replaceAllSafe("share-create", $share->createDate() ?? "");
		$output->replaceAllSafe("share-url", Config::INSTALL_PATH . $share->uri());
		$output->replaceAllSafe("share-urlFull", Request::baseUrl() . $share->uri());
		return $output;
	}
}
