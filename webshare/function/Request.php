<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

*/

namespace Friedinger\Webshare;

final class Request
{
	private string $uri;
	private array $get;
	private array $post;
	private array $file;
	private array $session;
	public function __construct(string $requestUri)
	{
		$this->uri = $this->prepareRequest($requestUri);
		$this->get = $_GET;
		$this->post = $_POST;
		$this->file = $_FILES;
		$this->session = $_SESSION;
	}
	public function uri(): string
	{
		return htmlspecialchars($this->uri);
	}
	public function get(string $key): string|null
	{
		if (empty($this->get[$key])) return null;
		return htmlspecialchars($this->get[$key]);
	}
	public function post(string $key): string|null
	{
		if (empty($this->post[$key])) return null;
		return htmlspecialchars($this->post[$key]);
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
		$request = explode("?", $request)[0]; // Remove parameters
		$request = rtrim($request, "/"); // Remove trailing slash
		return $request;
	}
}
