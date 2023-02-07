<?php
if (!isset($_SESSION)) session_start();
class main
{
	public static function head($pagetitle)
	{
		$statichead = "
			<link rel='shortcut icon' type='image/x-icon' href='/data/favicon.ico' />
			<meta name='viewport' content='width=device-width, initial-scale=1.0'>
			<link rel='stylesheet' type='text/css' href='/data/friedinger.css'>
			<script type='text/javascript' src='/data/friedinger.js'></script>
		";
		if ($pagetitle) {
			$pagetitle = " | " . $pagetitle;
		}
		$title = "<title>Friedinger" . $pagetitle . "</title>";
		print($title . $statichead);
	}
	public static function header()
	{
		include($_SERVER["DOCUMENT_ROOT"] . "/../pages/data/header.php");
	}
	public static function footer()
	{
		include($_SERVER["DOCUMENT_ROOT"] . "/../pages/data/footer.php");
	}
	public static function pathConfig()
	{
		return $_SERVER["DOCUMENT_ROOT"] . "/../config/config.json";
	}
	public static function pathPages()
	{
		return $_SERVER["DOCUMENT_ROOT"] . "/../pages/";
	}
}
