<?php
/*
  بِسْــــــــــــــــــمِ اﷲِالرَّحْمَنِ اارَّحِيم

  Eûzubillâhimineşşeytânirracîym - Bismillâhirrahmânirrahîm

  Rahman ve Rahim olan "Allah" 'ın adıyla
*/

namespace PhpProje\Library\Pdo\Mysql;


use PDOException;
use Exception;
use CustomErrorException;

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
			$db = new \PhpProje\Library\Pdo\Mysql\Mysql($c->getName(), $c->getPort(), $c->getDatabase(), $c->getUser(), $c->getPassword());
            return $db;
        } catch (PDOException $e) {
			throw new Exception($e->getMessage());
        }
    }
}