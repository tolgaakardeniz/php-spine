<?php

/**
 * بِسْــــــــــــــــــمِ اﷲِالرَّحْمَنِ اارَّحِيم
 * 
 * Eûzubillâhimineşşeytânirracîym - Bismillâhirrahmânirrahîm
 * 
 * Rahman ve Rahim olan "Allah" 'ın adıyla
 * 
 */

class kullaniciController extends Controllers
{
	public function __construct(App $app)
	{
		/**
		 * Böyle bir sayfa olmadığı için ana sayfaya yönlendir
		 */
		header("location: /");
		exit;
	}
}