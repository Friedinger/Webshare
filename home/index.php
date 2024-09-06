<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

https://github.com/Friedinger/Webshare

by Friedinger (friedinger.org)

Version: 3.1.0

*/


require_once $_SERVER["DOCUMENT_ROOT"] . "/../config/webshareConfig.php"; // Include webshare configuration
require_once $_SERVER["DOCUMENT_ROOT"] . "/../function/Webshare.php"; // Include main webshare class

// Start session if not already started, can be removed if session is started elsewhere
if (session_id() == "") {
	session_set_cookie_params([
		"secure" => true,
		"httponly" => true,
		"samesite" => "Strict",
	]);
	session_name("Webshare");
	session_start();
}

$webshare = new Webshare\Webshare(); // Start webshare
