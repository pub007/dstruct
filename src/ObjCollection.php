<?php
namespace pub007\dstruct;
/**
 * ObjCollection class
 */
/**
 *Provides functionality for Object Collections.
 *
 *Any objects used with this class must support Object::getID().
 *Extension of this class allows collections of objects to be collated, used and manipulated in a
 *convenient and consistent manner.
 *The child class is responsible for loading the objects and will often provide function(s) for loading and deleting objects.
 *An example function for loading all records as objects into the collection:
 *<code>
 *public function loadAll() {
 *	parent::clear(); // clears any objects in collection
 *	$rs = DataManager::loadObjects(); // load id's from the database to a var
 *	foreach ($rs as $row) {
 *		parent::add(ObjectClass::loadByID($row[0])); // load objects into this class. This may vary with the actual class method being called.
 *	}
 *}
 *</code>
 *<br />
 *@package dstruct_common
 */
abstract class ObjCollection implements Iterator {

/**
 * Sort ascending.
 * @var integer
 */
const SORT_OBJECTS_ASC = 1;

/**
 * Sort descending.
 * @var integer
 */
const SORT_OBJECTS_DESC = -1;

/**
 *Stores objects.
 *@var array
 */
public $objs = array();

/**
 *Array of objects.
 *@return array
 */
public function getArray() {return $this->objs;}

/**
 *Number of objects currently in collection
 *@return integer
 */
public function count() {return sizeof($this->objs);}

/**
 *Add an object to the collection.
 *
 *If the object exists, a DStructGeneralException will be thrown
 *@throws DStructGeneralException
 *@param object $obj
 *@return boolean
 */
public function add($obj) {
	if (!is_object($obj)) {throw new DStructGeneralException('ObjCollection::add() - Expecting object');}
	$id = $obj->getID();
	if (!is_integer($id) && !is_string($id)) {
	    throw new DStructGeneralException("ObjCollection::add() - getID() must return string or integer. Returned type: " . gettype($id));
	}
	if ($this->exists($obj)) {
		throw new DStructGeneralException('ObjCollection::add() - Attempting to add an object which already exists. ID:'.$obj->getID());
		return false;
	} else {
		$this->objs[$id] = $obj;
		return true;
	}
}

/**
 *Remove an object from the collection.
 *
 *If the object is not in the collection, a DStructGeneralException will be thrown
 *@throws DStructGeneralException
 *@param mixed $obj Object, or its ID
 *@return boolean
 */
public function remove($obj) {
	$objid = (is_object($obj))? $obj->getID() : $obj;
	if ($this->exists($objid)) {
		unset($this->objs[$objid]);
		return true;
	} else {
		throw new DStructGeneralException('ObjCollection::remove() - Attempting to remove an object which is not part of the collection');
		return false;
	}
}

/**
 *Clear the collection.
 */
protected function clear() {$this->objs = array();}

/**
 *Test whether an object exists in the collection.
 *@param mixed $obj Object, or it's ID
 *@return boolean
 */
public function exists($obj) {
    if (!is_object($obj)) {throw new DStructGeneralException("ObjCollection::exists() - expecting Object. Found " . gettype($obj));}
    $id = (is_object($obj))? $obj->getID() : $obj;
	if (!is_string($id) && !is_integer($id)) {
	    throw new DStructGeneralException("ObjCollection::add() - getID() must return string or integer. Returned type: " . gettype($id));
	}
	return (array_key_exists($id, $this->objs))? true : false;
}

/**
 *Returns an object from the collection based on its ID.
 *@param integer $id
 *@return object|false
 */
public function getByID($id) {
	if (array_key_exists($id, $this->objs)) {
		return $this->objs[$id];
	} else {
		return false;
	}
}

/**
 *Returns the first object in the collection.
 *@return object|false
 */
public function getFirst() {
	if (!$this->count()) {return false;}
	foreach ($this->objs as $obj) {
		return $obj;
	}
}

// ==================  SORT - Sort Objects by attribute or method ===========

/**
 * Callback used by usort in {@link sortObjects()} to sort by the return from an objects method.
 * @param object $a
 * @param object $b
 * @return integer 0 or 1
 */
private static function csort_cmp_method(&$a, &$b)
{
    global $csort_cmp;
	$paramstring = '';
	
	if ($csort_cmp['params']) {
		foreach ($csort_cmp['params'] as $param) {
			$paramstring .= $param . ', ';
		}
		// remove last ', '
		$paramstring = substr($paramstring, 0, strlen($paramstring) - 2);
	}
	
	$keya = $a->$csort_cmp['key']($paramstring);
	$keyb = $b->$csort_cmp['key']($paramstring);
	
	if ($csort_cmp['astime']) {
		$keya = strtotime($keya);
		$keyb = strtotime($keyb);
	}
	
	if ($csort_cmp['caseinsensitive'] && $csort_cmp['astime'] == false) {
		switch ($result = strcasecmp($keya, $keyb)) {
			case $result == 0:
				return 0;
				break;
			case $result < 0:
				return -1 * $csort_cmp['direction'];
				break;
			default:
				return $csort_cmp['direction'];
				break;
		}
	} else {
		if ($keya > $keyb)
			return $csort_cmp['direction'];
	
		if ($keya < $keyb)
			return -1 * $csort_cmp['direction'];
	
		return 0;
	}
}

/**
 * Callback used by usort in {@link sortObjects()} to sort by objects attributes
 * @param object $a
 * @param object $b
 * @return integer 0 or 1
 */
private static function csort_cmp_attribute(&$a, &$b)
{
    global $csort_cmp;
	
	$keya = $a->$csort_cmp['key'];
	$keyb = $b->$csort_cmp['key'];
	
	if ($csort_cmp['astime']) {
		$keya = strtotime($keya);
		$keyb = strtotime($keyb);
	}
	
	if ($csort_cmp['caseinsensitive'] && $csort_comp['astime'] == false) {
		switch ($result = strcasecmp($keya, $keyb)) {
			case $result == 0:
				return 0;
				break;
			case $result < 0:
				return -1 * $csort_cmp['direction'];
				break;
			default:
				return $csort_cmp['direction'];
				break;
		}
	} else {
		if ($keya > $keyb)
			return $csort_cmp['direction'];
	
		if ($a->$keya < $keyb)
			return -1 * $csort_cmp['direction'];
	
		return 0;
	}
}

/**
 *Sorts objects by attribute or return from a method.
 *
 *<b>WARNING:</b> This method may be slow! It is advisable to use another method if one is available,
 *for instance, using ORDER BY in an SQL statement to create and add the objects in order.<br />
 *The <var>$element</var> parameter can specify either an attribute or a method of
 *the objects in the collection. Methods should be named in full e.g. "method()".<br />
 *Although this uses the PHP function usort(), the array keys <i>are</i> preserved.<br />
 *The constants {@link SORT_OBJECTS_ASC} and {@link SORT_OBJECTS_DESC} are provided
 *to set the <var>$sort_direction</var> parameter.<br />
 *If you set <var>$astime</var> to true, the method will try to use PHP's strtotime() function to
 *attempt to convert strings into time, allowing you to sort dates in the expected order. You must
 *ensure that all data will convert to strtotime, or that data will be evalutated as false!
 *<br />
 *Example:<br />
 *<code>
 *class Employee {
 *public $name;
 *public function __construct($name) {$this->name = $name;}
 *public function getName() {return $this->name;}
 *}
 *
 *$employees = new Employees; // create collection object
 *$employees->add(new Employee('neil'));
 *$employees->add(new Employee('David'));
 *$employees->add(new Employee('Shane'));
 *
 * // order by name attribute
 *$employees->sortObjects('name');
 * // new order: David, neil, Shane
 *
 * // order by method return, and case sensitive, and passing a single parameter of true to the method
 *$employees->sortObjects('getName()', Employees::SORT_ORDERS_ASC, false, array(true));
 * // new order: David, Shane, neil
 *</code>
 *@param string $element Element of the objects to sort by
 *@param integer $sort_direction 1 (ascending) or -1 (descending). See above.
 *@param boolean $caseinsensitive Case sensitivity of sort.
 *@param array $params Parameters to be passed on if calling a method
 *@param boolean $astime Try to use strtotime() to sort by date
 *@todo If $element = getID(), use different sort
 */
public function sortObjects($element, $sort_direction=SORT_OBJECTS_ASC, $caseinsensitive = true, $params = '', $astime = false)
{
	global $csort_cmp;
	// create an array with settings to be used by csort_comp()
    $csort_cmp = array(
        'direction'     => $sort_direction,
		'caseinsensitive'   => $caseinsensitive,
		'params' => $params,
		'astime' => $astime);
	
	// sort the object
	// cant just pass "method()", so we need to use 2 different callbacks to sort
	if (substr($element, -2, 2) == '()') { // if ends in () sort by method
		$csort_cmp['key'] = substr($element, 0, strlen($element) -2);
    	usort($this->objs, array('ObjCollection', "csort_cmp_method"));
	} else { // sort by attribute
		$csort_cmp['key'] = $element;
		usort($this->objs, array('ObjCollection', "csort_cmp_attribute"));
	}
	
	// prevent warning if no $objs found
	$temparray = array();
	
	//rebuild keys
	foreach ($this->objs as $obj) {
		$temparray[$obj->getID()] = $obj;
	}
	$this->objs = $temparray;
	
	unset ($temparray);
    unset($csort_cmp);
}

//=================== SORT - End ==================================
/**
 * Get previous object in collection.
 * @return mixed object or false.
 */
public function prev() {return prev($this->objs);}

// extends iterator, so need...
/**
 * (non-PHPdoc)
 * @see Iterator::valid()
 */
public function valid() {return (!is_null(key($this->objs)));}

/**
 * (non-PHPdoc)
 * @see Iterator::rewind()
 */
public function rewind() {reset($this->objs);}

/**
 * (non-PHPdoc)
 * @see Iterator::current()
 */
public function current() {return current($this->objs);}

/**
 * (non-PHPdoc)
 * @see Iterator::key()
 */
public function key() {return key($this->objs);}

/**
 * (non-PHPdoc)
 * @see Iterator::next()
 */
public function next() {return next($this->objs);}
}

?>