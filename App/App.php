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

/**
 * Sınıfları otomatik yükleyiciyi
 * */
require_once(rootDir . "Bootstrap.php");

/**
 * Yükleyiciyi başlat
 */
Bootstrap::init(projectName, rootDir);


/**
 * Controllers Calss
 */

class Controllers
{
	public $app;
	public $name;

	public function __construct(App $app)
	{
		/**
		 * set App class in controllers
		 */
		$this->app = $app;

		/**
		 * set controller in app class to controller variable
		 */
		$app->controller = $this;


		/**
		 * set class name to name variable
		 */
		$this->name = preg_replace("/Controller/", "", get_class($this));

		/**
		 * check model for url
		 */
		$this->checkModel();

		/**
		 * set view for url
		 */
		$this->setView();
	}

	/**
	 * model process
	 */
	public function checkModel()
	{
		if (count($_POST) > 0) {

			$u = isset($this->app->u) ? (is_array($this->app->u) ? $this->app->u : array()) : array();
			$f = null;

			if (count($u) < 1) {
				$f = modelsDir . $this->name . ".php";
			} else {
				if (is_null($this->app->customFile)) {
					$f = modelsDir . $u[0] . dirSep . $u[1] . ".php";
				} else {
					$f = modelsDir . $this->app->customFile . ".php";
				}
			}

			if (is_file($f)) {
				require_once($f);
				$class = $this->name . "Model";
				$this->model = new $class($this->app);

				$process = isset($_POST["Islem"]) ? $_POST["Islem"] : null;

				if (method_exists($this->model, $process)) {
					$this->model->{$process}();
				} else {
					throw new CustomError(CustomError::message("Method error", "Your method not found. Please cotact your adminstrator.", "/", 20));
				}
			} else {
				throw new CustomError(CustomError::message("Model load error", "Model class file is not found: " . $f, "/", 20));
			}
		}

		return true;
	}

	/**
	 * view process
	 */
	public function setView()
	{

		$u = isset($this->app->u) ? (is_array($this->app->u) ? $this->app->u : array()) : array();
		$f = null;

		if (count($u) < 1) {
			$f = viewsDir . $this->name . ".php";
		} else {
			if (is_null($this->app->customFile)) {
				$f = viewsDir . $u[0] . dirSep . $u[1] . ".php";
			} else {
				$f = viewsDir . $this->app->customFile . ".php";
			}
		}

		try {
			if (is_file($f)) {
				require_once($f);
				$class = $this->name . "View";
				$this->app->view = new $class($this->app);
			} else {
				throw new CustomError("View file is not found. Please contact your administrator. File: " . $f);
			}
		} catch (Throwable $e) {
			throw new CustomError(CustomError::message("View load error", $e->getMessage(), "/", 60));
		}
	}
}



/**
 * Models Calss
 */


class Models
{
	public $app;

	public function __construct(App $app)
	{
		/**
		 * set App class in controllers
		 */
		$this->app = $app;

		/**
		 * set model in app class to model variable
		 */
		$app->model = $this;
	}
}


/**
 * Views Calss
 */

class Views
{


	public $output;
	public $params;

	public $app;

	public function __construct(App $app)
	{
		/**
		 * set App class in controllers
		 */
		$this->app = $app;

		/**
		 * set view in app class to view variable
		 */
		$app->view = $this;
	}

	/**
	 * Add template file to "viewTemp" variable in App class
	 * 
	 * @author Tolga AKARDENİZ
	 * 
	 * @return none
	 * 
	 */
	public function loadHtml()
	{
		$u = isset($this->app->u) ? (is_array($this->app->u) ? $this->app->u : array()) : array();
		$f = null;


		if (count($u) < 1) {
			$f = "views" . dirSep . $this->app->controller->name . ".html";
		} else {
			if (is_null($this->app->customFile)) {
				$f = "views" . dirSep . $u[0] . dirSep . $u[1] . ".html";
			} else {
				$f = "views" . dirSep . $this->app->customFile . ".html";
			}
		}

		try {
			App::$c->set("viewTemp", App::$twig->load($f));
		} catch (Throwable $th) {
			throw new CustomError(CustomError::message("View load error", "View template file is not found: " . $f . " th:" . var_export($th, true), "/", 20));
		}
	}


	/**
	 * 
	 * Send headers to user
	 * 
	 * @author Tolga AKARDENİZ
	 * 
	 * @return true
	 * 
	 */
	public function sendHeader(): bool
	{
		header("Pragma: no-cache");
		header("Cache-Control: max-age=0, no-cache, must-revalidate");
		header("Content-Type: text/html; charset=utf-8");

		return true;
	}
}


/**
 * App Class
 */
