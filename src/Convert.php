<?php
namespace pub007\dstruct;
/**
 * Convert class
 */
/**
 * Static methods providing conversions.
 * @package dstruct_common
 */
class Convert {

/**
 * Class constructor
 * @throws DStructGeneralException
 */
private function __construct() {
	throw new DStructGeneralException('Convert::__construct() - Trying to instantiate a class which has only static methods');
}

/**
 *Converts a MySQL date to a Unix Timestamp
 *
 *For compatability with Windows languages, a date of 1900-01-01 returns 
 *0 which is 1970-01-01 01:00:00<br>
 *The function uses PHP's strtotime(), which returns false for dates before the start
 *of the Unix Epoch in 1970 on Windows. If a date is before 1970, the function will return
 *0 as above.<br>
 *To ensure consistency in the database, this function should be used in conjunction
 *with {@link UTSToMySQLTimestamp()}.
 *@param string Format of 'yyyy-mm-dd'
 *@return integer
 */
public static function MySQLDateToUTS($date) {
	if ($date == '1900-01-01' || $date == '1970-01-01') {return 0;}
	$result = strtotime($date);
	if ($result) {
		return $result;
	} else {
		return 0;
	}
}

/**
 *Converts a MySQL Timestamp to a Unix Timestamp
 *
 *For compatability with Windows languages, a timestamp of 1900-01-01 00:00:00 returns 
 *0 which is 1970-01-01 01:00:00<br>
 *The function uses PHP's strtotime(), which returns false for timestamps before the start
 *of the Unix Epoch in 1970 on Windows. If a timestamp is before 1970, the function will return
 *0 as above.<br>
 *To ensure consistency in the database, this function should be used in conjunction
 *with {@link UTSToMySQLDate()}.
 *@param string $stamp Format of 'yyyy-mm-dd hh:mm:ss'
 *@return integer
 */
public static function MySQLDateTimeToUTS($stamp) {
	if ($stamp == '1900-01-01 00:00:00' || $stamp == '1970-01-01 01:00:00') {return 0;}
	$result = strtotime($stamp);
	if ($result) {
		return $result;
	} else {
		return 0;
	}
}

/**
 * Converts a string of six numeric characters into a UK Sort Code
 * 
 * Remember to store your numeric characters as a string rather than integer
 * so that leading zeros are preserved
 * @see Convert::sortCodeToNumeric()
 * @param string $numeric expects format nnnnnn
 * @return string in format nn-nn-nn
 */
public static function numericToSortCode($numeric) {
	$bits = str_split($numeric, 2);
	return implode('-', $bits);
}

/**
 * Converts a formatted UK Sort Code to a numeric string
 * 
 * @see Convert::numericToSortCode()
 * @param string $code in format nn-nn-nn
 * @return string in format nnnnnn
 * @throws DStructGeneralException
 */
public static function sortCodeToNumeric($code) {
	$result = str_replace('-', '', $code);
	if (!Validate::isNumeric($result, false, false)) {throw new DStructGeneralException('Convert::sortCodeToNumeric() - SortCode does not appear valid');}
	if (strlen($result) != 6) {throw new DStructGeneralException('Convert::sortCodeToNumeric() - SortCode does not appear to be the correct length:' . strlen($result));}
}

/**
 *Gets the date part of a UTS
 *If a UTS needs to be compared (for instance finding in an array), it may be desirable to
 *attempt to match days e.g. 25/08/2008 to 25/08/2008. If one or more of the UTS vars
 *is a timestamp, this won't work if they are not converted to a 'day' timestamp
 *@param integer $uts UTS timestamp
 *@return integer UTS timestamp
 */
public static function UTSDatePart($uts) {
	$output = date('d F Y', $uts);
	return strtotime($output);
}

/**
 *Converts a Unix Timestamp to a MySQL date
 *
 *If <var>$uts</var> = false, returns '1900-01-01' or 1970-01-01. This
 *is to maintain compatability with databases which have to be usable with Windows
 *programming languages which error when confronted with 0000-00-00 dates.
 *@param integer $uts
 *@param boolean $olddate Return 1900 rather than 1970 if true.
 *@return string Format 'yyyy-mm-dd hh:mm:ss'
 */
public static function UTSToMySQLDate($uts, $olddate = false) {
	if ($uts == false) {
		if ($olddate == true){
			return '1900-01-01';
		}
		return '1970-01-01';
	} 
	return date('Y-m-d',$uts);
}

/**
 *Converts a Unix Timestamp to a MySQL DateTime
 *
 *For MySQL Timestamp fields, you can just insert the UTS without conversion.
 *@param integer $uts UTC Timestamp
 *@param boolean $olddate Return 1900 rather than 1970 dates when $uts evaluates to false
 *@return string Format 'yyyy-mm-dd hh:mm:ss'
 */
public static function UTSToMySQLDateTime($uts, $olddate = false) {
	if (!$uts) {
		if ($olddate == true){
			return '1900-01-01 00:00:00';
		}
		return '1970-01-01 01:00:00';
	}
	return date('Y-m-d H:i:s', $uts);
}

/**
 * Get just the date part of a UTS in MySQL format
 * 
 * @param integer $uts
 * @return string Time in format 'nn:nn:nn'
 */
public static function UTSToMySQLTime($uts) {
	if (!$uts) {return '1970-01-01 01:00:00';}
	return date('H:i:s', $uts);
}
/**
 *Converts a 'natural' date into a Unix Timestamp
 *
 *The date <b>must</b> be valid.
 *@param string Format 'dd/mm/yyyy'
 *@return integer
 */
public static function UserDateToUTS($date) {
	list($dd,$mm,$yyyy)=explode("/",$date);
	$date = "$yyyy-$mm-$dd";
	return strtotime($date);
}

/**
 * Converts a 'natural' date to MySQL format
 * 
 * @param string $userdate in format 'dd/mm/yyyy'
 * @return string in format 'yyyy-mm-dd'
 */
public static function UserDateToMySQL($userdate) {
	return self::UTSToMySQLDate(self::UserDateToUTS($userdate));	
}

/**
 *Converts a 'natural' timestamp into a Unix Timestamp
 *
 *The timestamp <b>must</b> be valid.
 *@param string Format 'dd/mm/yyyy hh:mm:ss'
 *@return integer
 */
public static function UserTimestampToUTS($timestamp) {
	list($date,$time) = explode(" ",$timestamp);
	list($dd,$mm,$yyyy)=explode("/",$date);
	$date = "$yyyy-$mm-$dd";
	return strtotime($date.' '.$time);
}

}
?>