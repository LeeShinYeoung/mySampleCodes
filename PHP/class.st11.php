<?php
/**
API Requst
request($URL,$METHOD,$XML='')

배열을 XML형식으로 변환
arrayToXml($array)

XML형식을 배열로 변환
xmlToArray($xml_string)

XML->배열로 변환했을때 비어있는 Key null처리
recursiveNullArrayToNull($array)
**/
class st11
{
	public $test = false;
	public $test_log = array();

	public function __destruct()
	{
		if ($this->test) {
			echoDev( $this->test_log );
		}
	}

	static function request($URL,$METHOD,$XML='')
	{
		$ch = curl_init();
		$HEADER = array("Content-type: text/xml;charset=EUC-KR", "openapikey:");
		if ($GLOBALS['print_process']) echoDev($URL);
		if ($XML) {
			$XML = "<?xml version='1.0' encoding='euc-kr'?>".$XML;
			curl_setopt($ch, CURLOPT_POSTFIELDS, $XML);
			if( $GLOBALS['print_process'] ) echoDev($XML);
		}
		curl_setopt($ch, CURLOPT_URL, $URL);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $HEADER);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		switch ($METHOD) {
			case 'POST' : curl_setopt($ch, CURLOPT_POST, true); break;
			case 'PUT' : curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); break;
			case 'GET' : curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET'); break;
		}
		$response = curl_exec($ch);
		curl_close($ch);
		if( $GLOBALS['print_process'] ) echoDev($response);
		return $response;
	}
    
	static function arrayToXml($array)
	{
	    $xml = '';
		foreach ($array AS $k=>$v) {
			if (is_array($v)) {
				if (isset($v[0])) {
					for ($i=0,$end=count($v); $i<$end; ++$i) 
						$xml .= "<{$k}>".self::arrayToXml($v[$i])."</{$k}>";
				} else {
					$xml .= "<{$k}>".self::arrayToXml($v)."</{$k}>";
				}
			} else {
				$xml .= "<{$k}>{$v}</{$k}>";
			}
		}
		return $xml;
	}
    
	static function xmlToArray($xml_string)
	{
		$xml_string = str_replace('ns2:','',$xml_string);
		$xml = @simplexml_load_string($xml_string);
		if( $xml === false ) return false;
		return self::recursiveNullArrayToNull(util::recursive_iconv('utf8','euckr',json_decode(json_encode($xml),true)));
	}
    
	static function recursiveNullArrayToNull($array)
	{
		foreach ($array AS $k=>$v) {
			if (is_array($v)) {
				if (count($v) == 0)
					$array[$k] = null;
				else
					$array[$k] = self::recursiveNullArrayToNull($v);
			}
		}
		return $array;
	}
}