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

class indexController extends Controllers
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
		 * Veritabanı bağlantısı kontrol et
		 */
		if (!App::$c->getConncetion())
		{
			throw new CustomError(CustomError::message("Database Error", "Database connection is closed. Please contact your site administrator."));
		}

		/**
		 * Son girilen sayfa
		 */
		App::$c->setcookie("SonGirilenSayfa", $_SERVER["REQUEST_URI"]);

		/**
		 * Sayfa başlığı
		 * Page title
		 */
		$app->title = "Medical";

		/**
		 * Sayfa açıklaması
		 * Page description
		 */
		$app->description = "Deneme";

		/**
		 * Sayfa anahtar kelimeleri
		 * Page keywords
		 */
		$app->keywords = "Deneme";

		/**
		 * Sayfa stil dosyaları
		 * Page css files
		 */
		$app->cssFiles = [
			["href" => templateRootUrl . "global/sweetalert2/sweetalert2.min.css"],
			["href" => templateRootUrl . "global/animate/animate.min.css"],
			/* ["href" => templateRootUrl . "/global/waves/waves.min.css"], */
			["href" => templateRootUrl . "global/fontawesome/css/all.min.css"]
		];

		/**
		 * Sayfa program betiği dosyaları
		 * Page java script files
		 */
		$app->jsFiles = [
			["src" => templateRootUrl . "global/sweetalert2/sweetalert2.all.min.js"],
			["src" => templateRootUrl . "global/wow/wow.min.js"],
			/* ["src" => templateRootUrl . "/waves/waves.min.js"], */
			["src" => templateUrl . "assets/js/index.min.js"]
		];


		/**
		 * Controllers sınıfının __construct çalıştır
		 */
		parent::__construct($app);
	}
}