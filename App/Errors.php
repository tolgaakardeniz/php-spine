<?php

/**
 * بِسْــــــــــــــــــمِ اﷲِالرَّحْمَنِ اارَّحِيم
 * 
 * Eûzubillâhimineşşeytânirracîym - Bismillâhirrahmânirrahîm
 * 
 * Rahman ve Rahim olan "Allah" 'ın adıyla
 * 
 */

/**
 * Hata raporlamayı aktif et
 */
error_reporting(E_ALL);

/**
 * Hataların kayıt edileceği array dizisinin adı
 */
App::$c->set("errors", array());


/**
 * Sistem loglarının logDir içerisine kayıt edilmesinde kullanılacak
 */
class Log
{
	/**
	 * Log yazmak için kullan
	 * 
	 * @param {string} $message
	 * 
	 */
	public static function write(string $message)
	{
		$date = new DateTime();
		$logFileName  = logDir . $date->format('Y-m-d') . "-" . md5($date->format('Y-m-d')) . ".txt";

		if (is_dir(logDir)) {
			try {
				$logContent = "Time : " . $date->format('H:i:s:u') . "\n" . $message . "\n\n";
				file_put_contents($logFileName, $logContent, FILE_APPEND);
				return true;
			} catch (Exception $e) {
				throw new Exception("Unable to write log file. (" . $logFileName . ") Error: " . $e->getMessage());
			}
		} else {
			try {
				if (@mkdir(logDir, 0755, true) === true) {
					return Log::write($message);
				} else {
					throw new Exception("Unable to create log director. (" . logDir . ")");
				}
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
		}
	}
}

/**
 * Hataları işle
 */
class CustomErrorException
{
	public function setErrorHandler($code, $message, $file, $line, $a)
	{
		if (error_reporting() === 0) {
			return false;
		}

		switch ($code) {
			case E_NOTICE:
			case E_USER_NOTICE:
				$error = "Not";
				break;
			case E_WARNING:
			case E_USER_WARNING:
				$error = "Uyarı";
				break;
			case E_ERROR:
			case E_USER_ERROR:
				$error = "Ölümcül Hata";
				break;
			default:
				$error = "Bilinmeyen Hata (" . $code . ")";
				break;
		}

		$err = array($error, $message, $file, $line);

		if (showErrors) {
			App::$c->set("errors", array_merge(App::$c->get("errors"), array($err)));
		}

		if ((logErrors) && (($code !== E_NOTICE) && ($code !== E_USER_NOTICE))) {
			Log::write(var_export(array($error, $message, $file, $line), true));
		}
	}

	public function setExceptionHandler($message)
	{
		/**
		 * 
		 * Hata temasını yükle
		 * Error html
		 * 
		 */
		App::$c->set("errTemp", App::$twig->load("error.html"));

		if (is_object($message)) {
			$code = $message->getCode();

			$custom = array();

			if (preg_match("/CustomError/", get_class($message))) {
				$custom = json_decode($message->getMessage(), true);
			}

			switch ($code) {
				case E_NOTICE:
				case E_USER_NOTICE:
					$error = "Note";
					break;
				case E_WARNING:
				case E_USER_WARNING:
					$error = "Warning";
					break;
				case E_ERROR:
				case E_USER_ERROR:
					$error = "Fatal Error";
					break;
				default:
					$error = "Unknown Error (" . $code . ")";
					break;
			}

			$err = array($error, (isset($custom["message"]) ? $custom["message"] : $message->getMessage()), $message->getFile(), $message->getLine());

			if (showErrors) {

				$custom["err"] = $err;
				$custom["siteName"] = siteName;

				echo App::$compress->compress(App::$c->get("errTemp")->render($custom));
			}

			if ((logErrors)) {
				Log::write(var_export($err, true));
			}
		} else {
			if (showErrors) {

				$e = array();

				$e["title"] = $message["title"];
				$e["errors"] = App::$c->get("errors");

				App::$c->set("errors", array_merge($e, App::$c->get("errors"), array(array(var_export($message, true)))));

				echo App::$compress->compress(App::$c->get("errTemp")->render(App::$c->get("errors")));
			}

			if (logErrors) {
				Log::write(var_export($message, true));
			}
		}
	}
}

/**
 * Sistem hatalarını yönlendir
 */
set_error_handler(array(new CustomErrorException(), 'setErrorHandler'),  E_ALL);
/**
 * Kullanıcı tanımlı hataları yönlendir
 */
set_exception_handler(array(new CustomErrorException(), 'setExceptionHandler'));
