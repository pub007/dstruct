<?php
/**
 * Validate class
 */
/**
 * Class containing static methods to aid Validation
 * @package dstruct_common
 */
class Validate {

/**
 * Class constructor
 * @throws DStructGeneralException
 */
private function __construct() {
	throw new DStructGeneralException('Validate::__construct() - Trying to instantiate a class which has only static methods');
}

/**
 * Validate input contains only alphas
 * @param string $value
 * @return boolean
 */
public static function isAlpha($value) {
	$pattern = '/^[a-zA-Z]+$/';
	if(preg_match($pattern, $value)) {return true;}
	return false;
}

/**
 * Is the string a known API?
 * 
 * 
 * @param unknown $api
 * @param number $host
 * @return boolean|unknown|Ambigous <>|string
 * @todo Shane.... wft?
 */
public static function isAPI($api, $host = 1){
	if((!$api) || (!$host)){return false;}
	switch($host){
		case 1:
			// YOUTUBE
			if(strlen($api) < 12){
				if(self::isYouTubeAPI($api)){
					return $api;
				}
			}
			$preg1 = '@www.youtube.com\/watch\?v=(.*?)$@';
			if(preg_match($preg1, $api, $match)){
				$id = explode('&', $match[1]);   
				if(self::isYouTubeAPI($id[0])){
					return $id[0];
				}
			}
			$preg1 = '@www.youtube.com\/v\/(.*?)$@';
			if(preg_match($preg1, $api, $match)){
				$id = explode('&',$match[1]);   
				if(self::isYouTubeAPI($id[0])){
					return $id[0];
				}
			}
			return false;
			break;
		case 2:
			// BBC
			if(strlen($api) == 23){
				if(strrchr($api, '/')){
					if(!list($api1, $api2, $api3) = explode('/', $api, 3)){return false;}
					if(self::isNumeric($api1) == false){return false;}
					if(self::isNumeric($api2) == false){return false;}
					if(self::isNumeric($api3) == false){return false;}
					return $api;
				}
			}
			if(strstr($api, 'http://news.bbc.co.uk/media/emp/')){
				$start = strpos($api, 'http://news.bbc.co.uk/media/emp/');
				$api = substr($api, $start + 32, 23);
				if(strrchr($api, '/')){
					if(!list($api1, $api2, $api3) = explode('/', $api, 3)){return false;}
					if(self::isNumeric($api1) == false){return false;}
					if(self::isNumeric($api2) == false){return false;}
					if(self::isNumeric($api3) == false){return false;}
					return $api;
				}
			}
			return false;
			break;
	}
}

/**
 * Check input only contains letters and numbers.
 * 
 * @param string $value String to validate
 * @param string $allowablechars 
 * @throws DStructGeneralException
 * @return boolean
 * @todo split method from allowablechars stuff!
 */
public static function isAlphaNumeric($value, $allowablechars = false){
	if($allowablechars){
		if(!is_array($allowablechars)){throw new DStructGeneralException('Validate::isAlphaNumeric() - Trying to validate using allowablechars that are not in an array', E_USER_ERROR);}
		$alpha = '/^[a-zA-Z0-9';
		foreach ($allowablechars as $char) {
			$alpha .= $char;
		}
		$alpha .= ']+$/';
	}else{
		$alpha = '/^[a-zA-Z0-9]+$/';
	}
	if(preg_match($alpha, $value)) {return true;}
	return false;
}

/**
 * Validate format for bank account number.
 * 
 * Bank account numbers are just 8 numeric characters.
 * @param integer $number
 * @return boolean
 * @todo Just us a regex? Do we even need this? Better to keep it as ppl may get confused with leading zeros??
 */
public static function isBankAccountNumber($number) {
	if (!self::isNumeric($number, false, false)) {return false;}
	return (strlen($number) == 8)? true : false; // don't need iconv as is numeric
}

/**
 * Validate a two-letter country code.
 * 
 * @param string $code
 * @return boolean
 * @todo What about upper case stuff...?? Make it a param?
 * @tod What about method to return the country name and vice verse
 */
public static function isCountryCode($code){
	if(ctype_upper($code)){
		if(self::isAlpha($code)){
			if(in_array($code, array('AC', 'AD', 'AE', 'AF', 'AG', 'AI', 'AL', 'AM', 'AN', 'AO', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AW', 'AX', 'AZ', 'BA', 'BB', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BM', 'BN', 'BO', 'BR', 'BS', 'BT', 'BV', 'BW', 'BY', 'BZ', 'CA', 'CC', 'CD', 'CF', 'CG', 'CH', 'CI', 'CK', 'CL', 'CM', 'CN', 'CO', 'CR', 'CS', 'CU', 'CV', 'CX', 'CY', 'CZ', 'DE', 'DJ', 'DK', 'DM', 'DO', 'DZ', 'EC', 'EE', 'EG', 'EH', 'ER', 'ES', 'ET', 'FI', 'FJ', 'FK', 'FM', 'FO', 'FR', 'FX', 'GA', 'GB', 'GD', 'GE', 'GF', 'GH', 'GI', 'GL', 'GM', 'GN', 'GP', 'GQ', 'GR', 'GS', 'GT', 'GU', 'GW', 'GY', 'HK', 'HM', 'HN', 'HR', 'HT', 'HU', 'ID', 'IE', 'IL', 'IM', 'IN', 'IO', 'IQ', 'IR', 'IS', 'IT', 'JE', 'JM', 'JO', 'JP', 'KE', 'KG', 'KH', 'KI', 'KM', 'KN', 'KP', 'KR', 'KW', 'KY', 'KZ', 'LA', 'LB', 'LC', 'LI', 'LK', 'LR', 'LS', 'LT', 'LU', 'LV', 'LY', 'MA', 'MC', 'MD', 'ME', 'MG', 'MH', 'MK', 'ML', 'MM', 'MN', 'MO', 'MP', 'MQ', 'MR', 'MS', 'MT', 'MU', 'MV', 'MW', 'MX', 'MY', 'MZ', 'NA', 'NC', 'NE', 'NF', 'NG', 'NI', 'NL', 'NO', 'NP', 'NR', 'NT', 'NU', 'NZ', 'OM', 'PA', 'PE', 'PF', 'PG', 'PH', 'PK', 'PL', 'PN', 'PR', 'PS', 'PT', 'PW', 'PY', 'QA', 'RE', 'RO', 'RS', 'RU', 'RW', 'SA', 'SB', 'SC', 'SD', 'SE', 'SG', 'SH', 'SI', 'SJ', 'SK', 'SL', 'SM', 'SN', 'SO', 'SR', 'ST', 'SU', 'SV', 'SY', 'SZ', 'TC', 'TD', 'TF', 'TG', 'TH', 'TJ', 'TK', 'TM', 'TN', 'TO', 'TP', 'TR', 'TT', 'TV', 'TW', 'TZ', 'UA', 'UG', 'UK', 'UM', 'US', 'UY', 'UZ', 'VA', 'VC', 'VE', 'VG', 'VI', 'VN', 'VU', 'WF', 'WS', 'YE', 'YT', 'YU', 'ZA', 'ZM', 'ZR', 'ZW'))){
				return true;
			}
		}
	}
	return false;
}


/**
 * Check whether a date is valid'
 * @param string $date Format dd/mm/yyyy
 * @return boolean
 * @todo Only checks a very specific format!
 */
public static function isDate($date) {
    if (!isset($date) || $date=="") {return false;}
	if(strrpos($date, "/") == false){return false;}
    list($dd,$mm,$yy) = explode("/",$date);
	if ($dd!="" && $mm!="" && $yy!="") {
		if (is_numeric($yy) && is_numeric($mm) && is_numeric($dd)) {return checkdate($mm,$dd,$yy);}
	}  
	return false;
	
}

/**
 * Is a valid domain part.
 * 
 * Can't use FILTERs because they require scheme etc (docs are incorrect and flags do nothing).
 * Many solutions on the internet were examined for checking domains, but all of them failed
 * at least one test! Also, the PHP filter is broken at the time of writting.
 * @param string $domain
 * @return boolean
 * @author David Lidstone
 */
public static function isDomain($domain) {
	// cant do this as it returns the host part as the path if there is no scheme!! Passes http://.com. Fixed in PHP 5.4.7?
	//echo parse_url($url, PHP_URL_HOST);
	//if (@parse_url($url, PHP_URL_HOST)) {return true;}
	//return false;
	
	$domainlen = strlen($domain);
	if ($domainlen < 4 || $domainlen > 255) {return false;}
	
	// Regex by David as ones on internet are all broken!
	// ^[a-z0-9] From start of string (denoted by the ^), must start with alpha numeric
	// ( = start a sub pattern which contains...
	//     \\.(?![\\.|\\-]) = period not followed by another period or hyphen
	//     | = OR [a-z0-9] = any alpha-numeric
	//     | = OR [\\.(?![\\.|\\-]) = hyphen not followed by another hyphen or period
	// )* = close sub pattern - can be matched any number of times
	// \\.[a-z0-9]{2,}$ = must end with (denoted by the $) a period and at least 2 alpha numerics
	// i = case insensitive regex
	$regex = '/^[a-z0-9](\\.(?![\\.|\\-])|[a-z0-9]|\\-(?![\\-|\\.]))*\\.[a-z0-9]{2,}$/i';
	if (!preg_match($regex, $domain)) {return false;}
	return true;
}

/**
 * Checks that an email address is a valid format.
 *
 * N.B. This is NOT a check that the email EXISTS.
 * @param string
 * @return boolean
 */
public static function isEmailAddress($input) {
	return (filter_var($input, FILTER_VALIDATE_EMAIL))? true : false;
}

/**
 * Is string valid as a Google Analytics Key?
 * 
 * WARNING: This is based on rather uncertain data... it seems to fit all
 * keys tried so far, but Google may issue keys which don't fit this validation.
 * Please let us know of any which don't validate but are genuine.
 * Also, not that successful validation does not mean that the key is VALID with
 * Google, just that is fits the format of a key.
 * @param string $key
 * @return boolean
 */
public static function isGoogleAnalyticsKey($key) {
	if(preg_match('/^UA-[\d]{1,6}-[\d]{1,4}$/', $key)) {return true;}
	return false;
}

/**
 *Is a value a 'plain' numeric?
 *
 *PHP's built-in is_numeric() function also allows some more exotic values
 *such as hex, leading + chars and scientific notation.<br />
 *PHP's ctype_digit() also checks type and requires an int... this can
 *cause validation failures, particularly when using data from forms.
 *@param mixed $value String or Int to test
 *@param boolean $allowdecimal Allow decimal places
 *@param boolean $allowsigned Allow values with negative sign char
 *@return boolean
 */
public static function isNumeric($value, $allowdecimal = true, $allowsigned = true) {
	$pattern = '/^';
	if ($allowsigned) {$pattern .= '-?';}
	$pattern .= ($allowdecimal)? '\d+[\.\d+]*$/' : '[\d]+$/';
	if(preg_match($pattern, $value)) {return true;}
	return false;
}

/**
 *Is valid postcode format?
 *
 *See wikipedia for more info. Case insensitive.
 *Regex from wikipedia?
 *@param string $pc Postcode to check
 *@return boolean
*/
public static function isPostcode($pc) {
	$valid = preg_match('/(^gir\s0aa$)|(^[a-pr-uwyz]((\d{1,2})|([a-hk-y]\d{1,2})|(\d[a-hjks-uw])|([a-hk-y]\d[abehmnprv-y]))\s\d[abd-hjlnp-uw-z]{2}$)/i', $pc);
	return ($valid)? true : false;
	// BS7666 format... supposed to work, but can't get it to!
	//echo preg_match('/([A-Z]{1,2}[0-9R][0-9A-Z]? [0-9][A-Z-[CIKMOV]]{2})/i', $pc);
}

/**
 *Is valid UK Bank SortCode.
 *@param string $sortcode SortCode to check
 *@return boolean
 *@todo changed, needs checking
 */
public static function isSortCode($sortcode) {
	return (preg_match('/\d\d-\d\d-\d\d/', $sortcode))? true : false;
}

/**
* Checks whether string is 'strong'.
* 
* Fails if string is less than parsed length. Default is 8 characters.
* Fails if less than half the characters in the string are different.
* Default set to check for numeric characters fails if false
* If you expect non-ASCII compatible charactersets then dont use casechange.
* If you are using Western characterset then it is OK to use casechange
* @param string $password Password to check
* @param integer $minlength Minimum allowable length of password.
* @param boolean $numeric Fail if there aren't numeric characters in the password.
* @param boolean $casechange Check that there are upper and lower case characters.
* @returns boolean
*/
public static function isStrongPassword($password, $minlength = 8, $numeric = true, $casechange = false){
	$len = iconv_strlen($password);
	 if($len < $minlength){
		return false;
	}
	if(iconv_strlen(count_chars($password, 3)) < $len / 2){
		return false;
	}
	if($numeric){	
		$numeral = false;
		for($i = 0; $i < $len; $i++) {
			if(is_numeric($password[$i])){
				$numeral = true;
			}
		}
		if($numeral == false){
			return false;
		}
	}
	if($casechange){
		$uppercase = $lowercase = false;
		for($i = 0; $i < $len; $i++) {
			if(ctype_upper($password[$i])){
				$uppercase = true;
			}
		}
		for($i = 0; $i < $len; $i++) {
			if(ctype_lower($password[$i])){
				$lowercase = true;
			}
		}
		if($uppercase == false || $lowercase == false){
			return false;
		}
	}
	return true;	
}

/**
 * Is (UK) telephone number format.
 * 
 * Only very basic checking. Strips spaces and elipses, checks for numeric
 * and that there is a leading 0. Also checks that the length is 10 to 12 characters.
 * @param string $telno Number to validate
 * @return boolean
 */
public static function isTelephoneNumber($telno) {
	// strip out any allowable characters
	$allowablechars = array(' ', '(', ')', '+');
	foreach ($allowablechars as $char) {
		$telno = str_replace($char, '', $telno);
	}
	// see if is only numerics left
	if (!self::isNumeric($telno, false, false)) {return false;}
	
	// make sure has leading zero
	if (substr($telno, 0, 1) != '0') {return false;}
	
	// test length
	if (self::isWithinStringLength($telno, 10, 12)) {return true;}
	return false;
}

/**
 * Validate a date input in the format dd/mm/yyyy.
 * 
 * Populates ProjectError with error strings using $datename as field.
 * @param string $userdate
 * @param string $datename
 * @param ProjectError $errobj
 * @return boolean
 * @todo only validates in the format dd/mm/yyyyy!
 */
public static function isUserDate($userdate, $datename = 'Date', ProjectError $errobj = null) {
	if (iconv_strlen($userdate) != 10) {
		if ($errobj) {
			$errobj->addError("$datename is the wrong length");
		}
		return false;
	}
	
	if (preg_match('/\d\d\/\d\d\/\d\d\d\d/', $userdate) == 0) {
		if ($errobj) {
			$errobj->addError("$datename format is invalid");
		}
		return false;
	}
	
	list($day,$month,$year) = explode("/",$userdate);
	
	if (checkdate($month,$day,$year)) {return true;}
	
	if ($errobj) {$errobj->addError("$datename is not a valid date");}
	return false;
}

/**
 * Validate a time input in the format hh:mm:ss
 * 
 * Populates ProjectError with error strings using $tsname as field.
 * @param string $usertime
 * @param string $tsname
 * @param ProjectError $errobj
 * @return boolean
 */
public static function isUserTime($usertime, $tsname = 'Timestamp', ProjectError $errobj = null) {
	if(iconv_strlen($usertime) != 8) {
		if($errobj){
			$errobj->addError("$tsname is the wrong length");
		} return false;
	}

	list($hour,$min,$sec) = explode(":", $usertime);
	
	if(is_numeric($hour)){
		if (self::isWithinRange($hour, 0, 23) == false) {
			if($errobj){$errobj->addError("$tsname - Time is not Valid");}
			return false;
		}
	}
	
	if(is_numeric($min)){
		if(self::isWithinRange($min, 0, 59) == false) {
			if($errobj){$errobj->addError("$tsname - Time is not Valid");}
			return false;
		} 
	}
	
	if(is_numeric($sec)) {
		if (self::isWithinRange($sec, 0, 59) == false) {
			if($errobj){$errobj->addError("$tsname - Time is not Valid");}
			return false;
		} 
	return true;
	}
}

/**
 * Validate a timestamp input in the format dd/mm/yyyy hh:mm:ss
 * 
 * Populates ProjectError with error strings using $tsname as field.
 * @param string $date
 * @param string $tsname
 * @param ProjectError $errobj
 * @return boolean
 */
public static function isUserTimestamp($date, $tsname = '', ProjectError $errobj = null) {
	if (strpos($date, ' ') < 1) {return false;} // we need the space (not at the start) to do the explode
	list($userdate, $usertime) = explode(" ", $date);
	if (self::isUserDate($userdate, $tsname, $errobj) == false) {return false;}
	if (self::isUserTime($usertime, $tsname = "Time", $errobj) == false) {return false;}
	return true;
}

/**
 * Check whether current OS is windows.
 * @return boolean
 */
public static function isWindows() {
	return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? true : false;
}
	
/**
 * Is input within a range.
 * @param float $value Value to check
 * @param float $min Minimum allowable value
 * @param float $max Maximum allowable value
 * @return boolean
 */
public static function isWithinRange($value, $min, $max) {
	if(!is_numeric($value) || $value < $min || $value > $max) {return false;}
	return true;
}

/**
 * String is within a set number of character lengths.
 * @param string $str String to check
 * @param integer $min Minimum number of characters
 * @param integer $max Maximum number of characters
 * @return boolean
 */
public static function isWithinStringLength($str, $min, $max) {
	$len = iconv_strlen($str, 'UTF-8');
	if ($len < $min || $len > $max) {return false;}
	return true;
}

/**
 * Check YouTube video exists???
 * @param unknown $api
 * @return boolean
 * @todo Downloads whole video to check????? DELETE!?
 */
public static function isYouTubeAPI($api) {
	if(!$data = @file_get_contents('http://gdata.youtube.com/feeds/api/videos/' . $api)) {return false;}
	if($data == 'Video not found'){return false;}
	return true;
}

}
?>
