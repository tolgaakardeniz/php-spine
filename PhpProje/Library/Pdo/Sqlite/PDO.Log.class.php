<?php
class PDOLog
{
    private $path = DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "mysql" . DIRECTORY_SEPARATOR;

    public function __construct()
    {
		$this->path = $_SERVER["DOCUMENT_ROOT"] . $this->path;
    }

	public function write(string $message, $fileSalt)
	{
		$date = new DateTime();
		$logFileName  = $this->path . $date->format('Y-m-d') . "-" . md5($date->format('Y-m-d') . $fileSalt) . ".txt";

		if (is_dir($this->path)) {
			try {
				$logContent = "Time : " . $date->format('H:i:s:u') . "\n" . $message . "\n\n";
				file_put_contents($logFileName, $logContent, FILE_APPEND);
				return true;
			} catch (Exception $e) {
				throw new Exception("Unable to write log file. (" . $logFileName . ") Error: " . $e->getMessage());
			}
		} else {
			try {
				if (@mkdir($this->path, 0755, true) === true) {
					return $this->write($message, $fileSalt);
				} else {
					throw new Exception("Unable to create log director. (" . $this->path . ")");
				}
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
		}
	}
}