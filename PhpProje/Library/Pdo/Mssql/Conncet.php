<?php
namespace PhpProje\Library\Pdo\Mssql;

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
            $db = new \PhpProje\Library\Pdo\Mssql\Mssql($c->getName(), $c->getPort(), $c->getDatabase(), $c->getUser(), $c->getPassword());
            return $db;
        } catch (PDOException $e) {
			throw new Exception($e->getMessage());
        }
    }
}