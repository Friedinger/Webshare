<?php

/*

Webshare
A simple, lightweight, self hosted webservice to easily share files and links via an short custom URL.

https://github.com/Friedinger/Webshare

by Friedinger (friedinger.org)

*/

namespace Webshare;

use PDO;
use PDOStatement;

/**
 * Class Database
 *
 * Contains functions to connect to and query the database.
 */
class Database
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

	/**
	 * Executes a database query with optional parameters.
	 * Connects to the database if no connection exists.
	 *
	 * @param string $query The SQL query to execute.
	 * @param array $params An array of parameters to bind to the query.
	 * @return PDOStatement The executed PDOStatement object.
	 */
	public static function query(string $query, array $params = []): PDOStatement
	{
		if (!isset(self::$connection)) {
			self::connect(); // Connect to database if no connection exists
		}
		$statement = self::$connection->prepare($query); // Prepare query
		$statement->execute($params); // Execute query with parameters
		return $statement; // Return PDOStatement object
	}
}
