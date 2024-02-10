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
	private $connection;

	public function __construct()
	{
		$dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=utf8mb4";
		$options = [
			PDO::ATTR_EMULATE_PREPARES   => false,
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		];
		$this->connection = new PDO($dsn, Config::DB_USERNAME, Config::DB_PASSWORD, $options);
	}
	public function query(string $query, array $params = []): PDOStatement
	{
		$statement = $this->connection->prepare($query);
		$statement->execute($params);
		return $statement;
	}
}
