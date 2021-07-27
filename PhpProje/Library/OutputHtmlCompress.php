<?php

namespace PhpProje\Library;

class OutputHtmlCompress
{
	public $html;
	public $compressedHtml;
	public $startMicroTime;
	public $finishMicroTime;

	public function __construct()
	{

	}

	/**
	 * 
	 * Tolga AKARDENİZ
	 * 02.02.2010 06:35
	 * 
	 * @param string $h
	 * 
	 * @return string html to compressed html 
	 */
	public function compress($h)
	{
		$this->html = $h;
		$this->startMicroTime = microtime(true);

		$s = array(
			'/\t/',
			'/(\n|^)(\x20+|\t)/',
			'/(\n|^)\/\/(.*?)(\n|$)/',
			'/\n/',
			'/\<\!--.*?-->/',
			'/(\x20+|\t)/', # Delete multispace (Without \n)
			'/\>\s+\</', # strip whitespaces between tags
			'/(\"|\')\s+\>/', # strip whitespaces between quotation ("') and end tags
			'/=\s+(\"|\')/'
		);

		$r = array(
			"",
			"\n",
			"\n",
			" ",
			"",
			" ",
			"><",
			"$1>",
			"=$1"
		);

		$this->finishMicroTime = microtime(true);
		$this->compressedHtml = preg_replace($s, $r, $h);

		return $this->compressedHtml;
	}
}