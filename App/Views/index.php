<?php

/**
 * بِسْــــــــــــــــــمِ اﷲِالرَّحْمَنِ اارَّحِيم
 * 
 * Eûzubillâhimineşşeytânirracîym - Bismillâhirrahmânirrahîm
 * 
 * Rahman ve Rahim olan "Allah" 'ın adıyla
 * 
 */

class indexView extends Views
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
		 * Görüntüleme süreci oluştur 
		 * Create process for view
		 */
		$e = array();

		$e["navigation"] = [["href" => "1", "caption" => "deneme"], ["href" => "2", "caption" => "lol"]];
		$e["a_variable"] = "dasdasdsa";

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
			$this->userImageInfo["profileImage"] = backPhotoUrl;
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

		$e["viewHtml"] = $this->output . json_encode(array($_SESSION, App::$c->getConncetion()));

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