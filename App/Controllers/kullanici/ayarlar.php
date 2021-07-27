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

class ayarlarController extends Controllers
{

	public $db;

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
		if (!isset($_SESSION["Ref"]))
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
		 * Veritabanı bağlantısını tanımla
		 */
		$this->db = App::$c->getPdo();

		$x = (@count($_POST) > 0) ? 1 : 0;

		/** 
		 * Kullanıcı profil giriş sayısını artır
		 * */
		$r = $this->db->query("CALL `KullaniciProfiliGetirProseduru`(:Adi, :Ref, :Sayac);", array("Adi" => _isset($_SESSION,"Adi"), "Ref" => _isset($_SESSION,"Ref"), "Sayac" => $x));

		/**
		 * Kullanıcı bulunduysa
		 */
		if (count($r) > 0) {
			/**
			 * Kullanıcı bilgilerini tanımla
			 */
			App::$c->set("user", $r[0]);
		} else {
			throw new CustomError(CustomError::message("Database Error", "Your user reference not found from database."));
		}

		/**
		 * Sayfa başlığı
		 * Page title
		 */
		$app->title = "Ayarlar";

		/**
		 * Sayfa açıklaması
		 * Page description
		 */
		$app->description = "Kullanıcı ayarlar ekranıdır.";

		/**
		 * Sayfa anahtar kelimeleri
		 * Page keywords
		 */
		$app->keywords = "Kullanıcı, Ayarlar";

		/**
		 * Sayfa stil dosyaları
		 * Page css files
		 */
		$app->cssFiles = [
			["href" => templateRootUrl . "global/sweetalert2/sweetalert2.min.css"],
			["href" => templateRootUrl . "global/animate/animate.min.css"],
			["href" => templateRootUrl . "global/fontawesome/css/all.min.css"],
			["href" => templateUrl . "assets/css/colors.min.css"],
		];

		/**
		 * Sayfa program betiği dosyaları
		 * Page java script files
		 */
		$app->jsFiles = [
			["src" => templateRootUrl . "global/inputmask/jquery.inputmask.min.js"],
			["src" => templateRootUrl . "global/sweetalert2/sweetalert2.all.min.js"],
			["src" => templateRootUrl . "global/wow/wow.min.js"],
			["src" => templateUrl . "assets/js/kullanici/ayarlar.min.js"]
		];

		/**
		 * Controllers sınıfının __construct çalıştır
		 */
		parent::__construct($app);

	}
}