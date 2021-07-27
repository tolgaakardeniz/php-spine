<?php

/**
 * بِسْــــــــــــــــــمِ اﷲِالرَّحْمَنِ اارَّحِيم
 * 
 * Eûzubillâhimineşşeytânirracîym - Bismillâhirrahmânirrahîm
 * 
 * Rahman ve Rahim olan "Allah" 'ın adıyla
 * 
 */

class girisView extends Views
{
	public function __construct(App $app)
	{
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

		if (isset($this->app->viewArr)) {
			$e = array_merge($e, $this->app->viewArr);
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
		 * Ana tema html dosyası
		 * Main theme html file
		 */
		App::$c->set("userTemp", App::$twig->load("user.html"));

		$e = array();

		$e["title"] = $app->title;
		$e["description"] = $app->description;
		$e["keywords"] = $app->keywords;

		$e["styles"] = $app->cssFiles;
		$e["scripts"] = $app->jsFiles;

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
		echo App::$compress->compress(App::$c->get("userTemp")->render($e));
	}
}