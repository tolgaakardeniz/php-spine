<?php

/**
 * بِسْــــــــــــــــــمِ اﷲِالرَّحْمَنِ اارَّحِيم
 * 
 * Eûzubillâhimineşşeytânirracîym - Bismillâhirrahmânirrahîm
 * 
 * Rahman ve Rahim olan "Allah" 'ın adıyla
 * 
 */

class ayarlarView extends Views
{
	public $output;
	public $userImageInfo;

	public function __construct(App $app)
	{
		/**
		 * Kullanıcı görüntü bilgileri
		 */
		$this->userImageInfo = array();

		parent::__construct($app);

		$this->index($app);
	}

	public function index(App $app)
	{
		/**
		 * Load template file
		 */
		$this->loadHtml();

		/**
		 * Kullanıcı bilgilerini al
		 */
		$user = App::$c->get("user");

		/**
		 * Görüntüleme süreci oluştur 
		 * Create process for view
		 */
		$e = array(
			"logOutViews" => (is_numeric(_isset($user, "Cikis")) ? number_format($user["Cikis"], 0, ",", ".") : 0),
			"logInViews" => (is_numeric(_isset($user, "Giris")) ? number_format($user["Giris"], 0, ",", ".") : 0),
			"profileViews" => (is_numeric(_isset($user, "Profil")) ? number_format($user["Profil"], 0, ",", ".") : 0),
			"dateOfBirth" => date("d/m/Y", strtotime(_isset($user, "Dogum"))),
			"logOutDate" => date("d/m/Y H:i:s", strtotime(_isset($user, "CikisTarihi"))),
			"lastActivite" => date("d/m/Y H:i:s", strtotime(_isset($user, "SonAktivite"))),
			"logInDate" => date("d/m/Y H:i:s", strtotime(_isset($user, "OturumTarihi"))),
			"createdDate" => date("d/m/Y", strtotime(_isset($user, "OlusturmaTarihi"))),
			"userInfo" => _isset($user, "Bilgi"),
			"userName" => _isset($user, "Adi"),
			"name" => _crop(_isset($user, "Kunye"), 21),
			"fullName" => htmlspecialchars(_isset($user, "Kunye")),
			"isUser" => App::isThisUser($app->u[0])
		);

		/**
		 * Görüntü bilgilerini Json'dan çevir
		 */
		$x = @json_decode($user["Goruntu"], true);

		/**
		 * Profil fotoğrafı bilgilerini kontrol et
		 */
		if (is_null(_isset($x, "Profil"))) {
			$e["profileImage"] = defaultBackPhotoUrl;
		} else {
			$e["profileImage"] = $x["Profil"];
		}

		/**
		 * Arka plan fatoğrafı bilgilerini kontrol et
		 */
		if (is_null(_isset($x, "Arka"))) {
			$e["backImage"] = profilePhotoUrl;
		} else {
			$e["backImage"] = $x["Arka"];
		}

		/**
		 * Çıktıyı oluştur
		 */
		$this->output = App::$c->get("viewTemp")->render($e);

		/**
		 * Ekrana gönder
		 * Send to screen
		 */
		$this->render($app);
	}

	public function render(App $app)
	{
		/**
		 * Kullanıcı bilgilerini al
		 */
		$user = App::$c->get("user");

		/**
		 * Görüntü bilgilerini Json'dan çevir
		 */
		$x = @json_decode(_isset($_SESSION, "Goruntu"), true);

		/**
		 * Profil fotoğrafı bilgilerini kontrol et
		 */
		if (is_null(_isset($x, "Profil"))) {
			$this->userImageInfo["profileImage"] = defaultBackPhotoUrl;
		} else {
			$this->userImageInfo["profileImage"] = $x["Profil"];
		}

		/**
		 * Arka plan fatoğrafı bilgilerini kontrol et
		 */
		if (is_null(_isset($x, "Arka"))) {
			$this->userImageInfo["backImage"] = profilePhotoUrl;
		} else {
			$this->userImageInfo["backImage"] = $x["Arka"];
		}

		/**
		 * Ana tema html dosyası
		 * Main theme html file
		 */
		App::$c->set("indexTemp", App::$twig->load("index.html"));

		$e = array();

		$e["title"] = $app->title;
		$e["description"] = $app->description;
		$e["keywords"] = $app->keywords;

		$e["styles"] = $app->cssFiles;
		$e["scripts"] = $app->jsFiles;

		$e["viewHtml"] = $this->output;// . json_encode(array($_SESSION, App::$c->getConncetion()));

		/**
		 * Kullanıcı bilgilerini tanımla
		 */
		if (App::isUser()) {
			/**
			 * Kullanıcı bilgilerini tanımla
			 */
			$e = array_merge($e, array("userName" => _isset($_SESSION, "Adi"), "name" => _crop(_isset($_SESSION, "Kunye"), 21), "fullName" => htmlspecialchars(_isset($_SESSION, "Kunye"))), $this->userImageInfo);
		} else {
			/**
			 * Misafir kullanıcı bilgilerini tanımla
			 */
			$e = array_merge($e, array("userName" => "misafir", "name" => "Misafir Kullanıcı", "fullName" => "Misafir Kullanıcı"), $this->userImageInfo);
		}

		/**
		 * Hangi html dosyasını yükleyeceğini kullanıcı veya değil diye ayırt etmesi için bu bilgiyi tanımla
		 */
		$e["isUser"] = App::isUser();

		/**
		 * Üst bilgileri gönder
		 * Send headers 
		 */
		App::$c->set("sendedHeaders",$this->sendHeader());

		/**
		 * Ekrana gönder
		 * Send to screen
		 */
		echo App::$compress->compress(App::$c->get("indexTemp")->render($e));
	}
}