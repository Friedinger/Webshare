<?php

namespace Webshare;

class WebshareException extends \Exception
{
	public function __construct(string $code, string $message = "")
	{
		$message = $message ?: Config::TEXT_ADMIN[$code] ?? Config::TEXT_ADMIN["default"];
		parent::__construct($message);
	}
}

class FileException extends \Exception
{
}

class DatabaseException extends \Exception
{
}

class ShareException extends \Exception
{
	public function __construct(string $error)
	{
		switch ($error) {
			case "share_get_null":
				$message = "Share not found";
				break;
			case "share_get_expired":
				$message = "Share not found";
				break;
			case "share_store_file":
				$message = "Error while storing file";
				break;
			case "share_store_database":
				$message = "Error while storing share to database";
				break;
			case "share_delete_file":
				$message = "Error while deleting file";
				break;
			case "share_delete_database":
				$message = "Error while deleting share from database";
				break;
			case "share_redirect_file":
				$message = "Error while deleting share from database";
				break;
			default:
				$message = Config::TEXT_ADMIN[$error] ?? Config::TEXT_ADMIN["default"];
		}
		parent::__construct($message);
	}
}

class PermissionException extends \Exception
{
}
