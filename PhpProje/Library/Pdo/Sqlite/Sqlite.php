<?php
/*
 * PHP-PDO-MySQL-Class
 * https://github.com/lincanbin/PHP-PDO-MySQL-Class
 *
 * Copyright 2015 Canbin Lin (lincanbin@hotmail.com)
 * http://www.94cb.com/
 *
 * Licensed under the Apache License, Version 2.0:
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * A PHP MySQL PDO class similar to the the Python MySQLdb. 
 */

namespace PhpProje\Library\Pdo\Sqlite;

require(__DIR__ . '/PDO.Log.class.php');
require(__DIR__ . '/PDO.Iterator.class.php');

use PDO;
use PDOLog;
use PDOException;
use PDOIterator;


/** Class DB
 * @property PDO pdo PDO object
 * @property PDOStatement sQuery PDOStatement
 * @property PDOLog PDOLog logObject
 */
class Sqlite
{
    private $Host;
    private $DBPort;
    private $DBName;
    private $DBUser;
    private $DBPassword;
    private $DBPath;
    private $pdo;
    private $sQuery;
    public $connectionStatus = false;
    private $logObject;
    private $parameters;
    public $rowCount   = 0;
    public $columnCount   = 0;
    public $querycount = 0;

    private $retryAttempt = 0; // 失败重试次数
    const AUTO_RECONNECT = true;
    const RETRY_ATTEMPTS = 3; // 最大失败重试次数

    /**
     * DB constructor.
     * @param $Host
     * @param $DBPort
     * @param $DBName
     * @param $DBUser
     * @param $DBPassword
     */
    public function __construct($Host, $DBName, $DBPath)
    {
        $this->logObject  = new PDOLog();
        $this->Host       = $Host;
        $this->DBName     = $DBName;
        $this->DBPath     = $DBPath;
        $this->parameters = array();
        //$this->Connect();
    }

    /**
     * Adaptamos la funcion de conectado a SQLServer
     */
    public function Connect()
    {
        try {

            $dsn = 'sqlite:' . $this->DBPath . $this->DBName;

            $this->pdo = new PDO($dsn);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            # We can now log any exceptions on Fatal error.
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            # Disable emulation of prepared statements, use REAL prepared statements instead.
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->connectionStatus = true;
        } catch (PDOException $e) {
            $this->ExceptionLog($e, '', 'Connect');
        }
    }

    private function SetFailureFlag()
    {
        $this->pdo = null;
        $this->connectionStatus = false;
    }

    /**
     * close pdo connection
     */
    public function closeConnection()
    {
        $this->pdo = null;
    }

    private function Init($query, $parameters = null, $driverOptions = array())
    {
        if (!$this->connectionStatus) {
            $this->Connect();
        }

        try {
            $this->parameters = $parameters;
            $this->sQuery     = $this->pdo->prepare($this->BuildParams($query, $this->parameters), $driverOptions);

            if (!empty($this->parameters)) {
                if (array_key_exists(0, $parameters)) {
                    $parametersType = true;
                    array_unshift($this->parameters, "");
                    unset($this->parameters[0]);
                } else {
                    $parametersType = false;
                }
                foreach ($this->parameters as $column => $value) {
                    $this->sQuery->bindParam($parametersType ? intval($column) : ":" . $column, $this->parameters[$column]); //It would be query after loop end(before 'sQuery->execute()').It is wrong to use $value.
                }
            }

            if (!isset($driverOptions[PDO::ATTR_CURSOR])) {
                $this->sQuery->execute();
            }
            $this->querycount++;
        } catch (PDOException $e) {
            $this->ExceptionLog($e, $this->BuildParams($query), 'Init', array('query' => $query, 'parameters' => $parameters));
        }

        $this->parameters = array();
    }

    private function BuildParams($query, $params = null)
    {
        if (!empty($params)) {
            $array_parameter_found = false;
            foreach ($params as $parameter_key => $parameter) {
                if (is_array($parameter)) {
                    $array_parameter_found = true;
                    $in = "";
                    foreach ($parameter as $key => $value) {
                        $name_placeholder = $parameter_key . "_" . $key;
                        // concatenates params as named placeholders
                        $in .= ":" . $name_placeholder . ", ";
                        // adds each single parameter to $params
                        $params[$name_placeholder] = $value;
                    }
                    $in = rtrim($in, ", ");
                    $query = preg_replace("/:" . $parameter_key . "/", $in, $query);
                    // removes array form $params
                    unset($params[$parameter_key]);
                }
            }

            // updates $this->params if $params and $query have changed
            if ($array_parameter_found) $this->parameters = $params;
        }
        return $query;
    }

