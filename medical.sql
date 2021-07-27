-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Anamakine: phpmyadmin.ev
-- Üretim Zamanı: 07 May 2021, 00:22:04
-- Sunucu sürümü: 8.0.23-0ubuntu0.20.10.1
-- PHP Sürümü: 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `medical`
--
CREATE DATABASE IF NOT EXISTS `medical` DEFAULT CHARACTER SET utf8 COLLATE utf8_turkish_ci;
USE `medical`;

DELIMITER $$
--
-- Yordamlar
--
DROP PROCEDURE IF EXISTS `EtkinlikEkleProseduru`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `EtkinlikEkleProseduru` (IN `GelenAdi` CHAR(255), IN `GelenIslem` CHAR(255), IN `GelenTarayiciBilgisi` TEXT, IN `GelenHamVeri` TEXT, IN `GelenIp` CHAR(128))  BEGIN
	DECLARE RefDegeri BIGINT(20) UNSIGNED DEFAULT NULL;
	DECLARE KullaniciRefDegeri BIGINT(20) UNSIGNED DEFAULT NULL;
	DECLARE Md5RefDegeri BIGINT(20) UNSIGNED DEFAULT NULL;
	DECLARE Islem TINYINT UNSIGNED DEFAULT NULL;
	DECLARE Mesaj VARCHAR(2048) DEFAULT NULL;
    
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		ROLLBACK;

		IF (@Mesaj IS NULL) THEN
			SET @Mesaj = 'İşlem başarısız oldu. Lütfen tekrar deneyiniz.';
		END IF;

        SET @Islem = FALSE;
        SET @RefDegeri = NULL;

        SELECT @RefDegeri AS `Ref`, @Islem AS `Islem`, @Mesaj AS `Mesaj`;
	END;

	START TRANSACTION;

        SET @RefDegeri = NULL;
        SET @KullaniciRefDegeri = NULL;
        SET @Md5RefDegeri = NULL;
        SET @Islem = TRUE;

        SELECT `Ref` INTO @KullaniciRefDegeri FROM `Kullanicilar` WHERE `Adi`=GelenAdi LIMIT 1;

        IF ((NOT @KullaniciRefDegeri REGEXP '^[0-9]+$') OR (@KullaniciRefDegeri IS NULL)) THEN
			SET @Mesaj = 'Kullanıcı adı hatalı. Sistemsel bir hata var.';
			SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @Mesaj;
        ELSE
            SELECT `Ref` INTO @RefDegeri FROM `Parametreler` WHERE `Kod`='TarayiciBilgisi' LIMIT 1;

            IF ((NOT @RefDegeri REGEXP '^[0-9]+$') OR (@RefDegeri IS NULL)) THEN
                INSERT INTO `Parametreler` (`Kod`,`Aciklama`,`OlusturanRef`, `Ip`) VALUES ('TarayiciBilgisi', 'Kullanıcıların tarayıcı bilgileri MD5 olarak kayıt edilip aktiviteler tablosunda kullanılacak.', @KullaniciRefDegeri, GelenIp);

                IF ((LAST_INSERT_ID() IS NULL) OR (LAST_INSERT_ID() NOT REGEXP '^[0-9]+$')) THEN
                    SET @Mesaj = 'TarayiciBilgisi parametresi eklenemedi. Sistemsel bir hata var.';
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @Mesaj;
                END IF;

                SET @RefDegeri = LAST_INSERT_ID();
            END IF;

            SELECT Ref INTO @Md5RefDegeri FROM `Parametreler` WHERE `UstParametreRef`=@RefDegeri AND `Kod`=Md5(GelenTarayiciBilgisi) LIMIT 1;

            IF ((NOT @Md5RefDegeri REGEXP '^[0-9]+$') OR (@Md5RefDegeri IS NULL)) THEN
                INSERT INTO `Parametreler` (`UstParametreRef`,`Kod`,`MetinBir`,`OlusturanRef`, `Ip`) VALUES (@RefDegeri, Md5(GelenTarayiciBilgisi), GelenTarayiciBilgisi, @KullaniciRefDegeri, GelenIp);

                IF ((LAST_INSERT_ID() IS NULL) OR (LAST_INSERT_ID() NOT REGEXP '^[0-9]+$')) THEN
                    SET @Mesaj = 'TarayiciBilgisi Md5 parametresi eklenemedi. Sistemsel bir hata var.';
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @Mesaj;
                END IF;

                SET @Md5RefDegeri = LAST_INSERT_ID();
            END IF;

            INSERT INTO `Etkinlikler` (`Md5Ref`,`Islem`,`HamVeri`,`OlusturanRef`, `Ip`) VALUES (@Md5RefDegeri, GelenIslem, GelenHamVeri, @KullaniciRefDegeri, GelenIp);

			IF (LAST_INSERT_ID() NOT REGEXP '^[0-9]+$') OR (LAST_INSERT_ID() IS NULL) THEN
               SET @Mesaj = 'Etkinlik ekleme işlemi başarısız oldu.';
               SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @Mesaj;
            ELSE
        		SET @Mesaj = 'Etkinlik ekleme işlemi başarılı oldu.';
        	END IF;

			SET @RefDegeri = LAST_INSERT_ID();
        END IF;
        
    COMMIT;

	SELECT LAST_INSERT_ID() AS `Ref`, @Islem AS `Islem`, @Mesaj AS `Mesaj`;
END$$

