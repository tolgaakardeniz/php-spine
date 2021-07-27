<?php

namespace PhpProje\Library\Language;

use Exception;

class tr
{
	private $list = array(
		"0000" => "Deneme"
	);

	public function __construct()
	{
		try {
			// method body
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public function get($id)
	{
		try {
			// method body

			return $this->list[$id];
		} catch (Exception $e) {
			return "An error occurred. Translate not found from language list.";
		}
	}
}
