<?php
namespace PhpProje\Library\Pdo\Sqlite;

use PDOException;
use Exception;

class Connect
{
    public function __construct()
    {
        // method body
    }

    public function connect($c)
    {
        // method body
        try {
            $db = new \PhpProje\Library\Pdo\Sqlite\Sqlite($c->getName(), $c->getPort(), $c->getDatabase(), $c->getUser(), $c->getPassword());
            return $db;
        } catch (PDOException $e) {
			throw new Exception($e->getMessage());
        }
    }
}