<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/../webshare/webshareConfig.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/../webshare/function/Webshare.php");

if (session_id() == "") {
	session_set_cookie_params([
		"secure" => true,
		"httponly" => true,
		"samesite" => "Strict",
	]);
	session_name("Webshare");
	session_start();
}

$webshare = new Webshare\Webshare();
