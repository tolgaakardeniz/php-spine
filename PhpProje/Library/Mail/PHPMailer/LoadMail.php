<?php

namespace PhpProje\Library\Mail\PHPMailer;

use SMTP;
use Exception;

class LoadMail
{
	private $host = "smtp.gmail.com";
	private $port = 587;
	private $user = "zamanbuakar@gmail.com";
	private $password = "ufcfopsndoqggjso";
	private $smtpDebug = false;

	private static $output = array();

	public function __construct()
	{
		// method body

	}

	public static function getOutput()
	{
		// method body

		return LoadMail::$output;
	}

	public static function setOutput($output)
	{
		// method body

		LoadMail::$output[] = $output;
	}

	public function getHost()
	{
		// method body

		return $this->host;
	}

	public function setHost($host)
	{
		// method body

		$this->host = $host;
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

	public function getSmtpDebug()
	{
		// method body

		return $this->smtpDebug;
	}

	public function setSmtpDebug($smtpDebug)
	{
		// method body

		$this->smtpDebug = $smtpDebug;
	}

	public function sendMail(
		$mail = null,
		$name = null,
		$subject = null,
		$message = null
	) {
		try {

			// Instantiation and passing `true` enables exceptions
			$p = new \PhpProje\Library\Mail\PHPMailer\PHPMailer(true);

			$p->SetLanguage("tr", __DIR__ . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR);

			if ($this->smtpDebug)
			{
			
				$p->SMTPDebug = SMTP::DEBUG_LOWLEVEL;

				LoadMail::setOutput(array());
	
				$p->Debugoutput = function ($log, $level) {
					LoadMail::setOutput(array("level" => $level, "log" => $log));
				};
			}

			$p->isSMTP();
			$p->Host       = $this->host;
			$p->SMTPAuth   = true;
			$p->Username   = $this->user;
			$p->Password   = $this->password;
			$p->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			$p->Port       = $this->port;
			$p->CharSet = 'UTF-8';

			$p->setFrom('zamanbuakar@gmail.com', 'Tolga AKARDENİZ');
			$p->addAddress($mail, $name);
			$p->addReplyTo('zamanbuakar@gmail.com', 'Tolga AKARDENİZ');

			$p->isHTML(true);
			$p->Subject = $subject;
			$p->Body    = $message;
			//$p->AltBody = 'This is the body in plain text for non-HTML mail clients';

			return array("Islem" => true, "sendReturn" => $p->send(), "debugOutput" => LoadMail::getOutput());
		} catch (Exception $e) {
			return array("Islem" => false, "ErrorInfo" => $p->ErrorInfo, "Message" => $e->getMessage());
		}
	}
}