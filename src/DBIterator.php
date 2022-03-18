<?php
namespace pub007\dstruct;
/**
 * DBIterator class
 */
/**
 * Wraps database result set functionality.
 *
 * Gets results from PDO and turns them into arrays.<br />
 * Works with statements and queries.<br />
 * This also means that can use things like count() as PDO
 * doesn't always automatically return a recordset count.<br />
 * Also allows a different return modes.<br />
 * @package dstruct_common
 * @todo Gives a count of 1 when returns just an empty array?!
 */
class DBIterator implements Iterator {

/**
 *Total results returned.
 *@var integer
 */
private $total = 0;

/**
 *Recordset returned.
 *@var array
 */
private $rs = array();

/**
 *PDO::FETCH_xx mode used to fetch the recordset.
 *@var integer
 */
private $mode = 0;

/**
 *Class Constructor.
 *<var>$mode</var> is one of PDO::FETCH_xx constants, and defaults
 *to returning an associative array.
 *@param object $result PDO::Statement handle or PDO::Query result
 *@param integer $mode Array return mode. PDO::FETCH_xx constant.
 */
public function __construct($result, $mode = PDO::FETCH_ASSOC) {
	if (!$result) {
		$this->rs = array();
		return;
	}
	$this->rs = $result->fetchAll($mode);
	$this->total = count($this->rs);
	$this->mode = $mode;
}

/**
 *Returns the mode used to get the data.
 *
 *Mode is PDO::FETCH_xx constant
 *@return integer
 */
public function getMode() {return $this->mode;}

/**
 *Alias of {@link count()}.
 *
 *Sort-of mimics mysqli
 *@return integer
 */
public function num_rows() {
	return $this->count();
}

/**
 *Return count of records.
 *@return integer
 */
public function count() {
	return $this->total;
}

// extends iterator, so need...
/**
 * Rewind.
 * @see Iterator::rewind()
 */
public function rewind() {reset($this->rs);}

/**
 * Get current record.
 * @return string
 * @see Iterator::current()
 */
public function current() {return current($this->rs);}

/**
 * Get key of current record.
 * @return string
 * @see Iterator::key()
 */
public function key() {return key($this->rs);}

/**
 * Move to next record.
 * @see Iterator::next()
 */
public function next() {return next($this->rs);}

/**
 * Is there a record at the current position.
 * @see Iterator::valid()
 */
public function valid() {return $this->current() !== false;}
}
?>