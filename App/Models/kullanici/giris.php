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

class girisModel extends Models
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

	public function Giris()
	{
		/**
		 * Dizi tanımla
		 */
		$x = array();

		/**
		 * Giriş fonksiyonu kullanıldığında beklenen değişkenleri al
		 */
		$x["userName"] = $this->db->escape(_post("KullaniciAdi"));
		$x["password"] = $this->db->escape(_post("Parola"));
		$x["remember"] = $this->db->escape(_post("BeniHatirla"));

		$beniHatirla = NULL;

		/**
		 * BeniHatirla ile ilgili tanımlamalar kontrol yapılır
		 */
		if (!is_null($x["remember"])) {
			if (isset($_COOKIE["BeniHatirla"])) {
				$beniHatirla = $_COOKIE["BeniHatirla"];
			} else {
				$beniHatirla = md5(sha1(sha1(microtime() . rand(1111111, 9999999))));
				App::$c->setCookie("BeniHatirla", $beniHatirla);
			}
		} else {
			App::$c->setCookie("BeniHatirla", "");
		}


		/**
		 * Kullanıcı oturum açmak istiyorsa
		 */
		if (isset($_POST["KullaniciAdi"]) && isset($_POST["Parola"])) {
			$x["errors"] = array();

			$y = "A-Za-z0-9-_";

			$kullaniciAdi = $_POST["KullaniciAdi"];

			if (!preg_match("/^[{$y}]{3,24}$/", $kullaniciAdi)) {
				$x["errors"][] = "Kullanını adınız hatalı yazılmış. Sadece ({$y}) karakterlerini kullanmanız gerekiyor.";
			} else {
				$parola = md5(md5($_POST["Parola"]));
				$kontrol =  md5(md5($kullaniciAdi) . $parola);
				$kullaniciAdi = $_POST["KullaniciAdi"];

				$beniHatirla = (strlen($beniHatirla) >= 1) ? $beniHatirla : "NULL";

				$ip = App::$c->getIp();
				$tarayici = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : NULL;
				$hamVeri = (count($_REQUEST) > 0) ? json_encode($_REQUEST) : NULL;

				$q = $this->db->query("CALL `KullaniciGirisiProseduru` (?,?,?,?);", array($kullaniciAdi, $kontrol, $beniHatirla, $ip));

				if (count($q) > 0) {
					if (isset($q[0]["Islem"])) {
						if ($q[0]["Islem"] < 1) {
							$q = array();
						}
					} else {
						$q = array();
					}
				}

				if (count($q) > 0) {
					$detay = $q = $q[0];

					unset($_SESSION);
					session_destroy();
					session_start();

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

					$q = $this->db->query("SELECT `Ref`, `ModulKodu`, `KullaniciTuruRef`, `KullaniciTuruKodu`, `Ekle`, `Duzenle`, `Sil`, `Gir` FROM `YetkilerListe` WHERE `KullaniciTuruRef`=:Turu;", array("Turu" => $_SESSION["KullaniciTuru"]));

					$yetkiler = array();

					if (count($q) > 0) {
						foreach ($q as $k => $v) {
							$yetkiler[$v["ModulKodu"]] = $v;
						}
					}

					//$_SESSION["Yetkiler"] = $yetkiler;

					/** Geçerli giriş etkinliği ekle */
					/* 					$y = "Call EtkinlikEkleProseduru (?, ?, ?, ?, ?);";
					$a = array($_SESSION["Adi"], "Geçerli Giriş", $tarayici, $hamVeri, $ip);
					$q = $this->db->query($y, $a); */

					/** Ip bilgisi ekle */
					$a = array("Ip" => $ip);
					$q = $this->db->query("SELECT `Ref` FROM `Ip` WHERE `Ip`=:Ip AND ((`SonGuncellemeTarihi` IS NULL AND `OlusturmaTarihi`>(NOW() - INTERVAL 1 MONTH)) OR (`SonGuncellemeTarihi` IS NOT NULL AND `SonGuncellemeTarihi`>(NOW() - INTERVAL 1 MONTH))) LIMIT 1", $a);

					if (count($q) < 1) {
						/** Ip lokasyon bilgisini yükle */
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

					/** Kullanıcı giriş sayısını artır */
					$q = $this->db->query("UPDATE `KullanicilarBilgi` SET `Giris`=(CASE WHEN (`Giris`>0) THEN `Giris`+1 ELSE 1 END) WHERE `KullaniciRef`=:Ref LIMIT 1;", array("Ref" => $_SESSION["Ref"]));

					/** Log ekle */
					logEkle($this->db, $this->app->title, "Giriş", json_encode(array($_REQUEST)));

					/**
					 * Son girilen sayfa varsa ona yoksa ana sayfaya gönder
					 */
					if (isset($_COOKIE["SonGirilenSayfa"])) {
						$y = $_COOKIE["SonGirilenSayfa"];
					} else {
						$y = "/";
					}

					/**
					 * Gönderim sistemi json olarak yapılıyorsa
					 */
					if (_post("Tur") === "json") {
						header('Content-Type: application/json');
						echo json_encode(
							array(
								"status" => true,
								"redirect" => "/",
								"errorCount" => count($x["errors"]),
								"errors" => $x["errors"],
								"userInfo" => ((_post("Detay") === "true") ? json_encode($detay) : NULL)
							)
						);
					} else {
						header("location: {$y}");
					}

					exit;
				} else {
					/** Geçersiz giriş etkinliği ekle */
					$y = "Call EtkinlikEkleProseduru (?, ?, ?, ?, ?);";
					$a = array($kullaniciAdi, "Geçersiz Giriş", $tarayici, $hamVeri, $ip);
					$q = $this->db->query($y, $a);

					/** Log ekle */
					logEkle($this->db, $this->app->title, "Geçersiz Giriş", "Kullanıcı Adı : {$kullaniciAdi}, Parola : {$parola}" . (is_null($beniHatirla) ? NULL : ", Beni Hatırla : {$beniHatirla}"));

					$x["errors"][] = "Kullanıcı adınız veya parolanız geçerli değil.";
				}
			}

			/**
			 * BeniHatirla iptal
			 */
			unset($_COOKIE["BeniHatirla"]);
			App::$c->setCookie("BeniHatirla", "");

			/**
			 * Gönderim sistemi json olarak yapılıyorsa
			 */
			if (_post("Tur") === "json") {
				httpResponseCode(500);
				header('Content-Type: application/json');
				echo json_encode(
					array(
						"status" => false,
						"errorCount" => count($x["errors"]),
						"errors" => $x["errors"]
					)
				);
				exit;
			}
		}

		$this->app->viewArr = $x;
	}
}
