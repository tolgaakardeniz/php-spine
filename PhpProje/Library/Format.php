<?php

namespace PhpProje\Library;

use PhpProje\Library\ErrorExeptions\CustomError;

class Format
{

	/**
	 *
	 * Sınıf ayarlamalarını yapar.
	 *
	 * @param array|bool $query "Kontrol listesi"
	 * @param array|bool $data "Kontrol edilecek listesi"
	 * @param array|bool $array "Alt listeler var mı?"
	 * 
	 * @author Tolga AKARDENİZ
	 * 
	 * @return array
	 *
	 */
	public static function initialize($query, $data): array
	{
		if ($data === true) {
			$data = array();
		}

		if ($query === true) {
			$query = $data;
		}

		$responseList = array();

		foreach ($query as $k => $v) {
			/**
			 * Gerekli tanımlaması varsa
			 */
			if (isset($v['required'])) {
				/**
				 * Gerekli tanımlamasının içerisinde Regexp varsa
				 */
				if (isset($v['required']['regexp'])) {
					/**
					 * Regexp kontrolü yap
					 */
					if (!preg_match($v['required']['regexp'], self::trim($data[$k]))) {
						throw new CustomError(CustomError::message("Format error", "RegExp kontrol tutarsız. RegExp: " . $v['required']['regexp'] . " Veri: " . $data[$k] . " Anahtar: " . $k));
					} else {
						$responseList[$k] = $data[$k];
						continue;
					}
				} else {
					/**
					 * Yoksa listenin içinde ara
					 */
					if (!in_array($data[$k], $v['required'])) {
						throw new CustomError(CustomError::message("Format error", "Gerekli kontrolü tutarsız. Gerekli: " . json_encode($v['required']) . " Veri: " . $data[$k] . " Anahtar: " . $k));
					} else {
						$responseList[$k] = $data[$k];
						continue;
					}
				}
			} else if (isset($v['format'])) {
				$formatName = $v['format'];
				$data[$k] = self::$formatName($data[$k]);
				$responseList[$k] = $data[$k];
				continue;
			} else if (isset($v['regexp'])) {
				/**
				 * Veri yoksa pas geç
				 */
				if (isset($data[$k])) {
					/**
					 * Veri varsa Regexp kontrolü yap
					 */
					if (!preg_match($v['regexp'], self::trim($data[$k]))) {
						throw new CustomError(CustomError::message("Format error", "RegExp kontrol tutarsız. RegExp: " . $v['regexp'] . " Veri: " . $data[$k] . " Anahtar: " . $k));
					} else {
						$responseList[$k] = $data[$k];
						continue;
					}
				}
			} else {
				if (!isset($data[$k])) {
					//throw new CustomError(CustomError::message("Format error", "Sorgunun karşılığı bulunamadı. Sorgu: " . $k . " Veri: " . json_encode($v) . " Anahtar: " . $k));
				} else {
					if (is_array($v)) {
						if (is_array($data[$k])) {
							if (count($data[$k]) > 0) {
								foreach ($data[$k] as $k1 => $v1) {
									if (isset($v[0])) {
										$responseList[$k][] = self::initialize($v[0], $v1);
									} else {
										$responseList[$k][] = self::initialize($v, $v1);
									}
								}
							}
						}
					} else {
						$responseList[$k] = self::trim($data[$k]);
					}
				}
			}
		}

		return $responseList;
	}

	/**
	 *
	 * Url içerisindeki özel parametleri ayıklar
	 *
	 * @author Tolga AKARDENİZ
	 * @param string $apiUrl
	 * @return array 
	 *
	 */
	public static function getUrlSpecialParameters($apiUrl)
	{
		if (preg_match_all('@\{(.*?)\}@si', $apiUrl, $output)) {
			return $output[1];
		}

		return array();
	}

	/**
	 *
	 * UnixTime değerini milisaniye cinsine çevririr.
	 *
	 * @author Tolga AKARDENİZ
	 * @param int $timestamp
	 *
	 */
	public static function unixTime($timestamp)
	{
		return $timestamp * 1000;
	}

	/**
	 *
	 * Metnin başındaki ve sonundaki boşlukları siler.
	 *
	 * @author Tolga AKARDENİZ
	 * @param int $timestamp
	 *
	 */
	public static function trim($text)
	{
		return trim($text);
	}
}
