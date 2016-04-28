<?php
/**
 * Format class
 */
/**
 * Static methods providing formatting.
 * @package dstruct_common
 */
class Format {

/**
 * Class constructor.
 * Class should never be instanced - all methods should be static.
 * @throws DStructGeneralException
 */
private function __construct() {
	throw new DStructGeneralException('Format::__construct() - Trying to instantiate a class which has only static methods');
}

/**
 * Format a currency value.
 * @param float $val Currency value
 * @param string $trimdecimals Trim '.00' from end of output 
 * @param number $precision Decimal precision. Default is two.
 * @param string $symbol Symbol to prepend to output. Uses {@link Prefs::CURRENCY_SYMBOL} by default. 
 * @param string $decsep The decimal separator. Default is UK - period.
 * @param string $thousep The thousands separator. Default is UK - comma.
 * @return string
 * @todo Set default dec and thou separators in Prefs
 * @todo $trimdecimals currently doesn't take account of $decsep
 */
public static function currency($val, $trimdecimals = false, $precision = 2, $symbol = '', $decsep = '.', $thousep = ',') {
	if ($symbol === false) {
		$symbol = '';
	} elseif (!$symbol) {
		if (defined(Prefs::CURRENCY_SYMBOL)) {$symbol = Prefs::CURRENCY_SYMBOL;}
	}
	
	if ($precision !== false) {$val = Calculate::roundDecimal($val, $precision, $decsep);}
	$num = number_format($val, $precision, $decsep, $thousep);
	if ($trimdecimals) {
		// TODO: needs improving (a lot)!!
		if (substr($num, strlen($num)-3) == '.00') {$num = substr($num, 0, strlen($num)-3);}
	}
	return $symbol . $num;
}

/**
 * Formats data to more human friendly output.
 * 
 * For example, 1024 bytes --> 1kb
 * @param integer $bytes
 * @return boolean|number|string
 * @author unknown
 */
public static function dataSize($bytes) {
	//CHECK TO MAKE SURE A positive integer was provided.
	if(!Validate::isNumeric($bytes, false, false)) {return false;}
	if (!$bytes) {return 0;} // otherwise can get divide by zero error

	//SET TEXT TITLES TO SHOW AT EACH LEVEL
	$s = array('bytes', 'kb', 'MB', 'GB', 'TB', 'PB');
	$e = floor(log($bytes)/log(1024));

	//CREATE COMPLETED OUTPUT
	$output = sprintf('%.2f '.$s[$e], ($bytes/pow(1024, floor($e))));

	return $output;
}

/**
 * Format HTML Entities with UTF-8
 * 
 * As of PHP 5.4, UTF-8 is default
 * Use as a normal static method, or pass-by-ref:
 * $enc = Format::he($str); // normal
 * Format::he($enc); // pass-by-ref
 * @param string $str
 * @return string
 */
public static function he(&$str) {
    $str = htmlentities($str, ENT_QUOTES, 'UTF-8');
    return $str;
}

/**
 * Format HTML Special Characters with UTF-8
 *
 * As of PHP 5.4, UTF-8 is default
 * Use as a normal static method, or pass-by-ref:
 * $enc = Format::hsc($str); // normal
 * Format::hsc($enc); // pass-by-ref
 * @param string $str
 * @return string
 */
public static function hsc(&$str) {
    $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    return $str;
}

/**
 *Fast and dirty formatting of a string (typically from a database) for (X)HTML compatability.
 *
 *Return is HTML encoded and newlines are converted to<pre><br /></pre>.
 *Method is set to use UTF-8.
 *@param string
 *@return string
 */
public static function toHTML($input) {
	$output = htmlentities($input, ENT_QUOTES, 'UTF-8');
	return nl2br($output);
}

/**
 * Format UTS as full date.
 * @param integer $uts
 * @return string
 * @see Prefs::FORMAT_DISPLAY_DATE
 */
public static function date($uts) {return date(Prefs::FORMAT_DISPLAY_DATE, $uts);}

/**
 * Format UTS as full timestamp.
 * @param integer $uts
 * @return string
 * @see Prefs::FORMAT_DISPLAY_TS
 */
public static function timestamp($uts) {return date(Prefs::FORMAT_DISPLAY_TS, $uts);}

/**
 * Format UTS short date.
 * @param integer $uts
 * @return string
 * @see Prefs::FORMAT_FORM_DATE
 */
public static function formDate($uts) {return date(Prefs::FORMAT_FORM_DATE, $uts);}

/**
 * Format UTS short timestamp.
 * @param integer $uts
 * @return string
 */
public static function formTimestamp($uts) {return date(Prefs::FORMAT_FORM_TS, $uts);}

/**
 * Return a date in format yyyymmdd.
 * 
 * Particularly useful for where there is a need to sort by date, for example,
 * naming folders which are to be listed by their date.
 * @param integer $uts
 * @return string
 */
public static function orderedDate($uts = false) {
	if (!$uts) {$uts = time();}
	return (int) date('Ymd', $uts);
}

/**
 *Formats html message box.
 *
 *If a string is passed as $message, a div with the $message string is returned.<br>
 *If an array is passed, and un-ordered list of the variables is returned.<br>
 *Boxes have the class 'message', in addition to the default 'error' class or whatever is passed in its place.<br>
 *If an empty string is passed, nothing is returned, rather than an empty box.
 *@param mixed $messages
 *@param string $class CSS class for the message box
 *@return string
 */
public static function asMessage($messages, $class = 'error') {
	if ($messages == false) {return;}
	if (is_array($messages)) {
		$output = "<ul class='message $class'>\n";
		foreach($messages as $message) {
			$output .= "<li>$message</li>\n";
		}
		return $output . "</ul>";
	} else {
		return "<div class='message $class'>\n$messages\n</div>";
	}
}

}
?>