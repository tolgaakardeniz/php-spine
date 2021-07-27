<?php


namespace PhpProje\Library\ErrorExeptions;

use Exception;

/**
 * Costum error exception handler.
 *
 * @author Tolga AKARDENİZ <tolga.akardeniz@hotmail.com.tr>
 */

class CustomError extends Exception
{
    /**
     * Prettify error message output.
     *
     * @return string
     */
    public function errorMessage()
    {
        return $this->getMessage();
    }

	public static function message(string $title = null, string $message = null, string $redirect = null, int $second = 60)
	{
		return json_encode(array("title" => $title, "message" => $message, "redirect" => $redirect, "second" => $second));
	}
}