DROP PROCEDURE IF EXISTS `IpEkleProseduru`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `IpEkleProseduru` (IN `GelenIp` CHAR(128), IN `GelenIsim` CHAR(255), IN `GelenSehir` CHAR(255), IN `GelenBolge` CHAR(255), IN `GelenUlke` CHAR(32), IN `GelenEnlem` CHAR(32), IN `GelenBoylam` CHAR(32), IN `GelenOrganizasyon` VARCHAR(1024), IN `GelenPostaKodu` CHAR(32), IN `GelenSaatDilimi` CHAR(255), IN `GelenOlusturanRef` BIGINT, IN `GelenOlusturanIp` CHAR(128))  BEGIN
	DECLARE RefDegeri BIGINT(20) UNSIGNED DEFAULT NULL;
	DECLARE Islem TINYINT UNSIGNED DEFAULT NULL;
	DECLARE Mesaj VARCHAR(2048) DEFAULT NULL;
    
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		ROLLBACK;

		IF (@Mesaj IS NULL) THEN
			SET @Mesaj = 'İşlem başarısız oldu. Lütfen tekrar deneyiniz.';
		END IF;

        SET @Islem = FALSE;
        SET @RefDegeri = NULL;

        SELECT @RefDegeri AS `Ref`, @Islem AS `Islem`, @Mesaj AS `Mesaj`;
	END;

	START TRANSACTION;
        SET @RefDegeri = NULL;
        SET @Islem = TRUE;
  
		IF NOT EXISTS(SELECT `Ref` FROM `Ip` WHERE `Ip`=GelenIp LIMIT 1) THEN
        	INSERT INTO `Ip` (`Ip`, `Isim` ,`Sehir` ,`Bolge` ,`Ulke` ,`Enlem` ,`Boylam` ,`Organizasyon` ,`PostaKodu` ,`SaatDilimi` ,`Guncelleme` ,`OlusturanRef` ,`OlusturanIp`) VALUES (GelenIp, GelenIsim, GelenSehir, GelenBolge, GelenUlke, GelenEnlem, GelenBoylam, GelenOrganizasyon, GelenPostaKodu, GelenSaatDilimi, 0, GelenOlusturanRef, GelenOlusturanIp);

			SET @RefDegeri = LAST_INSERT_ID();

        	IF ((LAST_INSERT_ID() IS NULL) OR (LAST_INSERT_ID() NOT REGEXP '^[0-9]+$')) THEN
                SET @Mesaj = 'Ip eklenemedi. Sistemsel bir hata var.';
               	SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @Mesaj;
        	END IF;
  		ELSE
      		IF EXISTS(SELECT `Ref` FROM `Ip` WHERE `Ip`=GelenIp AND ((`SonGuncellemeTarihi` IS NULL AND `OlusturmaTarihi`<(NOW() - INTERVAL 1 MONTH)) OR (`SonGuncellemeTarihi` IS NOT NULL AND `SonGuncellemeTarihi`<(NOW() - INTERVAL 1 MONTH))) LIMIT 1) THEN
      			INSERT INTO `Ip` (`Ip`, `Isim` ,`Sehir` ,`Bolge` ,`Ulke` ,`Enlem` ,`Boylam` ,`Organizasyon` ,`PostaKodu` ,`SaatDilimi` ,`Guncelleme` ,`OlusturanRef` ,`OlusturanIp`) VALUES (GelenIp, GelenIsim, GelenSehir, GelenBolge, GelenUlke, GelenEnlem, GelenBoylam, GelenOrganizasyon, GelenPostaKodu, GelenSaatDilimi, 0, GelenOlusturanRef, GelenOlusturanIp) ON DUPLICATE KEY UPDATE `Ip`=GelenIp, `Isim`=GelenIsim, `Sehir`=GelenSehir, `Bolge`=GelenBolge, `Ulke`=GelenUlke, `Enlem`=GelenEnlem, `Boylam`=GelenBoylam, `Organizasyon`=GelenOrganizasyon, `PostaKodu`=GelenPostaKodu, `SaatDilimi`=GelenSaatDilimi, `Guncelleme`=(`Guncelleme`+1), `SonGuncellemeTarihi`=NOW();
      			IF ((LAST_INSERT_ID() IS NULL) OR (LAST_INSERT_ID() NOT REGEXP '^[0-9]+$')) THEN
        			SET @Mesaj = 'Ip güncellenemedi. Sistemsel bir hata var.';
               		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @Mesaj;
      			END IF;
    		END IF;
    	END IF;
    COMMIT;

	SELECT @Islem AS `Islem`, @Mesaj AS `Mesaj`, `Ip`.* FROM `Ip` WHERE `Ip`=GelenIp OR `Ref`=LAST_INSERT_ID() LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS `KullaniciGirisiProseduru`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `KullaniciGirisiProseduru` (IN `GelenKullaniciAdi` CHAR(32), IN `GelenKontrol` CHAR(64), IN `GelenHatirla` CHAR(64), IN `GelenIp` CHAR(128))  BEGIN
	DECLARE MESSAGE_TEXT VARCHAR(1024);
	DECLARE KullaniciRefDegeri BIGINT(20) UNSIGNED DEFAULT NULL;
	DECLARE RefDegeri BIGINT(20) UNSIGNED DEFAULT NULL;

	CIKIS:BEGIN
		IF GelenKullaniciAdi IS NULL OR GelenIp IS NULL THEN
			SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Kullanıcı veya Ip adresi adı boş olamaz.';
			LEAVE CIKIS;
		END IF;
    
		SET @RefDegeri = NULL;
		SET @KullaniciRefDegeri = NULL;

		IF GelenHatirla IS NULL THEN
			SELECT Ref INTO @RefDegeri FROM `Kullanicilar` WHERE `Adi`=GelenKullaniciAdi AND `Kontrol`=GelenKontrol AND `Pasif` IS NULL LIMIT 1;

			IF @RefDegeri IS NOT NULL THEN
				DELETE FROM `Hatirla` WHERE `KullaniciRef`=@RefDegeri;
				UPDATE `KullanicilarBilgi` SET `OturumTarihi`=NOW() WHERE `KullaniciRef`=@RefDegeri LIMIT 1;
				SELECT true AS `Islem`, `K1`.*, `K2`.*, `K3`.*, ParametreKoduGetirFonksiyonu(`K1`.`KullaniciTuru`) AS `Yetkisi` FROM `Kullanicilar` AS `K1` INNER JOIN `KullanicilarGenel` AS `K2` ON (`K1`.`Ref`=@RefDegeri AND `K1`.`Pasif` IS NULL) AND `K2`.`KullaniciRef`=`K1`.`Ref` INNER JOIN `KullanicilarBilgi` AS `K3` ON `K3`.`KullaniciRef`=`K1`.`Ref` LIMIT 1;
			ELSE
				SELECT false AS `Islem`;
				LEAVE CIKIS;
			END IF;
		ELSE
			IF LENGTH(GelenHatirla) != 32 THEN
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Beni hatırla 32 karakter olmalı.';
				LEAVE CIKIS;
			END IF;
		
			SELECT KullaniciRef INTO @KullaniciRefDegeri FROM `Hatirla` WHERE `Adi`=GelenKullaniciAdi AND `HatirlaRef`=GelenHatirla LIMIT 1;

			IF @KullaniciRefDegeri IS NOT NULL AND (@KullaniciRefDegeri REGEXP '^[0-9]+$') THEN
				UPDATE `KullanicilarBilgi` SET `OturumTarihi`=NOW() WHERE `KullaniciRef`=@KullaniciRefDegeri LIMIT 1;
				SELECT true AS `Islem`, `K1`.*, `K2`.*, `K3`.*, ParametreKoduGetirFonksiyonu(`K1`.`KullaniciTuru`) AS `Yetkisi` FROM `Kullanicilar` AS `K1` INNER JOIN `KullanicilarGenel` AS `K2` ON (`K1`.`Ref`=@KullaniciRefDegeri AND `K1`.`Pasif` IS NULL) AND `K2`.`KullaniciRef`=`K1`.`Ref` INNER JOIN `KullanicilarBilgi` AS `K3` ON `K3`.`KullaniciRef`=`K1`.`Ref` LIMIT 1;
			ELSE
				SET @RefDegeri = NULL;

				SELECT Ref INTO @RefDegeri FROM `Kullanicilar` WHERE `Adi`=GelenKullaniciAdi AND `Kontrol`=GelenKontrol AND `Pasif` IS NULL LIMIT 1;

				IF @RefDegeri IS NOT NULL THEN
					#DELETE FROM `Hatirla` WHERE `Adi`=GelenKullaniciAdi LIMIT 1;
					#INSERT INTO `Hatirla` (`KullaniciRef`,`Adi`,`HatirlaRef`,`Ip`) VALUES (@RefDegeri, GelenKullaniciAdi, GelenHatirla, GelenIp);
					INSERT INTO `Hatirla` (`KullaniciRef`,`Adi`,`HatirlaRef`,`Ip`) VALUES (@RefDegeri, GelenKullaniciAdi, GelenHatirla, GelenIp) ON DUPLICATE KEY UPDATE `KullaniciRef`=@RefDegeri, `Adi`=GelenKullaniciAdi, `HatirlaRef`=GelenHatirla, `Ip`=GelenIp;
					UPDATE `KullanicilarBilgi` SET `OturumTarihi`=NOW() WHERE `KullaniciRef`=@RefDegeri LIMIT 1;
					SELECT true AS `Islem`, `K1`.*, `K2`.*, `K3`.*, ParametreKoduGetirFonksiyonu(`K1`.`KullaniciTuru`) AS `Yetkisi` FROM `Kullanicilar` AS `K1` INNER JOIN `KullanicilarGenel` AS `K2` ON (`K1`.`Ref`=@RefDegeri AND `K1`.`Pasif` IS NULL) AND `K2`.`KullaniciRef`=`K1`.`Ref` INNER JOIN `KullanicilarBilgi` AS `K3` ON `K3`.`KullaniciRef`=`K1`.`Ref` LIMIT 1;
				ELSE
					SELECT false AS `Islem`;
					LEAVE CIKIS;
				END IF;
			END IF;
		END IF;
	END;
