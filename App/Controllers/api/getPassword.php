<?php

/**
 * بِسْــــــــــــــــــمِ اﷲِالرَّحْمَنِ اارَّحِيم
 * 
 * Eûzubillâhimineşşeytânirracîym - Bismillâhirrahmânirrahîm
 * 
 * Rahman ve Rahim olan "Allah" 'ın adıyla
 * 
 */

use PhpProje\Library\Format;
use PhpProje\Library\ErrorExeptions\CustomError;

class getPasswordController extends Controllers
{
	public function __construct(App $app)
	{
		/**
		 * Send json header to user
		 */
		header('Content-Type: application/json');


		/**
		 * Sayfa başlığı
		 * Page title
		 */
		$app->title = "Parola Hatırlat";

		/**
		 * Set controllers
		 */
		$query = array(
			// User email
			"email" => array('required' => array("regexp" => "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/")),
		);

		/**
		 * Check controllers
		 */
		$requestData = Format::initialize($query, $_POST);


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
			echo json_encode(array("status" => false, "errorCount" => 1, "errors" => array("Oturumunuz zaten açık.")));
			exit;
		}

		/**
		 * Veritabanı bağlantısı kontrol et
		 */
		if (!App::$c->getConncetion()) {
			throw new CustomError(CustomError::message("Database Error", "Database connection is closed. Please contact your site administrator."));
		}

		/**
		 * Veritabanı bağlantısını tanımla
		 */
		$this->db = App::$c->getPdo();

		/**
		 * Dizi tanımla
		 */
		$x = array();

		/**
		 * Giriş fonksiyonu kullanıldığında beklenen değişkenleri al
		 */
		$email = $x["email"] = $requestData["email"];

		/**
		 * Hatalar için dizi listesi oluştur
		 */
		$x["errors"] = array();

		/**
		 * Kullanıcı bilgilerini al
		 */
		$ip = App::$c->getIp();
		$browser = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : NULL;
		$rawData = (count($_REQUEST) > 0) ? json_encode($_REQUEST) : NULL;

		/**
		 * Gelen kullanıcı elektronik postasını sorgula
		 */
		$q = $this->db->query("SELECT * FROM `Kullanicilar` WHERE `Posta`=? LIMIT 1", array($email));

		/**
		 * Veritabanından sonuç duğru gelirse
		 */
		if (count($q) > 0) {
			$data = $q[0];

			/**
			 * Geçerli giriş etkinliği ekle
			 * */
			$y = "Call EtkinlikEkleProseduru (?, ?, ?, ?, ?);";
			$a = array($q["Adi"], "Parolamı Unuttum", $browser, $rawData, $ip);
			$q = $this->db->query($y, $a);

			/**
			 * Log ekle
			 * */
			logEkle($this->db, $app->title, "Parolamı Unuttum", json_encode(array($_REQUEST)));

			/**
			 * Sonuç döndür
			 */
			echo json_encode(
				array(
					"status" => true,
					"errorCount" => count($x["errors"]),
					"errors" => $x["errors"],
					"userInfo" => json_encode($data)
				)
			);

			/**
			 * Vertabanından kullanıcı bilgisi gelmediyse
			 */
		} else {
			/**
			 * Geçersiz giriş etkinliği ekle
			 * */
			$y = "Call EtkinlikEkleProseduru (?, ?, ?, ?, ?);";
			$a = array($email, "Geçersiz parolamı unuttum sorgulaması", $browser, $rawData, $ip);
			$q = $this->db->query($y, $a);

			/**
			 * Log ekle
			 * */
			logEkle($this->db, $app->title, "Geçersiz parolamı unuttum sorgulaması", json_encode(array($_REQUEST)));

			/**
			 * Sonuç döndür
			 */
			$x["errors"][] = "Elektronik posta adresi bulunamadı.";

			echo json_encode(
				array(
					"status" => false,
					"redirect" => "/",
					"errorCount" => count($x["errors"]),
					"errors" => $x["errors"]
				)
			);
		}

		/**
		 * İşlemleri bitir
		 */
		exit;
	}
}
