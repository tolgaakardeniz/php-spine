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

class setRegisterController extends Controllers
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
		$app->title = "Kullanıcı Adı Sorgula";

		/**
		 * Set controllers
		 */
		$query = array(
			// User Name and Surname
			"nameAndSurname" => array('required' => array("regexp" => "/^[^\d\s]{3,}(?:( {1}[^\d\s\W]{3,})+)$/")),
			// User name
			"userName" => array('required' => array("regexp" => "/^(?![0-9]+)[A-Za-z0-9-_]{3,24}$/")),
			// User email
			"email" => array('required' => array("regexp" => "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/")),
			// User password
			"password" => array('required' => array("regexp" => "/^.{0,32}$/")),
			// User password
			"repeatPassword" => array('required' => array("regexp" => "/^.{0,32}$/")),
			// User contract
			"userContract" => array('required' => array("regexp" => "/^true$/"))
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
		$userName = $x["userName"] = $requestData["userName"];

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
		$q = $this->db->query("SELECT * FROM `Kullanicilar` WHERE `Adi`=? LIMIT 1", array($userName));

		/**
		 * Veritabanından sonuç duğru gelirse
		 */
		if (count($q) > 0) {
			$data = $q[0];

			/**
			 * Geçerli giriş etkinliği ekle
			 * */
			$y = "Call EtkinlikEkleProseduru (?, ?, ?, ?, ?);";
			$a = array($q["Adi"], "Kullanıcı adı sorgulaması", $browser, $rawData, $ip);
			$q = $this->db->query($y, $a);

			/**
			 * Log ekle
			 * */
			logEkle($this->db, $app->title, "Kullanıcı adı sorgulaması", json_encode(array($_REQUEST)));

			/**
			 * Sonuç döndür
			 */
			$x["errors"][] = "Kullanıcı adı kullanılıyor.";

			echo json_encode(
				array(
					"status" => false,
					"redirect" => "/",
					"errorCount" => count($x["errors"]),
					"errors" => $x["errors"]
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
			$a = array($userName, "Geçersiz kullanıcı adı sorgulaması", $browser, $rawData, $ip);
			$q = $this->db->query($y, $a);

			/**
			 * Log ekle
			 * */
			logEkle($this->db, $app->title, "Geçersiz kullanıcı adı sorgulaması", json_encode(array($_REQUEST)));

			/**
			 * Sonuç döndür
			 */
			echo json_encode(
				array(
					"status" => true,
					"errorCount" => count($x["errors"]),
					"errors" => $x["errors"],
				)
			);
		}

		/**
		 * İşlemleri bitir
		 */
		exit;
	}
}
