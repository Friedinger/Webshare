<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

by Friedinger (friedinger.org)

*/

namespace Webshare;

use PDO;
use PDOStatement;

final class Database
{
	private static PDO $connection;

	private static function connect()
	{
		$dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=utf8mb4";
		$options = [
			PDO::ATTR_EMULATE_PREPARES   => false,
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		];
		self::$connection = new PDO($dsn, Config::DB_USERNAME, Config::DB_PASSWORD, $options);
	}

	public static function query(string $query, array $params = []): PDOStatement
	{
		if (!isset(self::$connection)) {
			self::connect();
		}
		$statement = self::$connection->prepare($query);
		$statement->execute($params);
		return $statement;
	}
}
