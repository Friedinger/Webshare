<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

*/

namespace Webshare;

final class Request
{
	private string $uri;
	private array $file;
	private array $session;
	public function __construct(string $requestUri)
	{
		$this->uri = $this->prepareRequest($requestUri);
		$this->file = $_FILES;
		$this->session = $_SESSION;
	}
	public function uri(): string
	{
		return htmlspecialchars($this->uri);
	}
	public function get(string $key): string|null
	{
		$get = filter_input(INPUT_GET, $key);
		if (empty($get)) return null;
		return htmlspecialchars($get);
	}
	public function post(string $key): string|null
	{
		$post = filter_input(INPUT_POST, $key);
		if (empty($post)) return null;
		return htmlspecialchars($post);
	}
	public function file(string ...$keys): mixed
	{
		$file = $this->file;
		foreach ($keys as $key) {
			if (!isset($file[$key])) return null;
			$file = $file[$key];
		}
		return $file;
	}
	public function session(string ...$keys): mixed
	{
		$session = $this->session;
		foreach ($keys as $key) {
			if (!isset($session[$key])) return null;
			$session = $session[$key];
		}
		return $session;
	}
	public function setSession(string $key, array $value): bool
	{
		$_SESSION[$key] = $value;
		$this->session[$key] = $value;
		return $_SESSION[$key] == $value;
	}
	private function prepareRequest(string $requestUri): string
	{
		$request = htmlspecialchars(strtolower(urldecode($requestUri))); // Remove special chars from request
		$request = str_replace(Config::INSTALL_PATH, "", $request); // Remove install path from request
		$request = parse_url($request)["path"]; // Remove parameters
		$request = rtrim($request, "/") . "/"; // Force trailing slash
		$request = str_replace("/index.php/", "", $request); // Remove index.php
		$request = rtrim($request, "/"); // Remove trailing slash
		$request = ltrim($request, "/"); // Remove start slash
		return $request;
	}
}