    /**
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * @return bool
     */
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    /**
     * @return bool
     */
    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    /**
     * execute a sql query, returns an result array in the select operation, and returns the number of rows affected in other operations
     * @param string $query
     * @param null $params
     * @param int $fetchMode
     * @return array|int|null
     */
    public function query($query, $params = null, $fetchMode = PDO::FETCH_ASSOC)
    {
        $query        = trim($query);
        $rawStatement = explode(" ", $query);
        $this->Init($query, $params);
        $statement = strtolower($rawStatement[0]);
        if ($statement === 'select' || $statement === 'show') {
            return $this->sQuery->fetchAll($fetchMode);
        } elseif (
            $statement === 'insert' || $statement === 'update' || $statement === 'delete'
            || $statement === 'exec'
        ) {
            return $this->sQuery->rowCount();
        } else {
            return NULL;
        }
    }

    /**
     * execute a sql query, returns an iterator in the select operation, and returns the number of rows affected in other operations
     * @param string $query
     * @param null $params
     * @param int $fetchMode
     * @return int|null|PDOIterator
     */
    public function iterator($query, $params = null, $fetchMode = PDO::FETCH_ASSOC)
    {
        $query        = trim($query);
        $rawStatement = explode(" ", $query);
        $this->Init($query, $params, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $statement = strtolower($rawStatement[0]);
        if ($statement === 'select' || $statement === 'show') {
            return new PDOIterator($this->sQuery, $fetchMode);
        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
            return $this->sQuery->rowCount();
        } else {
            return NULL;
        }
    }

    /**
     * @param $tableName
     * @param null $params
     * @return bool|string
     */
    public function insert($tableName, $params = null)
    {
        $keys = array_keys($params);
        $rowCount = $this->query(
            'INSERT INTO `' . $tableName . '` (`' . implode('`,`', $keys) . '`) 
                VALUES (:' . implode(',:', $keys) . ')',
            $params
        );
        if ($rowCount === 0) {
            return false;
        }
        return $this->lastInsertId();
    }

    /**
     * @return string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * @param $query
     * @param null $params
     * @return array
     */
    public function column($query, $params = null)
    {
        $this->Init($query, $params);
        $resultColumn = $this->sQuery->fetchAll(PDO::FETCH_COLUMN);
        $this->rowCount = $this->sQuery->rowCount();
        $this->columnCount = $this->sQuery->columnCount();
        $this->sQuery->closeCursor();
        return $resultColumn;
    }

    /**
     * @param $query
     * @param null $params
     * @param int $fetchmode
     * @return mixed
     */
    public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $this->Init($query, $params);
        $resultRow = $this->sQuery->fetch($fetchmode);
        $this->rowCount = $this->sQuery->rowCount();
        $this->columnCount = $this->sQuery->columnCount();
        $this->sQuery->closeCursor();
        return $resultRow;
    }

    /**
     * @param $query
     * @param null $params
     * @return mixed
     */
    public function single($query, $params = null)
    {
        $this->Init($query, $params);
        return $this->sQuery->fetchColumn();
    }

    /**
     * @param PDOException $e
     * @param string $sql
     * @param string $method
     * @param array $parameters
     */
    private function ExceptionLog(PDOException $e, $sql = "", $method = '', $parameters = array())
    {
        return;
        $message = $e->getMessage();
        $exception = 'Unhandled Exception. <br />';
        $exception .= $message;
        $exception .= "<br /> You can find the error back in the log.";

        if (!empty($sql)) {
            $message .= "\r\nRaw SQL : " . $sql;
        }
        $this->logObject->write($message, $this->DBName . md5($this->DBPassword));
        if (
            self::AUTO_RECONNECT
            && $this->retryAttempt < self::RETRY_ATTEMPTS
            && stripos($message, 'server has gone away') !== false
            && !empty($method)
            && !$this->inTransaction()
        ) {
            $this->SetFailureFlag();
            $this->retryAttempt++;
            $this->logObject->write('Retry ' . $this->retryAttempt . ' times', $this->DBName . md5($this->DBPassword));
            call_user_func_array(array($this, $method), $parameters);
        } else {
            if ($this->pdo === null) {
                throw new PDOException($message);
            } else {
                throw $e;
            }
        }
    }
}
