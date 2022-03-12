<?php
/**
 * Calculate class
 */
/**
 * Static methods providing calculations.
 * @package dstruct_common
 */
class Calculate {

/**
 * Return the total.
 * @var integer
 * @see grossFromNet()
 * @see netFromGross()
 */
const GROSSNET_TOTAL = 0;

/**
 * Return the difference.
 * @var integer
 * @see grossFromNet()
 * @see netFromGross()
 */
const GROSSNET_DIFFERENCE = 1;

/**
 * Class constructor.
 * @throws DStructGeneralException
 */
private function __construct() {
	throw new DStructGeneralException('Calculate::__construct() - Trying to instantiate a class which has only static methods');
}

/**
 * Get the number of bytes in a data size.
 * 
 * Particularly useful when parsing parameters from php.ini. The
 * method was converted from one from {@link http://www.php.net/manual/en/function.ini-get.php}.
 * <var>$val</var> should be expressed as a float followed by
 * 'k', 'm' or 'g' (not case sensitive) to denote the size 'scale'. Return
 * is an integer, rounded where necessary.
 * e.g. 1k returns 1024
 * 1.2k returns 1229
 * @param string $val
 * @return integer
 */
public static function bytes($val) {
	$val = trim($val);
	//$last = strtolower($val[strlen($val)-1]);
	$last = strtolower(substr($val,strlen($val/1),1));
	switch($last) {
		case 'g':
			$val *= 1073741824;
			break;
		case 'm':
			$val *= 1048576;
			break;
		case 'k':
			$val *= 1024;
			break;
	}

	return (int) round($val, 0);
}

/**
 *Calculates differences between dates.
 *
 *WARNING: This method may well have bugs. It is better to use strtotime() or the Date object! It is likely
 *to be deprecated in future.
 *Interval can be one of:
 *y = year, q = quarter, m = month, w = week, d = day, 
 *h = hour, m = minute, s = second.
 *Combined values can be send to $interval such as 1q or 3y. In these instances,
 *$number is ignored.
 *@param string $interval Intervals to be counted in or combined value (e.g. 1y, 2q)
 *@param integer $number Number of intervals to count
 *@param integer $date UTS date
 *@return integer
 *@todo Handle incorrect parameters
 */
public static function dateDiff($interval, $number = false, $date = false) {
	if (!$date) {$date = time();}
	// if not a plain alpha, then assume it is a combined value like 1q or 3y
	if (!Validate::isAlpha($interval)) {
		$number = substr($interval, 0 , strlen($interval) - 1);
		$interval = substr($interval, strlen($interval) - 1, 1);
	}
    $date_time_array = getdate($date);
    $hours = $date_time_array['hours'];
    $minutes = $date_time_array['minutes'];
    $seconds = $date_time_array['seconds'];
    $month = $date_time_array['mon'];
    $day = $dayoriginal = $date_time_array['mday'];
    $year = $date_time_array['year'];
	
    switch ($interval) {
        case 'y':
            $year+=$number;
            break;
        case 'q':
            $month+=($number*3);
            break;
        case 'm':
            $month+=$number;
            break;
        case 'd':
            $day+=$number;
            break;
        case 'w':
            $day+=($number*7);
            break;
        case 'h':
            $hours+=$number;
            break;
        case 'm':
            $minutes+=$number;
            break;
        case 's':
            $seconds+=$number;
            break;            
    }
	
	$timestamp= mktime($hours,$minutes,$seconds,$month,$day,$year);
	
	// Calculate::DateDiff('q', 1, mktime(0,0,0,11,30,2008));
	// which should give 28th Feb 2009 actually gives 2nd May unless we do this...
	$date_time_array = getdate($timestamp);
	if ($date_time_array['mday'] < $day) {
		//echo $date_time_array['mday'];
		$timestamp= mktime($hours,$minutes,$seconds,$month + 1,0,$year);
	}
	
    return $timestamp;
}

/**
 * Calculates the Gross (eg inc VAT) value from the Net (eg ex VAT).
 * 
 * @param float $net Net value
 * @param float $rate Percentage rate to use for calculations
 * @param integer $resultflag See class constants. Return Total, Difference or array with both
 * @param mixed $round Places to round to. Integer or False
 * @return mixed Float or Array. See above.
 */
public static function grossFromNet($net, $rate, $resultflag = false, $round = 2) {
	if (!$rate) {return $net;}
	if (!$net) {return 0;}
	$multiplier = ($rate / 100) + 1;
	$total = $net * $multiplier;
	if ($round !== false) {$total = Calculate::roundDecimal($total, $round);}
	if ($resultflag !== false) {
		switch ($resultflag) {
			case 0: // total
				return $total;
				break;
			case 1: // difference
				return $total - $net;
				break;
		}
	} else {
		return array('total' => $total, 'diff' => $total - $net);
	}
}

/**
 *Get the name of an array key.
 *@param array $a Subject
 *@param integer $pos Key position
 *@return string Name of the key
 */
public static function keyName(array $a, $pos) {
    $temp = array_slice($a, $pos, 1, true);
    return key($temp);
}

/**
 *Calculates the Net (eg ex VAT) value from the Gross (eg inc VAT).
 *
 *If <var>$diff</var> is true, an array is returned with keys 'total' and 'diff'.
 *'total' is the gross value and 'diff' is the difference between net and gross.<br />
 *If <var>$diff</var> is false, returns float
 *@param float $gross Gross value
 *@param float $rate Percentage rate to use for calculations
 *@param integer $resultflag See class constants. Return Total, Difference or array with both
 *@param mixed $round Places to round to. Integer or False
 *@return mixed Float or Array. See above.
 */
public static function netFromGross($gross, $rate, $resultflag = false, $round = 2) {
	if (!$rate) {return $gross;}
	if (!$gross) {return 0;}
	$total = ($gross / ($rate + 100)) * 100;
	if ($round !== false) {$total = Calculate::roundDecimal($total, $round);}
	if ($resultflag !== false) {
		switch ($resultflag) {
			case 0: // total
				return $total;
				break;
			case 1: // difference
				return $gross - $total;
				break;
		}
	} else {
		return array('total' => $total, 'diff' => $gross - $total);
	}
}

/**
 *Calculates rounding intuitively.
 *
 *Protects from floating point rounding errors. Versions of PHP earlier than XXXX? were prone
 *to this. I think later versions are 'ok'.
 *Not sure where this function came from.
 *@param float $number Value to round
 *@param integer $precision Precision to round to (decimal places)
 *@param string $decsep Decimal Separator
 *@return float
 *@todo complete long description
 */
public static function round($number, $precision=2, $decsep = '.')
{
	$negative = false;
    $number = sprintf("%s",sprintf("%f",$number));
    $parts = explode($decsep,$number);
    $integers = $parts[0];
    $decimals = $parts[1];
   
    if (strpos($integers,'-')!==false){
        $integers = str_replace('-','',$integers);
        $negative = true;
    }
   
    if($precision<1) {
        if ($decimals{0}>=5)
            $integers++;    
			
        return $integers;
    } else {
        $float = explode($decsep,($decimals/pow(10,strlen($decimals)))*pow(10,$precision));
        $my_decimals = $float[0];
        if($decimals{$precision}>=5)
            $my_decimals++;
			
        $total_decimals = ($my_decimals/pow(10,$precision));
    }

    $my_number     =      sprintf("%s",sprintf("%f",($integers+$total_decimals)));
    $padding    =     (strpos($my_number,$decsep)+1)+$precision;
    $my_number    =     str_pad($my_number,$padding,'0');
    $my_number    =    substr($my_number,0,strpos($my_number,$decsep)+$precision+1);
    $my_number     =     $negative?('-'.$my_number):$my_number;
   
    return $my_number;   
}

}
?>