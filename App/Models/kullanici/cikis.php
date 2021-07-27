<?php

/**
 * بِسْــــــــــــــــــمِ اﷲِالرَّحْمَنِ اارَّحِيم
 * 
 * Eûzubillâhimineşşeytânirracîym - Bismillâhirrahmânirrahîm
 * 
 * Rahman ve Rahim olan "Allah" 'ın adıyla
 * 
 */

use PhpProje\Library\ErrorExeptions\CustomError;

class cikisModel extends Models
{
	public $db;

	public function __construct(App $app)
	{
		parent::__construct($app);

		/**
		 * Veritabanı bağlantısını tanımla
		 */
		$this->db = App::$c->getPdo();
	}

	public function Cikis()
	{

		$x = array();

		if (isset($_SESSION["Ref"])) {
			/**
			 * Kullanıcı künyesini al
			 */
			$x["Kunye"] = $_SESSION["Kunye"];

			/**
			 * Kullanıcı çıkış sayısını artır
			 * */
			$r = $this->db->query("UPDATE `KullanicilarBilgi` SET `Cikis`=(CASE WHEN (`Cikis`>0) THEN `Cikis`+1 ELSE 1 END), `CikisTarihi`=NOW() WHERE `KullaniciRef`=:Ref LIMIT 1;", array("Ref" => $_SESSION["Ref"]));

			/**
			 * Sezon bilgilerini sil ve yeniden başlat
			 */
			unset($_SESSION);
			session_destroy();
			session_start();

			/**
			 * BeniHatirla iptal
			 */
			if (isset($_COOKIE["BeniHatirla"])) {
				unset($_COOKIE["BeniHatirla"]);
				App::$c->setCookie("BeniHatirla", "");
			}

			/** Log ekle */
			logEkle($this->db, $this->app->title, "Çıkış", json_encode(array($_REQUEST)));
		} else {
			/**
			 * Son girilen sayfa varsa ona yoksa ana sayfaya gönder
			 */
			if (isset($_COOKIE["SonGirilenSayfa"])) {
				$y = $_COOKIE["SonGirilenSayfa"];
			} else {
				$y = "/";
			}

			header("location: {$y}");
			exit;
		}

		$this->app->viewArr = $x;
	}
}
