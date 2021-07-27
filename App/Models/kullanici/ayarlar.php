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

class ayarlarModel extends Models
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

	/**
	 * Profil fotoğrafı yükleme
	 */
	public function profilFotografi()
	{
		/**
		 * Dizi tanımla
		 */
		$x = array();

		/**
		 * Hataların biriktirileceği liste
		 */
		$x["errors"] = array();

		/**
		 * Kullanıcı tanımlamalarını al
		 */
		$user = App::$c->get("user");

		/**
		 * Kontroller
		 */

		if (is_null(_isset($_SESSION, "Ref"))) {
			/**
			 * Kullanıcı kontrol edilir
			 */
			$x["errors"][] = "Oturum açılması gerekiyor.";
		} else if (is_null(_isset($_FILES, "file"))) {
			/**
			 * Dosya gönderilmemiş ise
			 */
			$x["errors"][] = "Dosya göndermeniz gerekiyor.";
		} else {
			/**
			 * Hata numarasını al
			 */
			$z = _isset($_FILES["file"], "error");

			/**
			 * Hata yoksa devam et
			 */
			if ($z === UPLOAD_ERR_OK) {
				/**
				 * Devam et
				 */
				$c = true;

				/**
				 * Kullanıcı veritabanı benzersiz numarası
				 */
				$ref = $user["Ref"];

				/**
				 * Fotoğrafın kayıt olacağı klasör yolu
				 */
				$path = dirSep . str_replace(rootDir, "", profilePhotoDir) . date("Y.m.d") . dirSep . $ref;

				/**
				 * Yüklenen dosyaya ait bilgiler
				 */
				$f = $_FILES["file"];

				/**
				 * Fotoğraf pixeli
				 */
				list($width, $height) = @getimagesize($f["tmp_name"]);

				/**
				 * Büyüklük kontrolü
				 */
				if (($height<201) || ($width<201))
				{
					throw new Exception("Büyük bir fotoğraf yüklemelisiniz.");
				}

				/**
				 * Benzersiz dosya ismi oluştur
				 */
				$hashids = new \PhpProje\Library\Hashids\Hashids($ref, 7);
				$name = $hashids->encode(1);

				/**
				 * Dosya uzantısını belirle
				 * */
				switch ($f["type"]) {
					case "image/png":
						$extension = ".png";
						break;
					case "image/jpeg":
						$extension = ".jpg";
						break;
					case "image/gif":
						$extension = ".gif";
						break;
					case "image/webp":
						$extension = ".webp";
						break;
					default:
						$extension = ".jpg";
						break;
				}

				/**
				 * Önce veritabanına kayıt et
				 */
				$r = $this->db->query("UPDATE `KullanicilarGenel` SET `Goruntu`=(CASE WHEN (JSON_VALID(`Goruntu`)=1) THEN JSON_SET(`Goruntu`, '$.Profil', :Goruntu) ELSE JSON_OBJECT('Profil',:Goruntu,'Arka',NULL) END) WHERE `KullaniciRef`=:Ref LIMIT 1;", array("Goruntu" => ($path . dirSep . $name . $extension), "Ref" => $ref));

				/**
				 * Kayıt edildiyse
				 */
				if ($r > 0) {
					/**
					 * Sondaki / sil
					 */
					$y = ((substr(rootDir, strlen(rootDir) - 1, 1) === dirSep) ? substr(rootDir, 0, -1) : rootDir);

					/**
					 * Görüntü bilgilerini Json'dan çevir
					 */
					$z = @json_decode(_isset($_SESSION, "Goruntu"), true);

					/**
					 * Profil fotoğrafı bilgilerini kontrol et
					 */
					if (!is_null(_isset($z, "Profil"))) {
						/**
						 * Path bilgisi al
						 */
						$z = pathinfo($y . $z["Profil"]);

						/**
						 * Kullanıcıya ait önceki tüm fotoğrafları sil
						 */
						delTree($z["dirname"]);
					}

					/**
					 * Dosyayı istenen yere taşı
					 */
					$upload = PhpProje\Library\SimplePhpUpload\Upload::factory($path, $y);
					$upload->file($f);
					$upload->set_filename($name . $extension);
					$upload->set_allowed_mime_types(array("image/png", "image/jpeg", "image/webp", "image/gif"));
					$y = $upload->upload();

					/**
					 * Upload sorunsuz ise
					 */
					if ((_isset($y, "status") === true) && (is_array(_isset($y, "errors")))) {
						/**
						 * Hata sıfırdan çoksa
						 */
						if (count($y["errors"]) > 0) {
							/**
							 * Devam etme
							 */
							$c = false;

							/**
							 * Hataları birleştir
							 */
							$x["errors"] = array_merge($x["error"], $y["errors"]);
						} else {
							/**
							 * Küçültme işlemini yap
							 */
							$imageLib = new PhpProje\Library\PhpImageMagician\imageLib($y["full_path"]);
							$imageLib->resizeImage(200, 200, array('crop', 'm'));
							$z = $imageLib->saveImage($y["full_path"], 100);

							/**
							 * İşlem sorunsuz ise
							 */
							if ($z === true) {
								/**
								 * Gönderim sistemi json olarak yapılıyorsa
								 */
								if (_post("Tur") === "json") {
									header('Content-Type: application/json');
									echo json_encode(
										array(
											"status" => true,
											"redirect" => $_SERVER["REQUEST_URI"],
											"errorCount" => count($x["errors"]),
											"errors" => $x["errors"]
										)
									);

									exit;
								}
							} else {
								/**
								 * Devam etme
								 */
								$c = false;
							}
						}
					}
				}
			} else {
				/**
				 * Hata bilgileri
				 */
				$phpFileUploadErrors = array(
					0 => 'There is no error, the file uploaded with success',
					1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
					2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
					3 => 'The uploaded file was only partially uploaded',
					4 => 'No file was uploaded',
					6 => 'Missing a temporary folder',
					7 => 'Failed to write file to disk.',
					8 => 'A PHP extension stopped the file upload.',
				);

				/**
				 * Hata mesajı tanımla
				 */
				$y = _isset($phpFileUploadErrors, $z);

				/**
				 * Hata ekle
				 */
				$x["errors"][] = "Hata oluştu. Hata: " . is_null($y) ? "Bilinmeyen hata" :  $y;
			}
		}

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

		$this->app->viewArr = $x;
	}

	/**
	 * Profil arka planı yükleme
	 */
	public function profilArkaPlani()
	{
		/**
		 * Dizi tanımla
		 */
		$x = array();

		/**
		 * Hataların biriktirileceği liste
		 */
		$x["errors"] = array();

		/**
		 * Kullanıcı tanımlamalarını al
		 */
		$user = App::$c->get("user");

		/**
		 * Kontroller
		 */

		if (is_null(_isset($_SESSION, "Ref"))) {
			/**
			 * Kullanıcı kontrol edilir
			 */
			$x["errors"][] = "Oturum açılması gerekiyor.";
		} else if (is_null(_isset($_FILES, "file"))) {
			/**
			 * Dosya gönderilmemiş ise
			 */
			$x["errors"][] = "Dosya göndermeniz gerekiyor.";
		} else {
			/**
			 * Hata numarasını al
			 */
			$z = _isset($_FILES["file"], "error");

			/**
			 * Hata yoksa devam et
			 */
			if ($z === UPLOAD_ERR_OK) {
				/**
				 * Devam et
				 */
				$c = true;

				/**
				 * Kullanıcı veritabanı benzersiz numarası
				 */
				$ref = $user["Ref"];

				/**
				 * Fotoğrafın kayıt olacağı klasör yolu
				 */
				$path = dirSep . str_replace(rootDir, "", backPhotoDir) . date("Y.m.d") . dirSep . $ref;

				/**
				 * Yüklenen dosyaya ait bilgiler
				 */
				$f = $_FILES["file"];

				/**
				 * Fotoğraf pixeli
				 */
				list($width, $height) = @getimagesize($f["tmp_name"]);

				/**
				 * Büyüklük kontrolü
				 */
				if (($height<600) || ($width<1024))
				{
					throw new Exception("Büyük bir fotoğraf yüklemelisiniz. Genişliği 1024 Piksel yüksekliği 600 pikselden çok olmalıdır.");
				}

				/**
				 * Benzersiz dosya ismi oluştur
				 */
				$hashids = new \PhpProje\Library\Hashids\Hashids($ref, 7);
				$name = $hashids->encode(1);

				/**
				 * Dosya uzantısını belirle
				 * */
				switch ($f["type"]) {
					case "image/png":
						$extension = ".png";
						break;
					case "image/jpeg":
						$extension = ".jpg";
						break;
					case "image/gif":
						$extension = ".gif";
						break;
					case "image/webp":
						$extension = ".webp";
						break;
					default:
						$extension = ".jpg";
						break;
				}

				/**
				 * Önce veritabanına kayıt et
				 */
				$r = $this->db->query("UPDATE `KullanicilarGenel` SET `Goruntu`=(CASE WHEN (JSON_VALID(`Goruntu`)=1) THEN JSON_SET(`Goruntu`, '$.Arka', :Goruntu) ELSE JSON_OBJECT('Profil',NULL,'Arka',:Goruntu) END) WHERE `KullaniciRef`=:Ref LIMIT 1;", array("Goruntu" => ($path . dirSep . $name . $extension), "Ref" => $ref));

				/**
				 * Kayıt edildiyse
				 */
				if ($r > 0) {
					/**
					 * Sondaki / sil
					 */
					$y = ((substr(rootDir, strlen(rootDir) - 1, 1) === dirSep) ? substr(rootDir, 0, -1) : rootDir);

					/**
					 * Görüntü bilgilerini Json'dan çevir
					 */
					$z = @json_decode(_isset($_SESSION, "Goruntu"), true);

					/**
					 * Profil arka plan bilgilerini kontrol et
					 */
					if (!is_null(_isset($z, "Arka"))) {
						/**
						 * Path bilgisi al
						 */
						$z = pathinfo($y . $z["Arka"]);

						/**
						 * Kullanıcıya ait önceki tüm fotoğrafları sil
						 */
						delTree($z["dirname"]);
					}

					/**
					 * Dosyayı istenen yere taşı
					 */
					$upload = PhpProje\Library\SimplePhpUpload\Upload::factory($path, $y);
					$upload->file($f);
					$upload->set_filename($name . $extension);
					$upload->set_allowed_mime_types(array("image/png", "image/jpeg", "image/webp", "image/gif"));
					$y = $upload->upload();

					/**
					 * Upload sorunsuz ise
					 */
					if ((_isset($y, "status") === true) && (is_array(_isset($y, "errors")))) {
						/**
						 * Hata sıfırdan çoksa
						 */
						if (count($y["errors"]) > 0) {
							/**
							 * Devam etme
							 */
							$c = false;

							/**
							 * Hataları birleştir
							 */
							$x["errors"] = array_merge($x["error"], $y["errors"]);
						} else {
							/**
							 * Küçültme işlemini yap
							 */
							$imageLib = new PhpProje\Library\PhpImageMagician\imageLib($y["full_path"]);
							$imageLib->resizeImage(1920, 600, array('crop', 'm'));
							$z = $imageLib->saveImage($y["full_path"], 100);

							/**
							 * İşlem sorunsuz ise
							 */
							if ($z === true) {
								/**
								 * Gönderim sistemi json olarak yapılıyorsa
								 */
								if (_post("Tur") === "json") {
									header('Content-Type: application/json');
									echo json_encode(
										array(
											"status" => true,
											"redirect" => $_SERVER["REQUEST_URI"],
											"errorCount" => count($x["errors"]),
											"errors" => $x["errors"]
										)
									);

									exit;
								}
							} else {
								/**
								 * Devam etme
								 */
								$c = false;
							}
						}
					}
				}
			} else {
				/**
				 * Hata bilgileri
				 */
				$phpFileUploadErrors = array(
					0 => 'There is no error, the file uploaded with success',
					1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
					2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
					3 => 'The uploaded file was only partially uploaded',
					4 => 'No file was uploaded',
					6 => 'Missing a temporary folder',
					7 => 'Failed to write file to disk.',
					8 => 'A PHP extension stopped the file upload.',
				);

				/**
				 * Hata mesajı tanımla
				 */
				$y = _isset($phpFileUploadErrors, $z);

				/**
				 * Hata ekle
				 */
				$x["errors"][] = "Hata oluştu. Hata: " . is_null($y) ? "Bilinmeyen hata" :  $y;
			}
		}

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

		$this->app->viewArr = $x;
	}

	
}


/**
 * 
 * Tolga AKARDENİZ
 * 20.01.2021 02:53
 * 
 * @param string $x = Folder
 * 
 */
function delTree($x)
{
	$r = array();

	if (!is_dir($x)) {
		return false;
	}

	if (substr($x, strlen($x) - 1, 1) != dirSep) {
		$x .= dirSep;
	}

	$files = glob($x . '*', GLOB_MARK);

	foreach ($files as $file) {
		if (is_dir($file)) {
			$r[] = array($file, delTree($file));
		} else {
			$r[] = array($file, @unlink($file));
		}
	}

	return array(array($x, @rmdir($x)), $r);
}
