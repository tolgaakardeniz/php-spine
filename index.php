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

function microtime_()
{
	list($usec, $sec) = explode(" ", microtime());
	return (int)($sec . substr($usec, 2, 4));
}

$startMicroTime = microtime_();

/**
 * 
 * Başlama ile ilgili bilgiler
 * 
 * Tarih: 20.01.2021
 * 
 * Projeyi başlatan: Tolga AKARDENİZ
 * 
 */

/**
 * Windows ve Unix sistemler için klasör speratörü
 */
if (!defined("dirSep")) {
	define("dirSep", DIRECTORY_SEPARATOR);
}
/**
 * Projenin çalışma klasörü
 * */
if (!defined("rootDir")) {
	define("rootDir", __DIR__ . dirSep);
}

/**
 * Ayarları yükle
 */
require_once(rootDir . "Config.php");

/**
 * App sınıfını yükle
 */
require_once(appDir . "App.php");

/**
 * App sınıfını sisteme tanımla
 */

$c = App::options(
	dbHostNameOrIp,
	dbPort,
	dbUserName,
	dbPassword,
	dbName,
	dbType
);

/**
 * Sezon adını sisteme tanımla
 */
$c->setSessionName(sessionName);


/**
 * Sıkştırma sınıfını yükle
 * Upload Compression Class
 */

App::$compress = new \PhpProje\Library\OutputHtmlCompress();


/**
 * Hata sınıfını yükle
 */
require_once(appDir . "Errors.php");

/**
 * twig kullanımı
 */
require_once(rootDir . "vendor" . dirSep . "autoload.php");

/* $loader = new \Twig\Loader\ArrayLoader([
    "index" => "Hello {{ name }}!",
]); */

/**
 * twig object set
 */
App::$twigLoader = $loader = new \Twig\Loader\FilesystemLoader(templateDir);
App::$twig = $twig = new \Twig\Environment(App::$twigLoader, [
	/* "cache" => twigDir, */
	"debug" => true
	/* "auto_reload" => false */
]);

//App::$controls = new \PhpProje\Library\Controls();
//var_dump($controls->validateTaxNumber("7230005479"));

/**
 * new App class set
 */
$app = new App();

/**
 * Load url
 */
if ($app->parseURL() === false) {
	/**
	 * Notfound
	 */
	throw new CustomError(CustomError::message("Not Found", "Your url was not found. Please check address in address bar.", "/", "20000000"));
}

/**
 * Check if loaded view class
 */
if (!is_object($app->view)) {
	/**
	 * Notfound
	 */
	throw new CustomError(CustomError::message("Class Error", "\"View\" class file is not loaded. Please contact the site administrator.", "/", "20"));
}

//array("title" => "Class Error", "message" => "View is not loaded", "redirect" => "/", "second" => "20")


$microtime = microtime_() - $startMicroTime;
echo <<<EOF
<!-- All transactions took $microtime microseconds to execute. -->
EOF;