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

class girisController extends Controllers
{
	public function __construct(App $app)
	{
		/**
		 * Veritabanına bağlan
		 */
		App::$c->connectSqlServer();

		/**
		 * Sezonu başlat
		 */
		App::$session = new \PhpProje\Library\Session\Mysql(App::$c);

		/**
		 * Oturum açık ise ana sayfaya gönder
		 */
		if (isset($_SESSION["Ref"]))
		{
			header("location: /");
			exit;
		}

		/**
		 * Veritabanı bağlantısı kontrol et
		 */
		if (!App::$c->getConncetion())
		{
			throw new CustomError(CustomError::message("Database Error", "Database connection is closed. Please contact your site administrator."));
		}

		/**
		 * Sayfa başlığı
		 * Page title
		 */
		$app->title = "Kullanıcı Girişi";

		/**
		 * Sayfa açıklaması
		 * Page description
		 */
		$app->description = "Oturum açma ekranıdır.";

		/**
		 * Sayfa anahtar kelimeleri
		 * Page keywords
		 */
		$app->keywords = "Oturum, Sezon, Giriş";

		/**
		 * Sayfa stil dosyaları
		 * Page css files
		 */
		$app->cssFiles = [
			["href" => templateRootUrl . "global/sweetalert2/sweetalert2.min.css"]
		];

		/**
		 * Sayfa program betiği dosyaları
		 * Page java script files
		 */
		$app->jsFiles = [
			["src" => templateRootUrl . "global/inputmask/jquery.inputmask.min.js"],
			["src" => templateRootUrl . "global/sweetalert2/sweetalert2.all.min.js"],
			["src" => templateUrl . "assets/js/kullanici/giris.min.js"]
		];

		/**
		 * Controllers sınıfının __construct çalıştır
		 */
		parent::__construct($app);

	}
}