class App
{
	public static $c;
	public static $session;
	public static $controls;
	public static $twigLoader;
	public static $twig;
	public static $compress;
	public static $headers = array();

	private $db;
	public $controller;
	public $model;
	public $view;

	public $title;
	public $jsFiles;
	public $cssFiles;
	public $description;
	public $keywords;
	public $customFile;

	private $parseURL = false;

	public function __construct()
	{
	}

	public static function options(
		string $Name = null,
		int $Port = null,
		string $User = null,
		string $Password = null,
		string $Database = null,
		string $PdoType = null

	) {
		$c = new \PhpProje\SystemConfig();

		$c->setName($Name);
		$c->setPort($Port);
		$c->setUser($User);
		$c->setPassword($Password);
		$c->setDatabase($Database);
		$c->setPdoType($PdoType);

		self::$c = $c;

		return $c;
	}

	/**
	 * Check user
	 */
	public static function isUser(): bool
	{
		return isset($_SESSION["Ref"]) ? true : false;
	}

	/**
	 * Check user
	 */
	public static function isThisUser($x): bool
	{
		return (_isset($_SESSION, "Adi") === $x) ? true : false;
	}

	/**
	 * Check user is admin
	 */
	public static function isAdmin(): bool
	{
		return isset($_SESSION["Ref"]) ? true : false;
	}
	/* 
	public function setController (Controller $controller)
	{
		$this->controller = $controller;
	} */

	public function parseURL()
	{
		if (!$this->parseURL) {
			$this->parseURL = true;
		} else {
			return false;
		}

		$u = explode("/", ltrim($_SERVER["REQUEST_URI"], "/"));

		if ((strlen($u[0]) === 0) || (is_numeric(strpos($u[0], "?")))) {

			$this->u = null;

			/**
			 * Index girişi
			 */
			$f = controllersDir . "index.php";

			if (is_file($f)) {
				require_once($f);
				$this->controller = new indexController($this);
			} else {
				throw new Exception("Controller not found: " . $f);
			}
		} else {
			/**
			 * Get kontrolü ve varsa Get'i temizle
			 */
			if ((strlen($u[count($u) - 1]) < 1) || (substr($u[count($u) - 1], 0, 1) === "?")) {
				unset($u[count($u) - 1]);
			}

			/**
			 * Veritabanına bağlan
			 */
			App::$c->connectSqlServer();

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
			 * Url bilgisini tanımla
			 */
			$this->u = $u;

			if (!isset($u[1])) {
				$f = controllersDir . $u[0] . ".php";

				if (is_file($f)) {
					require_once($f);
					$class = $u[0] . "Controller";
					$this->controller = new $class($this);
				} else {
					$x = (@count($_POST) > 0) ? 1 : 0;

					/** 
					 * Kullanıcı profil giriş sayısını artır
					 * */
					$r = $this->db->query("CALL `KullaniciProfiliGetirProseduru`(:Adi, :Ref, :Sayac);", array("Adi" => array($u[0]), "Ref" => _isset($_SESSION,"Ref"), "Sayac" => $x));

					/**
					 * Kullanıcı bulunduysa
					 */
					if (count($r) > 0) {
						/**
						 * Kullanıcı bilgilerini tanımla
						 */
						App::$c->set("user", $r[0]);

						/**
						 * Profil controller dosyasının adı
						 */
						$f = controllersDir . "kullanici" . dirSep . "profil.php";

						/**
						 * Dosya varsa
						 */
						if (is_file($f)) {
							require_once($f);
							$class = "profilController";
							$this->controller = new $class($this);
						} else {
							return false;
						}
					} else {
						/**
						 * Kullanıcı bulunamadıysa
						 */
						App::$c->unset("user");
						return false;
					}
				}
			} else {
				/**
				 * 3. blok url varsa 2. ye gönder çünkü 3. blok kullanmıyoruz
				 */
				if (isset($u[2])) {
					/* header("Location: /".$u[0]."/".$u[1]);
					exit; */

					return false;
				} else {

					$f = controllersDir . $u[0] . dirSep . $u[1] . ".php";

					if (is_file($f)) {

						require_once($f);
						$class = $u[1] . "Controller";
						$this->controller = new $class($this);
					} else {
						return false;
					}
				}
			}
		}
	}
}

/**
 * Kontrol fonksiyonları
 * Control functions
 */

/**
 * 
 * Check variable in post
 * 
 * Tolga AKARDENİZ
 * 31.03.2021 06:35
 * 
 * @param string $x
 */
function _post($x)
{
	return isset($_POST[$x]) ? trim($_POST[$x]) : null;
}

/**
 * 
 * Check variable in get
 *  
 * Tolga AKARDENİZ
 * 31.03.2021 06:35
 * 
 * @param string $x
 */
