<?php

namespace PhpProje\Library\Session;

use SessionHandler;
/* use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface; */

use DateTime;

use Exception;
use PhpProje\SystemConfig;

class File extends SessionHandler
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
	private $makeCompress = true;
	private $sessionDataHash;
	private $criticalDieTime = 3600;

	public function __construct(SystemConfig $c)
	{
		try {

			// method body

			if (!extension_loaded('openssl')) {
				throw new \RuntimeException(sprintf(
					"You need the OpenSSL extension to use %s",
					__CLASS__
				));
			}

			if (!extension_loaded('mbstring')) {
				throw new \RuntimeException(sprintf(
					"You need the Multibytes extension to use %s",
					__CLASS__
				));
			}

			/** global config class in set session */
			$c->setSessionId($this->sessionId);

			$c->set("criticalDieTime", $this->criticalDieTime);

			$this->config = $c;

			if (!is_dir(sessionDir)) {
				mkdir(sessionDir, 0775);
			}

			$this->cookieName = $c->getSessionName() . "Key";
			$this->key = $this->getKey($this->cookieName);


			/** default php variables is changed */
			ini_set("session.name", $c->getSessionName());
			ini_set("session.save_path", sessionDir);
			ini_set("session.gc_probability", 1);
			/* ini_set('session.use_only_cookies', 1); */
			ini_set('session.use_trans_sid', 0);
			ini_set('session.save_handler', 'files');
			ini_set('session.gc_maxlifetime', $this->criticalDieTime);

			/**
			 * Don't use file cookies
			 */
			ini_set("session.use_cookies", "on");

			// Set the handler to overide SESSION
			session_set_save_handler($this, true);

			// for old version php
			//register_shutdown_function(array($this, "close"));

			// Set the shutdown function
			//register_shutdown_function("session_write_close");

			/** Define and initialise the Session Handler */
			$this->sessionStart();
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public function destroy($sessionId)
	{
		$file = sessionDir . "sess_" . $sessionId;

		if (is_file($file)) {
			@unlink($file);
		}

		return true;
	}


	public function open($sessionSavePath, $sessionName)
	{
		// return value should be true for success or false for failure
		// ...

		return parent::open($sessionSavePath, $sessionName);
	}

	public function read($sessionId)
	{
		// return value should be the session data or an empty string

		$sessionData = parent::read($sessionId);

		try {
			if (empty($sessionData)) {
				$sessionData = "";
			} else {
				$this->sessionDataHash = md5($sessionData);

				/** gz aktif ise */
				if ($this->makeCompress) {
					$sessionData = @gzuncompress(hex2bin($sessionData));

					if ($sessionData === false) {
						$sessionData = "";
					}
				} else {
					/** Kripto yap aktif ise */
					if ($this->makeCrypto) {
						$sessionData = $this->decrypt($sessionData, $this->key);
					}
				}
			}
		} catch (Exception $e) {
			$sessionData = "";
		}

		return $sessionData;
	}

	public function write($sessionId, $sessionData)
	{
		// return value should be true for success or false for failure
		try {
			/** gz aktif ise */
			if ($this->makeCompress) {
				$sessionData = bin2hex(gzcompress($sessionData));
			} else {
				/** Kripto yap aktif ise */
				if ($this->makeCrypto) {
					$sessionData = $this->encrypt($sessionData, $this->key);
				}
			}

			if ($this->sessionDataHash !== md5($sessionData)) {
				return parent::write($sessionId, $sessionData);
			} else {
				return true;
			}
		} catch (Exception $e) {
			return false;
		}
	}


	/* 	public function session_write_close($sessionId, $sessionData)
	{
		return true;
	} */

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

	public function __destruct()
	{
		$date = new DateTime();
		$clear = false;

		switch ($date->format("is")) {
			case "1000":
			case "2000":
			case "3000":
			case "4000":
			case "5000":
					$clear = true;
				break;
			default:
				if ((rand() % 1000) < 1) {
					$clear = true;
				}

				break;
		}

		if ($clear)
		{
			$expire = time() - ini_get('session.gc_maxlifetime');

			$files = glob(sessionDir . "sess_*");

			foreach ($files as $file) {
				if (filemtime($file) < $expire) {
					@unlink($file);
				}
			}
		}
	}
}
