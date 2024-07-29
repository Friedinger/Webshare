<?php

/*

FileRouter
A simple php router that allows to run code before accessing a file while keeping the file structure as the url structure.

by Friedinger (friedinger.org)

*/

namespace Webshare;

/**
 * Class Misc
 *
 * Contains miscellaneous utility functions.
 */
final class Misc
{
	/**
	 * Retrieves the MIME type of a file.
	 * Uses mime_content_type as default, but also provides a custom list of MIME types based on file extension.
	 *
	 * @param string $filePath The path to the file.
	 * @return string|false The MIME type of the file if it can be determined, false otherwise.
	 */
	public static function getMime(string $filePath): string|false
	{
		$mimeTypes = [ // List of mime types depending on file extension
			"php" => "text/html",
			"html" => "text/html",
			"css" => "text/css",
			"js" => "application/x-javascript",
			"ico" => "image/x-icon",
			"vbs" => "application/x-vbs",
		];
		$extension = pathinfo($filePath, PATHINFO_EXTENSION); // Get file extension
		return $mimeTypes[$extension] ?? mime_content_type($filePath); // Choose mime type depending on file extension, php mime function as fallback
	}

	/**
	 * Prepare URI by removing special characters, parameters, trailing slash, index.php and start slash.
	 *
	 * @param string $uri The URI to prepare.
	 * @return string The prepared URI.
	 */
	public static function prepareUri(string $uri): string
	{
		$uri = htmlspecialchars(strtolower(urldecode($uri))); // Remove special chars from request
		$uri = parse_url($uri, PHP_URL_PATH); // Remove parameters
		$uri = rtrim($uri, "/") . "/"; // Force trailing slash
		$uri = "/" . ltrim($uri, "/"); // Force start slash
		$uri = str_replace("/index.php/", "", $uri); // Remove index.php
		$uri = rtrim($uri, "/"); // Remove trailing slash
		$uri = ltrim($uri, "/"); // Remove start slash
		return htmlspecialchars($uri); // Return cleaned URI
	}

	/**
	 * Returns the base URL of the application.
	 *
	 * This function concatenates the protocol, HTTP host, and installation path
	 * to form the base URL of the application.
	 *
	 * @return string The base URL of the application.
	 */
	public static function baseUrl(): string
	{
		return Request::protocol() . "://" . $_SERVER["HTTP_HOST"] . Config::INSTALL_PATH;
	}
}