END$$

DROP PROCEDURE IF EXISTS `KullaniciProfiliGetirProseduru`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `KullaniciProfiliGetirProseduru` (IN `GelenAdi` CHAR(32), IN `GelenRef` BIGINT, IN `Sayac` TINYINT)  BEGIN
    IF (Sayac<1) THEN
		#IF ((GelenRef IS NOT NULL) OR (GelenRef REGEXP '^[0-9]+$')) THEN
			#UPDATE `KullanicilarBilgi` AS `K2` INNER JOIN `Kullanicilar` AS `K1` ON `K1`.`Adi`=GelenAdi AND `K1`.`Ref`=GelenRef AND `K2`.`KullaniciRef`=`K1`.`Ref` SET `K2`.`SonAktivite`=NOW();
		#END IF;

		UPDATE `KullanicilarBilgi` AS `K2` INNER JOIN `Kullanicilar` AS `K1` ON `K1`.`Adi`=GelenAdi AND `K2`.`KullaniciRef`=`K1`.`Ref` SET `K2`.`Profil`=(CASE WHEN (`K2`.`Profil`>0) THEN `K2`.`Profil`+1 ELSE 1 END);
    END IF;

    SELECT `K1`.*, `K2`.*, `K3`.* FROM `Kullanicilar` AS `K1` INNER JOIN `KullanicilarGenel` AS `K2` ON (`K1`.`Adi`=GelenAdi AND `K1`.`Pasif` IS NULL) AND `K2`.`KullaniciRef`=`K1`.`Ref` INNER JOIN `KullanicilarBilgi` AS `K3` ON `K3`.`KullaniciRef`=`K1`.`Ref` LIMIT 1;
END$$

--
-- İşlevler
--
DROP FUNCTION IF EXISTS `AltParametreReferansiGetirFonksiyonu`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `AltParametreReferansiGetirFonksiyonu` (`GelenRef` BIGINT, `GelenKod` CHAR(255)) RETURNS CHAR(255) CHARSET utf8 COLLATE utf8_turkish_ci NO SQL
    COMMENT 'Parametreler tablosuna Kod gönderilerek Ref geri alınır'
RETURN (SELECT `Ref` FROM `Parametreler` WHERE `UstParametreRef`=GelenRef AND `Kod`=GelenKod LIMIT 1)$$

DROP FUNCTION IF EXISTS `KullaniciUnvaniGetirFonksiyonu`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `KullaniciUnvaniGetirFonksiyonu` (`KullaniciRefGelen` BIGINT(20) UNSIGNED) RETURNS CHAR(32) CHARSET utf8 NO SQL
    COMMENT 'Kullanıcı referansı gönderilir, Kullanıcı adı geri alınır'
RETURN (SELECT `Adi` FROM `Kullanicilar` WHERE `Ref`=KullaniciRefGelen LIMIT 1)$$

DROP FUNCTION IF EXISTS `ParametreAciklamasiGetirFonksiyonu`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `ParametreAciklamasiGetirFonksiyonu` (`GelenRef` BIGINT(20)) RETURNS VARCHAR(1024) CHARSET utf8 COLLATE utf8_turkish_ci NO SQL
    COMMENT 'Parametreler tablosuna Ref gönderilerek Aciklama geri alınır'
RETURN (SELECT `Aciklama` FROM `Parametreler` WHERE `Ref`=GelenRef LIMIT 1)$$

DROP FUNCTION IF EXISTS `ParametreKoduGetirFonksiyonu`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `ParametreKoduGetirFonksiyonu` (`KullaniciParametreRefGelen` BIGINT(20)) RETURNS CHAR(255) CHARSET utf8 COLLATE utf8_turkish_ci NO SQL
    COMMENT 'Parametreler tablosuna Ref gönderilerek Kod geri alınır'
RETURN (SELECT `Kod` FROM `Parametreler` WHERE `Ref`=KullaniciParametreRefGelen LIMIT 1)$$

DROP FUNCTION IF EXISTS `ParametreMetinBirGetirFonksiyonu`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `ParametreMetinBirGetirFonksiyonu` (`GelenRef` BIGINT(20)) RETURNS TEXT CHARSET utf8 COLLATE utf8_turkish_ci NO SQL
    COMMENT 'Parametreler tablosuna Ref gönderilerek MetinBir geri alınır'
