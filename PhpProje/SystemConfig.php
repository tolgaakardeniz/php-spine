<?php

/**
 * بِسْــــــــــــــــــمِ اﷲِالرَّحْمَنِ اارَّحِيم
 * 
 * Eûzubillâhimineşşeytânirracîym - Bismillâhirrahmânirrahîm
 * 
 * Rahman ve Rahim olan "Allah" 'ın adıyla
 * 
 */

namespace PhpProje;

use Exception;
use PDOException;

class SystemConfig
{
	protected $name;
	protected $port;
	protected $user;
	protected $password;
	protected $database;
	private $pdoType;
	private $pdo;
	private $ip;
	private $sessionName = "PhpProje";
	private $sessionId;
	private $isHttps;
	private $criticalDieTime = 2678400;

	private $array = array();

	public function __construct()
	{
		$this->isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$ip =  $_SERVER["HTTP_X_FORWARDED_FOR"];
		} else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
			$ip =  $_SERVER["HTTP_CLIENT_IP"];
		} else if (isset($_SERVER["REMOTE_ADDR"])) {
			$ip = $_SERVER["REMOTE_ADDR"];
		} else {
			$ip = "::1";
		}

		$this->setCookieParams();

		$this->setPdo((object)array("connectionStatus" => false));

		$this->setIp($ip);
	}



	public function setCookie($name, $value, $Lax = null, $expiresOrArray = null, $path = "/", $domain = null, $secure = false, $httpOnly = false, $sameSite = "None")
	{
		// method body

		if (is_null($domain)) {
			$domain = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : null;
		}

		/** Https kontrolü */
		if ($this->isHttps) {
			$secure = true;
			$httpOnly = false;

			if (!is_array($expiresOrArray)) {
				$expiresOrArray = ["expires" => time() + $this->criticalDieTime, "path" => "/", "domain" => $domain, "secure" => $secure, "httpOnly" => $httpOnly, "samesite" => (is_null($Lax) ? "Lax" : $sameSite)];
			}

			return setcookie($name, $value, $expiresOrArray);
		} else {
			if (!is_array($expiresOrArray)) {
				$expiresOrArray = ["expires" => time() + $this->criticalDieTime, "path" => "/", "domain" => $domain, "samesite" => "Lax"];
			}

			//var_dump($this->isHttps,$expiresOrArray);
			return setcookie($name, $value, $expiresOrArray);
		}
	}

	private function setCookieParams($Lax = null, $expiresOrArray = null, $path = "/", $domain = null, $secure = false, $httpOnly = false, $sameSite = "None")
	{

		// method body

		if (is_null($domain)) {
			$domain = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : null;
		}

		$criticalDieTime = $this->criticalDieTime;

		/** Https kontrolü */
		if ($this->isHttps) {
			$secure = true;
			$httpOnly = false;
		} else {
			$secure = false;
			$httpOnly = true;
		}

		if (PHP_VERSION_ID < 70300) {
			session_set_cookie_params($criticalDieTime, '/; samesite=' . $sameSite, $_SERVER['HTTP_HOST'], $secure, $httpOnly);
		} else {
			session_set_cookie_params([
				'lifetime' => $criticalDieTime,
				'path' => '/',
				'domain' => $_SERVER['HTTP_HOST'],
				'secure' => $secure,
				'httponly' => $httpOnly,
				'samesite' => $sameSite
			]);
		}
	}

	public function get($name = null)
	{
		// method body

		if (is_null($name)) {
			return $this->array;
		} else {
			if (isset($this->array[$name])) {
				return $this->array[$name];
			} else {
				return false;
			}
		}
	}

	public function set($name, $value)
	{
		// method body

		return $this->array[$name] = $value;
	}

	public function unset($name)
	{
		// method body

		if (isset($this->array[$name])) {
			unset($this->array[$name]);
			return true;
		} else {
			return false;
		}
	}

	public function getSessionId()
	{
		// method body

		return $this->sessionId;
	}

	public function setSessionId($sessionId)
	{
		// method body

		$this->sessionId = $sessionId;
	}

	public function getSessionName()
	{
		// method body

		return $this->sessionName;
	}

	public function setSessionName($sessionName)
	{
		// method body

		$this->sessionName = $sessionName;
	}

	public function getIsHttps()
	{
		// method body

		return $this->isHttps;
	}

	public function setIsHttps($isHttps)
	{
		// method body

		$this->isHttps = $isHttps;
	}

	public function getName()
	{
		// method body

		return $this->name;
	}

	public function setName($name)
	{
		// method body

		$this->name = $name;
	}

	public function getPort()
	{
		// method body

		return $this->port;
	}

	public function setPort($port)
	{
		// method body

		$this->port = $port;
	}

	public function getUser()
	{
		// method body

		return $this->user;
	}

	public function setUser($user)
	{
		// method body

		$this->user = $user;
	}

	public function getPassword()
	{
		// method body

		return $this->password;
	}

	public function setPassword($password)
	{
		// method body

		$this->password = $password;
	}

	public function getDatabase()
	{
		// method body

		return $this->database;
	}

	public function setDatabase($database)
	{
		// method body

		$this->database = $database;
	}

	public function getPdoType()
	{
		// method body

		return $this->pdoType;
	}

	public function setPdoType($pdoType)
	{
		// method body

		$this->pdoType = $pdoType;
	}

	public function getPdo()
	{
		// method body

		return $this->pdo;
	}

	public function setPdo($pdo)
	{
		// method body

		$this->pdo = $pdo;
	}

	public function getIp()
	{
		// method body

		return $this->ip;
	}

	public function setIp($ip)
	{
		// method body

		$this->ip = $ip;
	}

	public function getConncetion()
	{
		// method body

		try {
			return $this->getPdo()->connectionStatus;
		} catch (Exception $e) {
			return false;
		}
	}

	public function connectSqlServer()
	{
		// method body
		try {
			switch ($this->getPdoType()) {
				case "Mysql":
					$p = new \PhpProje\Library\Pdo\Mysql\Connect();
					$this->setPdo($p->connect($this));
					break;
				case "Mssql":
					$p = new \PhpProje\Library\Pdo\Mssql\Connect();
					$this->setPdo($p->connect($this));
					break;
				default:
					$p = new \PhpProje\Library\Pdo\Mysql\Connect();
					$this->setPdo($p->connect($this));
					break;
			}

			return $this->getPdo();
		} catch (PDOException $e) {
			return $e;
		}
	}
}