function _get($x)
{
	return isset($_GET[$x]) ? trim($_GET[$x]) : null;
}

/**
 * 
 * Check variable in request
 * 
 * Tolga AKARDENİZ
 * 31.03.2021 06:35
 * 
 * @param string $x
 */
function _request($x)
{
	return isset($_REQUEST[$x]) ? trim($_REQUEST[$x]) : null;
}

/**
 * 
 * Check variable name in array or object
 * 
 * Tolga AKARDENİZ
 * 02.10.2020 06:35
 * 
 * @param array $x
 * @param string $y
 * 
 */
function _isset($x = array(), $y = null)
{
	return isset($x[$y]) ? $x[$y] : null;
}

/**
 * 
 * Crop variable if large 
 * 
 * Tolga AKARDENİZ
 * 19.05.2021 21:52
 * 
 * @param string $x
 * @param int $y
 * 
 */
function _crop($x, $y = 1)
{
	return (strlen($x) > $y) ? substr($x, 0, $y) . "..." : $x;
}

/**
 * 
 * Add log to database
 * 
 * Tolga AKARDENİZ
 * 31.03.2021 06:35
 * 
 * @param object $db
 * @param string $ekran
 * @param string $islem
 * @param string $aciklama
 * 
 * @return int
 */
function logEkle($db, $ekran, $islem, $aciklama = NULL)
{
	if (isset($_SESSION["Ref"])) {
		$x = !is_numeric($_SESSION["Ref"]) ? 1 : $_SESSION["Ref"];
	} else {
		$x = 1;
	}

	$a = array($ekran, $islem, $aciklama, $x, App::$c->getIp());
	$q = $db->query("INSERT INTO `Loglar` (`Ekran`, `Islem`, `Aciklama`, `OlusturanRef`, `Ip`) VALUES (?, ?, ?, ?, ?);", $a);

	return $q;
}



/**
 * 
 * Send status code to header
 * 
 * Tolga AKARDENİZ
 * 21.05.2021 00:58
 * 
 * @param int $c
 * 
 * @return string
 */
if (!function_exists('httpResponseCode')) {
	function httpResponseCode($c = NULL)
	{
		if ($c !== NULL) {
			switch ($c) {
				case 100:
					$x = 'Continue';
					break;
				case 101:
					$x = 'Switching Protocols';
					break;
				case 200:
					$x = 'OK';
					break;
				case 201:
					$x = 'Created';
					break;
				case 202:
					$x = 'Accepted';
					break;
				case 203:
					$x = 'Non-Authoritative Information';
					break;
				case 204:
					$x = 'No Content';
					break;
				case 205:
					$x = 'Reset Content';
					break;
				case 206:
					$x = 'Partial Content';
					break;
				case 300:
					$x = 'Multiple Choices';
					break;
				case 301:
					$x = 'Moved Permanently';
					break;
				case 302:
					$x = 'Moved Temporarily';
					break;
				case 303:
					$x = 'See Other';
					break;
				case 304:
					$x = 'Not Modified';
					break;
				case 305:
					$x = 'Use Proxy';
					break;
				case 400:
					$x = 'Bad Request';
					break;
				case 401:
					$x = 'Unauthorized';
					break;
				case 402:
					$x = 'Payment Required';
					break;
				case 403:
					$x = 'Forbidden';
					break;
				case 404:
					$x = 'Not Found';
					break;
				case 405:
					$x = 'Method Not Allowed';
					break;
				case 406:
					$x = 'Not Acceptable';
					break;
				case 407:
					$x = 'Proxy Authentication Required';
					break;
				case 408:
					$x = 'Request Time-out';
					break;
				case 409:
					$x = 'Conflict';
					break;
				case 410:
					$x = 'Gone';
					break;
				case 411:
					$x = 'Length Required';
					break;
				case 412:
					$x = 'Precondition Failed';
					break;
				case 413:
					$x = 'Request Entity Too Large';
					break;
				case 414:
					$x = 'Request-URI Too Large';
					break;
				case 415:
					$x = 'Unsupported Media Type';
					break;
				case 500:
					$x = 'Internal Server Error';
					break;
				case 501:
					$x = 'Not Implemented';
					break;
				case 502:
					$x = 'Bad Gateway';
					break;
				case 503:
					$x = 'Service Unavailable';
					break;
				case 504:
					$x = 'Gateway Time-out';
					break;
				case 505:
					$x = 'HTTP Version not supported';
					break;
				default:
					exit('Unknown http status code "' . htmlspecialchars($c) . '"');
					break;
			}

			$p = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

			header($p . ' ' . $c . ' ' . $x);
		} else {
			$c = 200;
		}

		return $c;
	}
}
