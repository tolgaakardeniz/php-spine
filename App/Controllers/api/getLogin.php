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

class getLoginController extends Controllers
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
		$app->title = "Kullanıcı Girişi";

		/**
		 * Set controllers
		 */
		$query = array(
			// User name
			"userName" => array('required' => array("regexp" => "/^(?![0-9]+)[A-Za-z0-9-_]{3,24}$/")),
			// User password
			"password" => array('required' => array("regexp" => "/^.{32}$/")),
			// Remember me
			"remember" => array('required' => array("regexp" => "/^(.{32}|true|false)$/")),
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
		$password = $x["password"] = $requestData["password"];
		$x["remember"] = $requestData["remember"];



		$remember = NULL;

		/**
		 * BeniHatirla ile ilgili tanımlamalar kontrol yapılır
		 */
		if ($x["remember"]) {
			if (isset($_COOKIE["remember"])) {
				$remember = $_COOKIE["remember"];
			} else {
				$remember = md5(sha1(sha1(microtime() . rand(1111111, 9999999))));
				App::$c->setCookie("remember", $remember);
			}
		} else {
			App::$c->setCookie("remember", "");
		}


		$control =  md5(md5($x["userName"]) . $x["password"]);


		$remember = (strlen($remember) >= 1) ? $remember : "NULL";

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

		$q = $this->db->query("CALL `KullaniciGirisiProseduru` (?,?,?,?);", array($userName, $control, $remember, $ip));

		if (count($q) > 0) {
			if (isset($q[0]["Islem"])) {
				if ($q[0]["Islem"] < 1) {
					$q = array();
				}
			} else {
				$q = array();
			}
		}

		/**
		 * Veritabanından sonuç duğru gelirse
		 */
		if (count($q) > 0) {
			$detay = $q = $q[0];

			/**
			 * Sezonu yeniden başlar
			 */
			unset($_SESSION);
			session_destroy();
			session_start();

			/**
			 * Kullanıcı bilgilerini sezona ekle
			 */
			$_SESSION["Ref"] = $q["Ref"];
			$_SESSION["Adi"] = $q["Adi"];
			$_SESSION["Parola"] = $q["Parola"];
			$_SESSION["Kunye"] = $q["Kunye"];
			$_SESSION["Yetkisi"] = $q["Yetkisi"];
			$_SESSION["Posta"] = $q["Posta"];
			$_SESSION["KullaniciTuru"] = is_numeric($q["KullaniciTuru"]) ? $q["KullaniciTuru"] : NULL;
			$_SESSION["Pasif"] = is_numeric($q["Pasif"]) ? $q["Pasif"] : NULL;
			$_SESSION["OlusturmaTarihi"] = $q["OlusturmaTarihi"];
			$_SESSION["PostaOnay"] = $q["PostaOnay"];
			$_SESSION["Goruntu"] = $q["Goruntu"];
			$_SESSION["Vatandas"] = $q["Vatandas"];
			$_SESSION["Dogum"] = date('d/m/Y', strtotime($q["Dogum"]));
			$_SESSION["Telefon"] = $q["Telefon"];
			$_SESSION["Cinsiyet"] = $q["Cinsiyet"];
			$_SESSION["OturumTarihi"] = $q["OturumTarihi"];

			/**
			 * Veritabanından kullanıcı yetkilerini al
			 */
			$q = $this->db->query("SELECT `Ref`, `ModulKodu`, `KullaniciTuruRef`, `KullaniciTuruKodu`, `Ekle`, `Duzenle`, `Sil`, `Gir` FROM `YetkilerListe` WHERE `KullaniciTuruRef`=:Turu;", array("Turu" => $_SESSION["KullaniciTuru"]));

			$yetkiler = array();

			if (count($q) > 0) {
				foreach ($q as $k => $v) {
					$yetkiler[$v["ModulKodu"]] = $v;
				}
			}

			//$_SESSION["Yetkiler"] = $yetkiler;

			/**
			 * Geçerli giriş etkinliği ekle
			 * */
			$y = "Call EtkinlikEkleProseduru (?, ?, ?, ?, ?);";
			$a = array($_SESSION["Adi"], "Geçerli Giriş", $browser, $rawData, $ip);
			$q = $this->db->query($y, $a);

			/**
			 * Ip bilgisi ekle
			 * */
			$a = array("Ip" => $ip);
			$q = $this->db->query("SELECT `Ref` FROM `Ip` WHERE `Ip`=:Ip AND ((`SonGuncellemeTarihi` IS NULL AND `OlusturmaTarihi`>(NOW() - INTERVAL 1 MONTH)) OR (`SonGuncellemeTarihi` IS NOT NULL AND `SonGuncellemeTarihi`>(NOW() - INTERVAL 1 MONTH))) LIMIT 1", $a);

			if (count($q) < 1) {
				/**
				 * Iplokasyon bilgisini yükle
				 * */
				$a = (array)json_decode(@file_get_contents("http://ipinfo.io/{$ip}"), true);

				if (isset($a["loc"])) {
					$z = explode(",", $a["loc"])[0];
					$y = explode(",", $a["loc"])[1];

					$a = array();

					$a["GelenIp"] = $ip;
					$a["GelenIsim"] = isset($Js["hostname"]) ? $Js["hostname"] : "NULL";
					$a["GelenSehir"] = isset($Js["city"]) ? $Js["city"] : "NULL";
					$a["GelenBolge"] = isset($Js["region"]) ? $Js["region"] : "NULL";
					$a["GelenUlke"] = isset($Js["country"]) ? $Js["country"] : "NULL";
					$a["GelenEnlem"] = $z;
					$a["GelenBoylam"] = $y;
					$a["GelenOrganizasyon"] = isset($Js["org"]) ? $Js["org"] : "NULL";
					$a["GelenPostaKodu"] = isset($Js["postal"]) ? $Js["postal"] : "NULL";
					$a["GelenSaatDilimi"] = isset($Js["timezone"]) ? $Js["timezone"] : "NULL";
					$a["GelenOlusturanRef"] = $_SESSION["Ref"];
					$a["GelenOlusturanIp"] = $ip;

					$q = $this->db->query("CALL `IpEkleProseduru` (:GelenIp, :GelenIsim, :GelenSehir, :GelenBolge, :GelenUlke, :GelenEnlem, :GelenBoylam, :GelenOrganizasyon, :GelenPostaKodu, :GelenSaatDilimi, :GelenOlusturanRef, :GelenOlusturanIp);", $a);
				}
			}

			/**
			 * Kullanıcı giriş sayısını artır
			 * */
			$q = $this->db->query("UPDATE `KullanicilarBilgi` SET `Giris`=(CASE WHEN (`Giris`>0) THEN `Giris`+1 ELSE 1 END) WHERE `KullaniciRef`=:Ref LIMIT 1;", array("Ref" => $_SESSION["Ref"]));

			/**
			 * Log ekle
			 * */
			logEkle($this->db, $app->title, "Giriş", json_encode(array($_REQUEST)));

			/**
			 * Sonuç döndür
			 */
			echo json_encode(
				array(
					"status" => true,
					"redirect" => "/",
					"errorCount" => count($x["errors"]),
					"errors" => $x["errors"],
					"userInfo" => json_encode($detay)
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
			$a = array($userName, "Geçersiz Giriş", $browser, $rawData, $ip);
			$q = $this->db->query($y, $a);

			/**
			 * Log ekle
			 * */
			logEkle($this->db, $app->title, "Geçersiz Giriş", "Kullanıcı Adı : {$userName}, Parola : {$password}" . (is_null($remember) ? NULL : ", Beni Hatırla : {$remember}"));

			/**
			 * Sonuç döndür
			 */
			$x["errors"][] = "Kullanıcı adınız veya parolanız geçerli değil.";

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
