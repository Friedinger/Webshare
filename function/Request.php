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
	private array $get;
	private array $post;
	private array $file;
	private array $session;

	public function __construct()
	{
		$this->uri = $this->prepareUri($_SERVER["REQUEST_URI"]);
		$this->get = $_GET;
		$this->post = $_POST;
		$this->file =  $_FILES;
		$this->session = $_SESSION;
	}

	public function uri(): string
	{
		return htmlspecialchars($this->uri);
	}

	public function get(string $key): string|null
	{
		$get = $this->get[$key] ?? null;
		if (empty($get)) return null;
		return htmlspecialchars($get);
	}

	public function post(string $key): string|null
	{
		$post = $this->post[$key] ?? null;
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

	public function setSession(string $key, mixed $value): bool
	{
		$_SESSION[$key] = $value;
		$this->session[$key] = $value;
		return $_SESSION[$key] == $value;
	}

	private function prepareUri(string $requestUri): string
	{
		$uri = htmlspecialchars(strtolower(urldecode($requestUri))); // Remove special chars from request
		$uri = ltrim($uri, Config::INSTALL_PATH); // Remove install path from request
		$uri = parse_url($uri, PHP_URL_PATH); // Remove parameters
		$uri = rtrim($uri, "/") . "/"; // Force trailing slash
		$uri = str_replace("/index.php/", "", $uri); // Remove index.php
		$uri = rtrim($uri, "/"); // Remove trailing slash
		$uri = ltrim($uri, "/"); // Remove start slash
		return $uri;
	}
}
