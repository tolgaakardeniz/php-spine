<?php

namespace PhpProje\Library;


class Controls
{
	/**
	 * This method logically validates Turkish VAT number
	 *
	 * @param string $taxNumber
	 * @return bool
	 */
	public function validateTaxNumber(string$taxNumber):bool{if(strlen($taxNumber)!==10)return false;$y=0;$q=null;for($i=0;$i<9;$i++){$x=($taxNumber[$i]+(9-$i))%10;$z=($x*(2**(9-$i)))%9;if($x!==0&&$z===0)$z=9;$y+=$z;}if($y%10===0)$q=0;else $q=10-($y%10);if((int)$taxNumber[9]!==$q)return false;return true;}

	/**
	 * T.C. Kimlik kontrol
	 * 
	 * @param string $tcKimlik
	 * @return bool
	 */
	public function tcKimlikKontrol($tcKimlik):bool{if(!preg_match("/^[0-9]{11}$/",$tcKimlik))return false;$tcKimlik=trim($tcKimlik);$tcKimlik=trim($tcKimlik,"0");if(strlen($tcKimlik)!=11)return false;$tekBasamaklar=0;$ciftBasamaklar=0;for($i=0;$i<=8;$i+=2)$tekBasamaklar+=$tcKimlik[$i];for($i=1;$i<=7;$i+=2)$ciftBasamaklar+=$tcKimlik[$i];if(((7*$tekBasamaklar)-$ciftBasamaklar)%10!=$tcKimlik[9])return false;$toplam=0;for($i=0;$i<=9;$i++)$toplam+=$tcKimlik[$i];if($toplam%10!=$tcKimlik[10])return false;else return true;}
}
