<?php

/**
 * بِسْــــــــــــــــــمِ اﷲِالرَّحْمَنِ اارَّحِيم
 * 
 * Eûzubillâhimineşşeytânirracîym - Bismillâhirrahmânirrahîm
 * 
 * Rahman ve Rahim olan "Allah" 'ın adıyla
 * 
 */

class kayitView extends Views
{
	public function __construct(App $app)
	{
		parent::__construct($app);

		$this->index();
	}

	public function index()
	{
		/**
		 * Sayfa başlığı
		 * Page title
		 */
		$this->title = "Medical";

		/**
		 * Sayfa açıklaması
		 * Page description
		 */
		$this->description = "Deneme";

		/**
		 * Sayfa anahtar kelimeleri
		 * Page keywords
		 */
		$this->keywords = "Deneme";

		/**
		 * Sayfa stil dosyaları
		 * Page css files
		 */
		$this->cssFiles = [/* 
			["href" => "ffff.css"]
		 */];

		/**
		 * Sayfa program betiği dosyaları
		 * Page java script files
		 */
		$this->jsFiles = [/* 
			["src" => "ffff.js"]
		 */];

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
		$this->render();
	}

	public function render()
	{
		/**
		 * Ana tema html dosyası
		 * Main theme html file
		 */
		App::$c->set("indexTemp", App::$twig->load("index.html"));

		$e = array();

		$e["title"] = $this->title;
		$e["description"] = $this->description;
		$e["keywords"] = $this->keywords;

		$e["styles"] = $this->cssFiles;
		$e["scripts"] = $this->jsFiles;

		$e["viewHtml"] = $this->output;

		/**
		 * Üst bilgileri gönder
		 * Send headers 
		 */
		$this->sendHeader();

		/**
		 * Ekrana gönder
		 * Send to screen
		 */
		echo App::$compress->compress(App::$c->get("indexTemp")->render($e));
	}
}