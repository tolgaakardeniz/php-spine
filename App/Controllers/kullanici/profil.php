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

class profilController extends Controllers
{
	public function __construct(App $app)
	{
		/**
		 * Sezonu başlat
		 */
		App::$session = new \PhpProje\Library\Session\Mysql(App::$c);

		/**
		 * Oturum açık ise ana sayfaya gönder
		 */
		if (!is_array(App::$c->get("user"))) {
			header("location: /");
			exit;
		}

		/**
		 * Veritabanı bağlantısı kontrol et
		 */
		if (!App::$c->getConncetion()) {
			throw new CustomError(CustomError::message("Database Error", "Database connection is closed. Please contact your site administrator."));
		}

		/**
		 * Kullanıcı bilgilerini al
		 */
		$user = App::$c->get("user");

		/**
		 * 
		 * Sadece bu sayfaya özeldir.
		 * 
		 * Oturumu açılmış kullanıcı ile profiline girilen kullanıcı aynı ise taze alınmış bilgi ile görüntü bilgisini güncelle
		 * 
		 */
		if ($app->u[0] === _isset($_SESSION, "Adi")) {
			$_SESSION["Goruntu"] = $user["Goruntu"];
		}

		/**
		 * Son girilen sayfa
		 */
		App::$c->setcookie("SonGirilenSayfa", $_SERVER["REQUEST_URI"]);

		/**
		 * Sayfa başlığı
		 * Page title
		 */
		$app->title = htmlspecialchars(_isset($user, "Kunye"));

		/**
		 * Sayfa açıklaması
		 * Page description
		 */
		$app->description = "Kullanıcı profil sayfası. " . $app->title . " kullanıcısının detaylı bilgileri.";

		/**
		 * Sayfa anahtar kelimeleri
		 * Page keywords
		 */
		$app->keywords = "Kullanıcı, Profil, Bilgi";

		/**
		 * Sayfa stil dosyaları
		 * Page css files
		 */
		$app->cssFiles = [
			["href" => templateRootUrl . "global/sweetalert2/sweetalert2.min.css"],
			["href" => templateRootUrl . "global/animate/animate.min.css"],
			["href" => templateRootUrl . "global/fontawesome/css/all.min.css"],
			["href" => templateUrl . "assets/css/colors.min.css"],
			["href" => templateUrl . "assets/css/kullanici/profil.min.css"]
		];

		/**
		 * Sayfa program betiği dosyaları
		 * Page java script files
		 */
		$app->jsFiles = [
			["src" => templateRootUrl . "global/inputmask/jquery.inputmask.min.js"],
			["src" => templateRootUrl . "global/sweetalert2/sweetalert2.all.min.js"],
			["src" => templateRootUrl . "global/wow/wow.min.js"],
			["src" => templateUrl . "assets/js/kullanici/profil.min.js"]
		];

		/**
		 * Özel view tanımlaması
		 */
		$app->customFile = "kullanici" . dirSep . "profil";

		/**
		 * Controllers sınıfının __construct çalıştır
		 */
		parent::__construct($app);
	}
}