RETURN (SELECT `MetinBir` FROM `Parametreler` WHERE `Ref`=GelenRef LIMIT 1)$$

DROP FUNCTION IF EXISTS `ParametreReferansiGetirFonksiyonu`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `ParametreReferansiGetirFonksiyonu` (`KullaniciParametreKoduGelen` CHAR(255)) RETURNS CHAR(255) CHARSET utf8 COLLATE utf8_turkish_ci NO SQL
    COMMENT 'Parametreler tablosuna Kod gönderilerek Ref geri alınır'
RETURN (SELECT `Ref` FROM `Parametreler` WHERE `Kod`=KullaniciParametreKoduGelen LIMIT 1)$$

DROP FUNCTION IF EXISTS `ParametreTamSayiBirGetirFonksiyonu`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `ParametreTamSayiBirGetirFonksiyonu` (`GelenRef` BIGINT(20)) RETURNS BIGINT RETURN (SELECT `TamSayiBir` FROM `Parametreler` WHERE `Ref`=GelenRef LIMIT 1)$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Aktiviteler`
--

DROP TABLE IF EXISTS `Aktiviteler`;
CREATE TABLE IF NOT EXISTS `Aktiviteler` (
  `Ref` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `Md5Ref` bigint UNSIGNED NOT NULL,
  `Yer` varchar(4096) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `Konum` tinyint(1) DEFAULT NULL,
  `HamVeri` text CHARACTER SET utf8 COLLATE utf8_turkish_ci,
  `OlusturanRef` bigint UNSIGNED NOT NULL,
  `Ip` char(128) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturmaTarihi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Ref`) USING BTREE,
  KEY `IndexMd5Ref` (`Md5Ref`) USING BTREE,
  KEY `IndexOlusturunRef` (`OlusturanRef`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci COMMENT='Tüm aktivitelerin kaydı için kullanılacak tablodur.';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Bildirimler`
--

DROP TABLE IF EXISTS `Bildirimler`;
CREATE TABLE IF NOT EXISTS `Bildirimler` (
  `Ref` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `TakipEt` tinyint UNSIGNED DEFAULT NULL,
  `TakipBirak` tinyint UNSIGNED DEFAULT NULL,
  `Favori` tinyint UNSIGNED DEFAULT NULL,
  `Engel` tinyint UNSIGNED DEFAULT NULL,
  `Yorum` tinyint UNSIGNED DEFAULT NULL,
  `Giris` tinyint UNSIGNED DEFAULT NULL,
  `Cikis` tinyint UNSIGNED DEFAULT NULL,
  `GecersizGiris` tinyint UNSIGNED DEFAULT NULL,
  `OlusturanRef` bigint UNSIGNED NOT NULL,
  `Ip` char(128) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturmaTarihi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Ref`) USING BTREE,
  KEY `IndexOlusturanRef` (`OlusturanRef`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci COMMENT='İşlemlere verilecek bildirimlerin istek durumları saklanır';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Engel`
--

DROP TABLE IF EXISTS `Engel`;
CREATE TABLE IF NOT EXISTS `Engel` (
  `Ref` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `KullaniciRef` bigint UNSIGNED NOT NULL,
  `OlusturanRef` bigint UNSIGNED NOT NULL,
  `Ip` char(128) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturmaTarihi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Ref`) USING BTREE,
  KEY `IndexOlusturunRef` (`OlusturanRef`) USING BTREE,
  KEY `IndexKullaniciRef` (`KullaniciRef`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci COMMENT='Engellenen kullanıcıların takip tablosudur.';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Etiketler`
--

DROP TABLE IF EXISTS `Etiketler`;
CREATE TABLE IF NOT EXISTS `Etiketler` (
  `Ref` bigint UNSIGNED NOT NULL,
  `Adi` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturanRef` bigint UNSIGNED NOT NULL,
  `Ip` char(128) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturmaTarihi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Ref`) USING BTREE,
  UNIQUE KEY `BenzersizAdi` (`Adi`),
  KEY `IndexAdi` (`Adi`) USING BTREE,
  KEY `IndexOlusturanRef` (`OlusturanRef`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci COMMENT='Etiketler Tablosu';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Etkinlikler`
--

DROP TABLE IF EXISTS `Etkinlikler`;
CREATE TABLE IF NOT EXISTS `Etkinlikler` (
  `Ref` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `Md5Ref` bigint UNSIGNED NOT NULL,
  `Islem` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `HamVeri` text CHARACTER SET utf8 COLLATE utf8_turkish_ci,
  `OlusturanRef` bigint UNSIGNED NOT NULL,
  `Ip` char(128) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturmaTarihi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Ref`) USING BTREE,
  KEY `IndexMd5Ref` (`Md5Ref`) USING BTREE,
  KEY `IndexOlusturunRef` (`OlusturanRef`) USING BTREE,
  KEY `IndexIslem` (`Islem`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci COMMENT='Tüm hesap etkinlikleri için kullanılacak tablodur.';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Guncellemeler`
--

DROP TABLE IF EXISTS `Guncellemeler`;
CREATE TABLE IF NOT EXISTS `Guncellemeler` (
  `Ref` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `Degisen` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `Islem` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturanRef` bigint UNSIGNED NOT NULL,
  `Ip` char(128) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturmaTarihi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Ref`) USING BTREE,
  KEY `IndexOlusturanRef` (`OlusturanRef`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci COMMENT='Kullanıcı Adı ve E-Posta bilgilerinin güncellemelerinin kayıt edileceği tablodur.';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Hatirla`
--

DROP TABLE IF EXISTS `Hatirla`;
CREATE TABLE IF NOT EXISTS `Hatirla` (
  `KullaniciRef` bigint UNSIGNED NOT NULL,
  `Adi` char(32) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `HatirlaRef` char(64) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `Ip` char(128) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturmaTarihi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`KullaniciRef`) USING BTREE,
  UNIQUE KEY `BenzersizAdi` (`Adi`) USING BTREE,
  KEY `IndexAdi` (`Adi`) USING BTREE,
  KEY `IndexHatirlaRef` (`HatirlaRef`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci COMMENT='Kullanıcılar İçin Beni Hatırla Tablosu';

--
-- Tablo döküm verisi `Hatirla`
--

INSERT INTO `Hatirla` VALUES(1, 'admin', '183d74bf0d849910f2284ab5af34c793', '10.7.7.2', '2021-04-11 17:49:17');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Ip`
--

DROP TABLE IF EXISTS `Ip`;
CREATE TABLE IF NOT EXISTS `Ip` (
  `Ref` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `Ip` char(128) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `Isim` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci DEFAULT NULL,
  `Sehir` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci DEFAULT NULL,
  `Bolge` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci DEFAULT NULL,
  `Ulke` char(32) CHARACTER SET utf8 COLLATE utf8_turkish_ci DEFAULT NULL,
  `Enlem` char(32) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `Boylam` char(32) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `Organizasyon` varchar(1024) CHARACTER SET utf8 COLLATE utf8_turkish_ci DEFAULT NULL,
  `PostaKodu` char(32) CHARACTER SET utf8 COLLATE utf8_turkish_ci DEFAULT NULL,
  `SaatDilimi` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci DEFAULT NULL,
  `Guncelleme` bigint UNSIGNED NOT NULL DEFAULT '0',
  `SonGuncellemeTarihi` datetime DEFAULT NULL,
  `OlusturanRef` bigint UNSIGNED NOT NULL,
  `OlusturanIp` char(128) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturmaTarihi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Ref`) USING BTREE,
  UNIQUE KEY `BenzersizIp` (`Ip`) USING BTREE,
  KEY `IndexIp` (`Ip`) USING BTREE,
  KEY `IndexOlusturanRef` (`OlusturanRef`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci COMMENT='Ip bilgilerinin saklanacağı ve takip edileceği tablodur.';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Kullanicilar`
--

DROP TABLE IF EXISTS `Kullanicilar`;
CREATE TABLE IF NOT EXISTS `Kullanicilar` (
  `Ref` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `Adi` char(32) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `Parola` char(64) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `Kunye` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `Posta` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `Kontrol` char(64) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `Pasif` tinyint UNSIGNED DEFAULT NULL,
  `KullaniciTuru` bigint DEFAULT NULL,
  `OlusturanRef` bigint UNSIGNED NOT NULL,
  `Ip` char(128) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturmaTarihi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Ref`) USING BTREE,
  UNIQUE KEY `BenzersizAd` (`Adi`),
  UNIQUE KEY `BenzersizPosta` (`Posta`),
  KEY `IndexAdi` (`Adi`) USING BTREE,
  KEY `IndexParola` (`Parola`) USING BTREE,
  KEY `IndexPosta` (`Posta`) USING BTREE,
  KEY `IndexKontrol` (`Kontrol`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci COMMENT='Temel kullanıcılar tablosudur. Sürekli gerekli olanlar burada saklanacaktır. Diğerleri KullanicilarGenel tablosunda tutulacaktır.';

--
-- Tablo döküm verisi `Kullanicilar`
--

INSERT INTO `Kullanicilar` VALUES(1, 'admin', 'QFBhcm9sYQ==', 'Sistem Yöneticisi', 'ocalcem@gmail.com', '9430ddf343c2b226047b93f445eab23e', NULL, 5, 1, '127.0.0.1', '2020-04-15 20:53:40');
INSERT INTO `Kullanicilar` VALUES(2, 'tolga', 'QFBhcm9sYQ==', 'Tolga AKARDENİZ', 'tolga.akardeniz@hotmail.com.tr', 'be34aaab34e618b8cbeaa555612102f8', NULL, 5, 1, '127.0.0.1', '2020-05-27 00:15:08');

--
-- Tetikleyiciler `Kullanicilar`
--
DROP TRIGGER IF EXISTS `KullanicilarDeleteBefore`;
DELIMITER $$
CREATE TRIGGER `KullanicilarDeleteBefore` BEFORE DELETE ON `Kullanicilar` FOR EACH ROW IF EXISTS(SELECT `KullaniciRef` FROM `KullanicilarGenel` WHERE `KullaniciRef`=Old.Ref LIMIT 1) THEN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Önce kullanıcılar genel tablosundaki kayıt silinmelidir.';
END IF
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `KullanicilarInsertAfter`;
DELIMITER $$
CREATE TRIGGER `KullanicilarInsertAfter` AFTER INSERT ON `Kullanicilar` FOR EACH ROW INSERT INTO `KullanicilarGenel` (`KullaniciRef`) VALUES (New.`Ref`)
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `KullanicilarBilgi`
--

DROP TABLE IF EXISTS `KullanicilarBilgi`;
CREATE TABLE IF NOT EXISTS `KullanicilarBilgi` (
  `KullaniciRef` bigint UNSIGNED NOT NULL,
  `Urun` bigint UNSIGNED DEFAULT NULL COMMENT 'Kullanıcıya ait toplam ürün miktarı.',
  `Puan` tinyint UNSIGNED DEFAULT NULL,
  `Begeni` bigint UNSIGNED DEFAULT NULL,
  `Takipci` bigint UNSIGNED DEFAULT NULL,
  `Takip` bigint UNSIGNED DEFAULT NULL,
  `Profil` bigint UNSIGNED DEFAULT NULL,
  `Giris` bigint UNSIGNED DEFAULT NULL,
  `Cikis` bigint UNSIGNED DEFAULT NULL,
  `Durum` bigint UNSIGNED DEFAULT NULL,
  `PostaOnay` tinyint UNSIGNED DEFAULT NULL COMMENT 'Kullanıcının posta adresi onay kontrol alanı.',
  `OturumTarihi` datetime DEFAULT NULL,
  `CikisTarihi` datetime DEFAULT NULL,
  `SonAktivite` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`KullaniciRef`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci COMMENT='Kullanıcıların sayaç bilgi tablosudur.';

--
-- Tablo döküm verisi `KullanicilarBilgi`
--

INSERT INTO `KullanicilarBilgi` VALUES(1, NULL, NULL, NULL, NULL, NULL, 8, 35, 1, NULL, NULL, '2021-05-07 00:05:39', '2021-05-07 00:05:30', '2021-04-05 12:32:34');
INSERT INTO `KullanicilarBilgi` VALUES(2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-04-05 12:32:34');

--
-- Tetikleyiciler `KullanicilarBilgi`
--
DROP TRIGGER IF EXISTS `KullanicilarBilgiDeleteAfter`;
DELIMITER $$
CREATE TRIGGER `KullanicilarBilgiDeleteAfter` AFTER DELETE ON `KullanicilarBilgi` FOR EACH ROW DELETE FROM `KullanicilarGenel` WHERE `KullaniciRef`=Old.KullaniciRef LIMIT 1
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `KullanicilarGenel`
--

DROP TABLE IF EXISTS `KullanicilarGenel`;
CREATE TABLE IF NOT EXISTS `KullanicilarGenel` (
  `KullaniciRef` bigint UNSIGNED NOT NULL,
  `PostaOnay` tinyint(1) DEFAULT NULL,
  `Cinsiyet` tinyint(1) DEFAULT NULL,
  `Vatandas` bigint UNSIGNED DEFAULT NULL,
  `Dogum` date DEFAULT NULL,
  `Telefon` char(32) CHARACTER SET utf8 COLLATE utf8_turkish_ci DEFAULT NULL,
  `Goruntu` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci DEFAULT NULL,
  `Bilgi` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci DEFAULT NULL,
  PRIMARY KEY (`KullaniciRef`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci COMMENT='Genel Kullanıcılar Tablosu';

--
-- Tablo döküm verisi `KullanicilarGenel`
--

INSERT INTO `KullanicilarGenel` VALUES(1, NULL, 1, NULL, '2021-05-05', NULL, '{\"Arka\": \"/tmp/back/2021.05.06/1/34dkLrv.jpg\", \"Profil\": \"/tmp/profile/2021.05.06/1/34dkLrv.jpg\"}', 'Deneme mesajıdır. Deneme için yapılmıştır.');
INSERT INTO `KullanicilarGenel` VALUES(2, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Tetikleyiciler `KullanicilarGenel`
--
DROP TRIGGER IF EXISTS `KullanicilarGenelDeleteAfter`;
DELIMITER $$
CREATE TRIGGER `KullanicilarGenelDeleteAfter` AFTER DELETE ON `KullanicilarGenel` FOR EACH ROW DELETE FROM `Kullanicilar` WHERE `Ref`=Old.KullaniciRef LIMIT 1
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `KullanicilarGenelInsertAfter`;
DELIMITER $$
CREATE TRIGGER `KullanicilarGenelInsertAfter` AFTER INSERT ON `KullanicilarGenel` FOR EACH ROW INSERT INTO `KullanicilarBilgi` (`KullaniciRef`) VALUES (New.`KullaniciRef`)
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Kurlar`
--

DROP TABLE IF EXISTS `Kurlar`;
CREATE TABLE IF NOT EXISTS `Kurlar` (
  `Ref` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `Birim` bigint UNSIGNED NOT NULL,
  `Kod` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `Cins` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `D1` decimal(20,4) UNSIGNED DEFAULT NULL,
  `D2` decimal(20,4) UNSIGNED DEFAULT NULL,
  `D3` decimal(20,4) UNSIGNED DEFAULT NULL,
  `D4` decimal(20,4) UNSIGNED DEFAULT NULL,
  `Tarih` date NOT NULL,
  `OlusturanRef` bigint UNSIGNED NOT NULL,
  `Ip` char(128) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturmaTarihi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Ref`) USING BTREE,
  KEY `KurlarKullanicilarBaglantisi` (`OlusturanRef`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci COMMENT='Merkez bankasından alınacak bilgiler ile oluşturulacak döviz kurları tablosu.';

--
-- Tetikleyiciler `Kurlar`
--
DROP TRIGGER IF EXISTS `KurlarInsertBefore`;
DELIMITER $$
CREATE TRIGGER `KurlarInsertBefore` BEFORE INSERT ON `Kurlar` FOR EACH ROW IF EXISTS(SELECT Ref FROM `Kurlar` WHERE `Kod`=New.Kod AND `Tarih`=New.Tarih LIMIT 1) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Kayıt zaten var.';
END IF
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Loglar`
--

DROP TABLE IF EXISTS `Loglar`;
CREATE TABLE IF NOT EXISTS `Loglar` (
  `Ref` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `Ekran` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `Islem` varchar(4096) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `Aciklama` text CHARACTER SET utf8 COLLATE utf8_turkish_ci,
  `OlusturanRef` bigint UNSIGNED NOT NULL,
  `Ip` char(128) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturmaTarihi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Ref`) USING BTREE,
  KEY `IndexOlusturanRef` (`OlusturanRef`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci COMMENT='Tüm sistemde yapılan işlemler ile ilgili bilginin tutulacağı tablo.';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Parametreler`
--

DROP TABLE IF EXISTS `Parametreler`;
CREATE TABLE IF NOT EXISTS `Parametreler` (
  `Ref` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `UstParametreRef` bigint UNSIGNED DEFAULT NULL,
  `Kod` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `Aciklama` varchar(1024) CHARACTER SET utf8 COLLATE utf8_turkish_ci DEFAULT NULL,
  `MetinBir` text CHARACTER SET utf8 COLLATE utf8_turkish_ci,
  `MetinIki` text CHARACTER SET utf8 COLLATE utf8_turkish_ci,
  `MetinUc` text CHARACTER SET utf8 COLLATE utf8_turkish_ci,
  `MetinDort` text CHARACTER SET utf8 COLLATE utf8_turkish_ci,
  `MetinBes` text CHARACTER SET utf8 COLLATE utf8_turkish_ci,
  `OndalikSayiBir` decimal(20,6) DEFAULT NULL,
  `OndalikSayiIki` decimal(20,6) DEFAULT NULL,
  `OndalikSayiUc` decimal(20,6) DEFAULT NULL,
  `OndalikSayiDort` decimal(20,6) DEFAULT NULL,
  `OndalikSayiBes` decimal(20,6) DEFAULT NULL,
  `TamSayiBir` bigint DEFAULT NULL,
  `TamSayiIki` bigint DEFAULT NULL,
  `TamSayiUc` bigint DEFAULT NULL,
  `TamSayiDort` bigint DEFAULT NULL,
  `TamSayiBes` bigint DEFAULT NULL,
  `TarihBir` datetime DEFAULT NULL,
  `TarihIki` datetime DEFAULT NULL,
  `TarihUc` datetime DEFAULT NULL,
  `TarihDort` datetime DEFAULT NULL,
  `TarihBes` datetime DEFAULT NULL,
  `OlusturanRef` bigint UNSIGNED NOT NULL,
  `Ip` char(128) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturmaTarihi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Ref`) USING BTREE,
  KEY `IndexUstParametreRef` (`UstParametreRef`) USING BTREE,
  KEY `IndexKod` (`Kod`) USING BTREE,
  KEY `IndexOlusturunRef` (`OlusturanRef`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci COMMENT='Amaç tüm sistemdeki parametreleri farklı tablolarda tutmak yerine tek çatı altında toplamak.';

--
-- Tablo döküm verisi `Parametreler`
--

INSERT INTO `Parametreler` VALUES(1, NULL, 'TarayiciBilgisi', 'Kullanıcıların tarayıcı bilgileri MD5 olarak kayıt edilip aktiviteler tablosunda kullanılacak.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '10.7.7.2', '2021-04-05 12:32:37');
INSERT INTO `Parametreler` VALUES(2, 1, '19ff34532f2db64cc1d58783534ce764', NULL, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36 OPR/75.0.3969.149', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '10.7.7.2', '2021-04-05 13:15:28');
INSERT INTO `Parametreler` VALUES(3, 1, '5b8063a6d4179acbdbc00c2a331f2fd2', NULL, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.114 Safari/537.36 OPR/75.0.3969.171', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '10.7.7.2', '2021-04-19 21:07:19');
INSERT INTO `Parametreler` VALUES(4, 1, '4843d5625d41684e46d509cd62ab0b20', NULL, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.128 Safari/537.36 OPR/75.0.3969.218', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '10.7.7.2', '2021-04-21 21:51:50');
INSERT INTO `Parametreler` VALUES(5, 1, 'f60fa90a811603110aa9acdbc3ce4d8e', NULL, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.85 Safari/537.36 OPR/76.0.4017.94', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '10.7.7.2', '2021-04-30 20:51:33');

--
-- Tetikleyiciler `Parametreler`
--
DROP TRIGGER IF EXISTS `ParametrelerDeleteBefore`;
DELIMITER $$
CREATE TRIGGER `ParametrelerDeleteBefore` BEFORE DELETE ON `Parametreler` FOR EACH ROW BEGIN
    DECLARE Mesaj CHAR(255);

    IF EXISTS(SELECT `Ref` FROM `Parametreler` WHERE `UstParametreRef`=Old.`Ref` LIMIT 1) THEN
      SET Mesaj = "Silmek istediğiniz parametrenin bağlantısı var. Silinemez.";
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = Mesaj;
    END IF;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `ParametrelerInsertBefore`;
DELIMITER $$
CREATE TRIGGER `ParametrelerInsertBefore` BEFORE INSERT ON `Parametreler` FOR EACH ROW BEGIN
    DECLARE Mesaj CHAR(255);
  # REGEXP '^[0-9]+$'
    IF (New.UstParametreRef IS NULL) THEN
    IF EXISTS(SELECT Ref FROM `Parametreler` WHERE `UstParametreRef` IS NULL AND Kod=New.`Kod` LIMIT 1) THEN
      SET Mesaj = "Aynı parametre koduna ait kayıt zaten var.";
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = Mesaj;
        END IF;
  ELSE
      #IF EXISTS(SELECT Ref FROM `Parametreler` WHERE `UstParametreRef`=New.`UstParametreRef` AND Kod=New.Kod AND `Aciklama`=New.`Aciklama` LIMIT 1) THEN
      IF (New.`UstParametreRef` = (SELECT `Ref` FROM `Parametreler` WHERE `Kod`='Yetkiler' LIMIT 1)) THEN


        IF EXISTS(SELECT Ref FROM `Parametreler` WHERE `UstParametreRef`=New.`UstParametreRef` AND Kod=New.Kod AND `Aciklama`=New.`Aciklama` LIMIT 1) THEN
        SET Mesaj = "Üst parametreye tanımlı aynı koda ait kayıt zaten var.";
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = Mesaj;
          END IF;

          ELSE

        IF EXISTS(SELECT Ref FROM `Parametreler` WHERE `UstParametreRef`=New.`UstParametreRef` AND Kod=New.Kod LIMIT 1) THEN
        SET Mesaj = "Üst parametreye tanımlı aynı koda ait kayıt zaten var.";
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = Mesaj;
          END IF;


          END IF;



    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `PostaOnay`
--

DROP TABLE IF EXISTS `PostaOnay`;
CREATE TABLE IF NOT EXISTS `PostaOnay` (
  `Ref` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `Kod` bigint UNSIGNED NOT NULL COMMENT 'Onay kodu sadece sayı 6 karakter olmalı.',
  `Posta` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `Veri` text CHARACTER SET utf8 COLLATE utf8_turkish_ci,
  `Ip` char(128) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturmaTarihi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Ref`) USING BTREE,
  UNIQUE KEY `UniquePosta` (`Posta`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci COMMENT='Kullanıcıların posta adreslerini onaylamak için kullanılacak.';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Sezonlar`
--

DROP TABLE IF EXISTS `Sezonlar`;
CREATE TABLE IF NOT EXISTS `Sezonlar` (
  `Ref` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Benzersiz artan numara.',
  `Islem` bigint DEFAULT '1' COMMENT 'GuncellemeTarihi alanının güncelleme miktarı.',
  `Benzersiz` varchar(32) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL COMMENT 'Benzersiz sezon referansı.',
  `Guncelleme` bigint UNSIGNED DEFAULT NULL COMMENT 'Verin alanının güncelleme miktarı.',
  `Veri` text CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL COMMENT 'Sezon verisi.',
  `OlusturanRef` bigint UNSIGNED DEFAULT NULL COMMENT 'Kullanıcı referansı.',
  `Ip` char(128) CHARACTER SET utf8 COLLATE utf8_turkish_ci DEFAULT NULL COMMENT 'Kullanıcı ip numarası.',
  `GuncellemeTarihi` datetime DEFAULT NULL COMMENT 'Son güncelleme tarihi sürekli işlenir.',
  `OlusturmaTarihi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'İlk oluşturma tarihi tanımlanır.',
  PRIMARY KEY (`Ref`),
  UNIQUE KEY `UniqueBenzersiz` (`Benzersiz`) USING BTREE,
  KEY `IndexOlusturanRef` (`OlusturanRef`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `Sezonlar`
--

INSERT INTO `Sezonlar` VALUES(104, 6, 'd94051757542a42e35a63a7c1c9852fd', NULL, 'Ref|s:1:\"1\";Adi|s:5:\"admin\";Parola|s:12:\"QFBhcm9sYQ==\";Kunye|s:18:\"Sistem Yöneticisi\";Yetkisi|s:32:\"f60fa90a811603110aa9acdbc3ce4d8e\";Posta|s:17:\"ocalcem@gmail.com\";KullaniciTuru|s:1:\"5\";Pasif|N;OlusturmaTarihi|s:19:\"2020-04-15 20:53:40\";PostaOnay|N;Goruntu|s:97:\"{\"Arka\": \"/tmp/back/2021.05.06/1/34dkLrv.jpg\", \"Profil\": \"/tmp/profile/2021.05.06/1/34dkLrv.jpg\"}\";Vatandas|N;Dogum|s:10:\"05/05/2021\";Telefon|N;Cinsiyet|s:1:\"1\";OturumTarihi|s:19:\"2021-05-07 00:05:39\";', 1, '10.7.7.2', '2021-05-07 00:19:50', '2021-05-07 00:05:39');

--
-- Tetikleyiciler `Sezonlar`
--
DROP TRIGGER IF EXISTS `SezonlarUpdateBefore`;
DELIMITER $$
CREATE TRIGGER `SezonlarUpdateBefore` BEFORE UPDATE ON `Sezonlar` FOR EACH ROW BEGIN
	IF Old.`GuncellemeTarihi`<>New.`GuncellemeTarihi` THEN
        SET New.`Islem`=New.`Islem`+1;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Sorgulamalar`
--

DROP TABLE IF EXISTS `Sorgulamalar`;
CREATE TABLE IF NOT EXISTS `Sorgulamalar` (
  `Ref` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `Sorgulanan` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `Islem` char(255) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturanRef` bigint UNSIGNED NOT NULL,
  `Ip` char(128) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturmaTarihi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Ref`) USING BTREE,
  KEY `IndexOlusturanRef` (`OlusturanRef`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci COMMENT='Kullanıcı Adı ve E-Posta bilgilerinin sorgulamalarının kayıt edileceği tablodur.';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Takip`
--

DROP TABLE IF EXISTS `Takip`;
CREATE TABLE IF NOT EXISTS `Takip` (
  `Ref` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `KullaniciRef` bigint UNSIGNED NOT NULL,
  `OlusturanRef` bigint UNSIGNED NOT NULL,
  `Ip` char(128) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,
  `OlusturmaTarihi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Ref`) USING BTREE,
  KEY `IndexOlusturunRef` (`OlusturanRef`) USING BTREE,
  KEY `IndexKullaniciRef` (`KullaniciRef`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci COMMENT='Takip edilen kullanıcıların takip tablosudur.';

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `Etiketler`
--
ALTER TABLE `Etiketler` ADD FULLTEXT KEY `Adi` (`Adi`);

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `Aktiviteler`
--
ALTER TABLE `Aktiviteler`
  ADD CONSTRAINT `AktivitelerKullanicilarBaglantisi` FOREIGN KEY (`OlusturanRef`) REFERENCES `Kullanicilar` (`Ref`);

--
-- Tablo kısıtlamaları `Bildirimler`
--
ALTER TABLE `Bildirimler`
  ADD CONSTRAINT `BildirimlerKullanicilarBaglantisi` FOREIGN KEY (`OlusturanRef`) REFERENCES `Kullanicilar` (`Ref`);

--
-- Tablo kısıtlamaları `Engel`
--
ALTER TABLE `Engel`
  ADD CONSTRAINT `EngelKullanicilarBaglantisiKullaniciRef` FOREIGN KEY (`KullaniciRef`) REFERENCES `Kullanicilar` (`Ref`),
  ADD CONSTRAINT `EngelKullanicilarBaglantisiOlusturanRef` FOREIGN KEY (`OlusturanRef`) REFERENCES `Kullanicilar` (`Ref`);

--
-- Tablo kısıtlamaları `Etiketler`
--
ALTER TABLE `Etiketler`
  ADD CONSTRAINT `EtiketlerKullanicilarBaglantisi` FOREIGN KEY (`OlusturanRef`) REFERENCES `Kullanicilar` (`Ref`);

--
-- Tablo kısıtlamaları `Etkinlikler`
--
ALTER TABLE `Etkinlikler`
  ADD CONSTRAINT `EtkinliklerKullanicilarBaglantisi` FOREIGN KEY (`OlusturanRef`) REFERENCES `Kullanicilar` (`Ref`);

--
-- Tablo kısıtlamaları `Guncellemeler`
--
ALTER TABLE `Guncellemeler`
  ADD CONSTRAINT `GuncellemelerKullanicilarBaglantisi` FOREIGN KEY (`OlusturanRef`) REFERENCES `Kullanicilar` (`Ref`);

--
-- Tablo kısıtlamaları `Hatirla`
--
ALTER TABLE `Hatirla`
  ADD CONSTRAINT `HatirlaKulanicilarBaglantisi` FOREIGN KEY (`KullaniciRef`) REFERENCES `Kullanicilar` (`Ref`);

--
-- Tablo kısıtlamaları `Ip`
--
ALTER TABLE `Ip`
  ADD CONSTRAINT `IpKullanicilarBaglantisi` FOREIGN KEY (`OlusturanRef`) REFERENCES `Kullanicilar` (`Ref`);

--
-- Tablo kısıtlamaları `KullanicilarBilgi`
--
ALTER TABLE `KullanicilarBilgi`
  ADD CONSTRAINT `KullanicilarBilgiKulanicilarBaglantisi` FOREIGN KEY (`KullaniciRef`) REFERENCES `Kullanicilar` (`Ref`);

--
-- Tablo kısıtlamaları `KullanicilarGenel`
--
ALTER TABLE `KullanicilarGenel`
  ADD CONSTRAINT `KullanicilarGenelKulanicilarBaglantisi` FOREIGN KEY (`KullaniciRef`) REFERENCES `Kullanicilar` (`Ref`);

--
-- Tablo kısıtlamaları `Kurlar`
--
ALTER TABLE `Kurlar`
  ADD CONSTRAINT `KurlarKullanicilarBaglantisi` FOREIGN KEY (`OlusturanRef`) REFERENCES `Kullanicilar` (`Ref`);

--
-- Tablo kısıtlamaları `Loglar`
--
ALTER TABLE `Loglar`
  ADD CONSTRAINT `LoglarKullaniciBaglantisi` FOREIGN KEY (`OlusturanRef`) REFERENCES `Kullanicilar` (`Ref`);

--
-- Tablo kısıtlamaları `Parametreler`
--
ALTER TABLE `Parametreler`
  ADD CONSTRAINT `ParametrelerKulanicilarBaglantisi` FOREIGN KEY (`OlusturanRef`) REFERENCES `Kullanicilar` (`Ref`);

--
-- Tablo kısıtlamaları `Sorgulamalar`
--
ALTER TABLE `Sorgulamalar`
  ADD CONSTRAINT `SorgulamalarKullanicilarBaglantisi` FOREIGN KEY (`OlusturanRef`) REFERENCES `Kullanicilar` (`Ref`);

--
-- Tablo kısıtlamaları `Takip`
--
ALTER TABLE `Takip`
  ADD CONSTRAINT `TakipKullanicilarBaglantisiKullaniciRef` FOREIGN KEY (`KullaniciRef`) REFERENCES `Kullanicilar` (`Ref`),
  ADD CONSTRAINT `TakipKullanicilarBaglantisiOlusturanRef` FOREIGN KEY (`OlusturanRef`) REFERENCES `Kullanicilar` (`Ref`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
