<?php
namespace pub007\dstruct;
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
 * Calculates the Gross (eg inc VAT) value from the Net (eg ex VAT).
 * 
 * @param float $net Net value
 * @param float $rate Percentage rate to use for calculations
 * @param integer $resultflag See class constants. Return Total, Difference or array with both
 * @param mixed $round Places to round to. Integer or False
 * @return mixed Float or Array. See above.
 */
public static function grossFromNet($net, $rate, $resultflag = false, $round = 2) {
	if (!$rate) {
		return $net;
	}
	if (!$net) {
		return 0;
	}
	$multiplier = ($rate / 100) + 1;
	$total = $net * $multiplier;
	if ($round !== false) {
		$total = Calculate::roundDecimal($total, $round);
	}
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
		return [
			'total' => $total,
			'diff' => $total - $net
		];
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
	if (!$rate) {
		return $gross;
	}
	if (!$gross) {
		return 0;
	}
	$total = ($gross / ($rate + 100)) * 100;
	if ($round !== false) {
		$total = Calculate::roundDecimal($total, $round);
	}
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
		return [
			'total' => $total,
			'diff' => $gross - $total
		];
	}
}

}
?>