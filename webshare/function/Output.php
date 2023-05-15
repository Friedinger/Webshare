<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

*/

namespace Friedinger\Webshare;

final class Output
{
	public static string|null $status = null;
	public static string|null $uri = null;
	public static string|null $value = null;
	public static string|null $expireDate = null;
	public static string|null $createDate = null;
	public static string|null $sharePreview = null; // Only available in view page
	public static string|null $shareList = null;
	public static function url($uri = null): string|null
	{
		$uri = $uri ?? self::$uri;
		if ($uri == null) return null;
		return $_SERVER["HTTP_HOST"] . Config::INSTALL_PATH . $uri;
	}
	public static function link(string $uri = null, string|null $text = null, bool $longLink = false): string
	{
		$uri = htmlspecialchars($uri ?? self::$uri);
		$shareLink = self::url($uri);
		if ($longLink) {
			$text = htmlspecialchars($text ?? $shareLink);
			return "
				<a href='//" . $shareLink . "'>https://" . $text . "</a>
				<a href='javascript:void(0);' onclick='navigator.clipboard.writeText(`https://" . $shareLink . "`);'>
					<span class='copy-icon'></span>
				</a>
			";
		} else {
			$text = htmlspecialchars($text ?? $uri);
			return "<a href='//" . $shareLink . "'>" . $text . "</a> ";
		}
	}
}
