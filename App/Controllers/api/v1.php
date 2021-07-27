<?php

/**
 * بِسْــــــــــــــــــمِ اﷲِالرَّحْمَنِ اارَّحِيم
 * 
 * Eûzubillâhimineşşeytânirracîym - Bismillâhirrahmânirrahîm
 * 
 * Rahman ve Rahim olan "Allah" 'ın adıyla
 * 
 */

class v1Controller extends Controllers
{
	public function __construct(App $app)
	{

		header('Content-Type: application/json');
		/**
		 * Böyle bir sayfa olmadığı için ana sayfaya yönlendir
		 */
		header("location: /");
		exit;
	}
}
