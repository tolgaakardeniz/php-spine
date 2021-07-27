<?php

namespace PhpProje\Library\Session;

use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;

use Exception;
use PhpProje\SystemConfig;


class Mysql implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
	private $key;
	/**
	 * Keylerin kontrolü için istediğini yaz
	 * Sezon tahminini engellemek içindir
	 */
	private $secretKey = "1453";
	private $cookieName;
	private $sessionId;
	private $config;
	private $cryptoMedhot = "AES-256-CBC";
	private $makeCrypto = false;
	private $makeCompress = false;
	private $sessionDataHash;
	private $criticalDieTime = 3600;

	public function __construct(SystemConfig $c)
	{
		try {
			// method body

			$c->set("criticalDieTime", $this->criticalDieTime);

			$this->db = $c->getPdo();
			$this->config = $c;
			$this->cookieName = $c->getSessionName() . "Key";
			$this->key = $this->getKey($this->cookieName);

			/** default php variables is changed */
			ini_set("session.name", $c->getSessionName());
			ini_set("session.save_path", sessionDir);
			ini_set("session.gc_probability", 1);
			ini_set('session.gc_maxlifetime', $this->criticalDieTime);

			/**
			 * Eğer SQL bağlantısı yoksa dosya olarak tut
			 */
			if ($c->getConncetion() !== false) {
				/**
				 * Don't use file cookies
				 */
				ini_set("session.use_cookies", "off");

				// Set the handler to overide SESSION
				session_set_save_handler($this, true);

				// for old version php
				register_shutdown_function(array($this, "close"));

				// Set the shutdown function
				//register_shutdown_function("session_write_close");

				$this->sessionStart();
			} else {
				$f = new \PhpProje\Library\Session\File($c);
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}


	public function close()
	{
		// return value should be true for success or false for failure
		// ...

		return true;
	}

	public function destroy($sessionId)
	{
		// return value should be true for success or false for failure
		// ...

		try {
			$this->db->query("DELETE FROM `Sezonlar` WHERE Benzersiz='" . $this->db->escape($sessionId) . "'; DELETE FROM `Sezonlar` WHERE (`GuncellemeTarihi`<'" . date("Y-m-d H:i:s", time() - $this->criticalDieTime) . "') OR (`GuncellemeTarihi` IS NULL AND `OlusturmaTarihi`<(NOW() - INTERVAL 60 MINUTE)) OR (`OlusturanRef` IS NULL AND `OlusturmaTarihi`<(NOW() - INTERVAL 24 HOUR));");
			return true;
		} catch (Exception $e) {
			return true;
		}
	}

	public function gc($maximumLifetime)
	{
		// return value should be true for success or false for failure
		// ...

		return true;
	}

	public function open($sessionSavePath, $sessionName)
	{
		// return value should be true for success or false for failure
		// ...



		$sessionName = $this->config->getSessionName();

		return true;
	}

	public function read($sessionId)
	{
		// return value should be the session data or an empty string
		// ...

		$x = $this->db->query("SELECT `Veri` FROM `Sezonlar` WHERE `Benzersiz`=:sessionId LIMIT 1;", array("sessionId" => $sessionId));

		if (count($x) > 0) {
			$x = $x[0]["Veri"];
			$this->sessionDataHash = md5($x);

			/** gz aktif ise */
			if ($this->makeCompress) {
				$x = @gzuncompress(hex2bin($x));

				if ($x === false) {
					$x = "";
				}
			} else {
				/** Kripto yap aktif ise */
				if ($this->makeCrypto) {
					$x = $this->decrypt(hex2bin($x), $this->key);
				}
			}

			return $x;
		} else {
			return "";
		}
	}

	public function write($sessionId, $sessionData)
	{
		// return value should be true for success or false for failure
		// ...

		try {
			/** gz aktif ise */
			if ($this->makeCompress) {
				$sessionData = bin2hex(gzcompress($sessionData));
			} else {
				/** Kripto yap aktif ise */
				if ($this->makeCrypto) {
					$sessionData = bin2hex($this->encrypt($sessionData, $this->key));
				}
			}

			if ($this->sessionDataHash !== md5($sessionData)) {

				$y = isset($_SESSION["Ref"]) ? $_SESSION["Ref"] : NULL;
				$y = $this->db->escape(is_null($y) ? "NULL" : $y);
				$x = $this->db->query("INSERT INTO `Sezonlar` (`Benzersiz`, `Veri`, `Ip`, `OlusturanRef`) VALUES ('" . $this->db->escape($sessionId) . "', '" . $this->db->escape($sessionData) . "', '" . $this->db->escape($this->config->getIp()) . "', " . $y . ") ON DUPLICATE KEY UPDATE `Veri`='" . $this->db->escape($sessionData) . "', `GuncellemeTarihi`=NOW(), `Guncelleme`=(CASE WHEN `Guncelleme` IS NULL THEN 1 ELSE `Guncelleme`+1 END), `OlusturanRef`=" . $y . ", `Ip`='" . $this->db->escape($this->config->getIp()) . "';");

				if ($x > 0) {
					return true;
				} else {
					return true;
				}
			} else {
				return true;
			}
		} catch (Exception $e) {
			return true;
		}
	}

	public function create_sid()
	{
		// available since PHP 5.5.1
		// invoked internally when a new session id is needed
		// no parameter is needed and return value should be the new session id created
		// ...

		return $this->sessionId;
	}

	public function validateId($sessionId)
	{
		// implements SessionUpdateTimestampHandlerInterface::validateId()
		// available since PHP 7.0
		// return value should be true if the session id is valid otherwise false
		// if false is returned a new session id will be generated by php internally
		// ...
		if ($this->sessionId === $sessionId) {
			return true;
		} else {
			return false;
		}
	}

	public function updateTimestamp($sessionId, $sessionData)
	{
		// implements SessionUpdateTimestampHandlerInterface::validateId()
		// available since PHP 7.0
		// return value should be true for success or false for failure
		// ...

		try {
			if (is_null($sessionId) === false) {
				$x = $this->db->query("UPDATE `Sezonlar` SET `GuncellemeTarihi`=NOW() WHERE `Benzersiz`='" . $this->db->escape($sessionId) . "'");

				if ($x > 0) {
					return true;
				} else {

					return false;
				}
			} else {
				return false;
			}
		} catch (Exception $e) {
			return false;
		}
	}

	public function session_write_close($sessionId, $sessionData)
	{
		return true;
	}

	public function sessionStart()
	{
		try {
			if (session_status() !== PHP_SESSION_ACTIVE) {
				if (@session_start() === false) {
					$this->config->setCookie($this->cookieName, "");
					$this->config->setCookie($this->config->getSessionName(), "");
				}
			}
		} catch (Exception $e) {
			throw new Exception("Sezon başlatma hatası. Hata: " . $e->getMessage());
		}
	}

	public function sessionClose()
	{
		try {
			if (session_status() === PHP_SESSION_ACTIVE) {
				@session_destroy();
			}
		} catch (Exception $e) {
			throw new Exception("Sezon yok etme hatası. Hata: " . $e->getMessage());
		}
	}


	/* 	public function session_write_close($sessionId, $sessionData)
	{
		return true;
	} */


	/**
	 * Encrypt and authenticate
	 *
	 * @param string $sessionData
	 * @param string $key
	 * @return string
	 */
	protected function encrypt($sessionData, $key)
	{
		$iv = random_bytes(16); // AES block size in CBC mode
		// Encryption
		$ciphertext = openssl_encrypt(
			$sessionData,
			$this->cryptoMedhot,
			mb_substr($key, 0, 32, '8bit'),
			OPENSSL_RAW_DATA,
			$iv
		);
		// Authentication
		$hmac = hash_hmac(
			'SHA256',
			$iv . $ciphertext,
			mb_substr($key, 32, null, '8bit'),
			true
		);
		return $hmac . $iv . $ciphertext;
	}

	/**
	 * Authenticate and decrypt
	 *
	 * @param string $sessionData
	 * @param string $key
	 * @return string
	 */
	protected function decrypt($sessionData, $key)
	{
		$hmac       = mb_substr($sessionData, 0, 32, '8bit');
		$iv         = mb_substr($sessionData, 32, 16, '8bit');
		$ciphertext = mb_substr($sessionData, 48, null, '8bit');

		// Authentication
		$hmacNew = hash_hmac(
			'SHA256',
			$iv . $ciphertext,
			mb_substr($key, 32, null, '8bit'),
			true
		);

		if (!hash_equals($hmac, $hmacNew)) {
			return false;
			//throw new Exception('Authentication failed');
		}

		// Decrypt
		return openssl_decrypt(
			$ciphertext,
			$this->cryptoMedhot,
			mb_substr($key, 0, 32, '8bit'),
			OPENSSL_RAW_DATA,
			$iv
		);
	}

	/**
	 * Get the encryption and authentication keys from cookie
	 *
	 * @param string $name
	 * @return string
	 */
	protected function getKey($name)
	{
		/**
		 * Kullanıcıdaki sezon referansını al
		 */
		if (isset($_COOKIE[$this->config->getSessionName()])) {
			$this->sessionId = $_COOKIE[$this->config->getSessionName()];
			$this->config->setSessionId($this->sessionId);
		}

		/**
		 * Kullanıcıdaki sezon referansını 32 karakter değilse tekrar oluştur
		 */
		if (strlen($this->sessionId) !== 32) {
			$this->sessionId = $this->sessionId();
			$this->config->setSessionId($this->sessionId);
			$this->config->setCookie($this->config->getSessionName(), $this->sessionId, "None");
		}

		/**
		 * Güvenlik anahtarı ile karşılaştırmak için anahtar oluştur
		 */
		$key = md5($this->sessionId . $this->secretKey . $this->sessionId);

		if (!isset($_COOKIE[$name])) {
			/**
			 * Güvenlik anahtarı ile oluşturulmuş güvenlik cookie sini gönder
			 */
			$this->config->setCookie($name, $key, "None");
			$_COOKIE[$name] = $key;
		} else {
			/**
			 * Kullanıcıdaki cookie lerin uyumu karşılaştır
			 */
			if ($_COOKIE[$name] === $key) {
				$key = $_COOKIE[$name];
			} else {
				/**
				 * Uyumsuz ise tekrar oluştur
				 */
				$this->sessionId = $this->sessionId();
				$this->config->setSessionId($this->sessionId);
				$this->config->setCookie($this->config->getSessionName(), $this->sessionId, "None");

				$key = md5($this->sessionId . $this->secretKey . $this->sessionId);
				$this->config->setCookie($name, $key, "None");
				$_COOKIE[$name] = $key;
			}
		}

		return $key;
	}

	/**
	 * Md5 rastgele 32 karakter 
	 * 
	 * @return string
	 * 
	 */
	private function sessionId()
	{
		return md5(sha1(sha1(microtime() . rand(1111111, 9999999))));
	}
}
