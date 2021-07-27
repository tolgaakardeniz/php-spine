<?php

namespace PhpProje;

use stdClass;

class stdClassToArray
{
	public function __construct()
	{
		
	}

	/**
	 * 
	 * Tolga AKARDENİZ
	 * 32.02.2010 23:21
	 * 
	 * @param class $class
	 * 
	 * @return array class list to array
	 */
	public function convert($class)
	{
		if (is_array($class)) {
			foreach ($class as $k => $v) {
				if (is_array($v)) {
					$class[$k] = $this->convert($v);
				}
				if ($v instanceof stdClass) {
					$class[$k] = $this->convert((array)$v);
				}
			}
		}

		if ($class instanceof stdClass) {
			return $this->convert((array)$class);
		}

		return $class;
	}
}
