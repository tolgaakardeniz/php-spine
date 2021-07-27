<?php

/**
 * بِسْــــــــــــــــــمِ اﷲِالرَّحْمَنِ اارَّحِيم
 * 
 * Eûzubillâhimineşşeytânirracîym - Bismillâhirrahmânirrahîm
 * 
 * Rahman ve Rahim olan "Allah" 'ın adıyla
 * 
 */

/**
 * Projenin adı
 * */
if (!defined("projectName")) {
	define("projectName", "PhpProje");
}

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
 * Projenin geçici dosyalarının saklanacağı klasör
 */
if (!defined("tempDir")) {
	define("tempDir", rootDir . "tmp" . dirSep);
}

/**
 * Projenin sezon bilgilerinin saklanacağı klasör
 */
if (!defined("sessionDir")) {
	define("sessionDir", tempDir . "session" . dirSep);
}

/**
 * Projenin sistem hatalarının saklanacağı klasör
 */
if (!defined("logDir")) {
	define("logDir", tempDir . "errors" . dirSep);
}

/**
 * Projenin profil fotoğraflarının saklanacağı klasör
 */
if (!defined("profilePhotoDir")) {
	define("profilePhotoDir", tempDir . "profile" . dirSep);
}

/**
 * Projenin varsayılan profil fotoğrafı
 */
if (!defined("profilePhotoUrl")) {
	define("profilePhotoUrl", "/files/jpg/backImage.jpg");
}

/**
 * Projenin arka plan fotoğraflarının saklanacağı klasör
 */
if (!defined("backPhotoDir")) {
	define("backPhotoDir", tempDir . "back" . dirSep);
}

/**
 * Projenin varsayılan arka plan fotoğrafı
 */
if (!defined("backPhotoUrl")) {
	define("backPhotoUrl", "/files/png/profile.png");
}

/**
 * Projenin App klasörü
 * */
if (!defined("appDir")) {
	define("appDir", rootDir . "App" . dirSep);
}

/**
 * Projenin App klasörü
 * */
if (!defined("controllersDir")) {
	define("controllersDir", appDir . "Controllers" . dirSep);
}

/**
 * Projenin Model klasörü
 * */
if (!defined("modelsDir")) {
	define("modelsDir", appDir . "Models" . dirSep);
}

/**
 * Projenin View klasörü
 * */
if (!defined("viewsDir")) {
	define("viewsDir", appDir . "Views" . dirSep);
}

/**
 * Projenin Controller klasörü
 * */
if (!defined("controllersDir")) {
	define("controllersDir", appDir . "Controller" . dirSep);
}

/**
 * Tema dosyalarının saklanacağı klasör
 */
if (!defined("templateRootUrl")) {
	define("templateRootUrl", "/templates" . dirSep);
}

/**
 * Tema dosyalarının saklanacağı klasör
 */
if (!defined("templateUrl")) {
	define("templateUrl", "/templates" . dirSep .  "medical" . dirSep);
}

/**
 * Tema dosyalarının saklanacağı klasör
 */
if (!defined("templateDir")) {
	define("templateDir", rootDir . "templates" . dirSep .  "medical" . dirSep);
}

/**
 * twig geçici dosyalarının saklanacağı klasör
 */
if (!defined("twigDir")) {
	define("twigDir", tempDir . "twigcache" . dirSep);
}



/**
 * Projenin sezon bilgilerinin saklanacağı klasör
 */
if (!defined("sessionName")) {
	define("sessionName", projectName);
}
/**
 * Projede hatalar gösterilsin mı?
 * */
if (!defined("showErrors")) {
	define("showErrors", true);
}

/**
 * Projede hatalar dosyalansın mı?
 * */
if (!defined("logErrors")) {
	define("logErrors", true);
}

/**
 * Sitenin adı
 * */
if (!defined("siteName")) {
	define("siteName", "Doktorun Sitesi");
}




/**
 * SQL ile ilgili tanımlamalar
 */

if (!defined("dbType")) {
	define("dbType", "MYSQL");
}
if (!defined("dbHostNameOrIp")) {
	define("dbHostNameOrIp", "127.0.0.1");
}
if (!defined("dbUserName")) {
	define("dbUserName", "medical");
}
if (!defined("dbPassword")) {
	define("dbPassword", "@Parola571*");
}
if (!defined("dbName")) {
	define("dbName", "medical");
}
if (!defined("dbPort")) {
	define("dbPort", 3306);
}
