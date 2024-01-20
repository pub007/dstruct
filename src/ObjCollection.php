<?php
namespace pub007\dstruct;

/**
 * Provides functionality for Object Collections.
 *
 * Any objects used with this class must support Object::getID().
 * Extension of this class allows collections of objects to be collated, used and manipulated in a
 * convenient and consistent manner.
 * The child class is responsible for loading the objects and will often provide function(s) for loading and deleting objects.
 * An example function for loading all records as objects into the collection:
 * <code>
 * public function loadAll() {
 * parent::clear(); // clears any objects in collection
 * $rs = DataManager::loadObjects(); // load id's from the database to a var
 * foreach ($rs as $row) {
 * parent::add(ObjectClass::loadByID($row[0])); // load objects into this class. This may vary with the actual class method being called.
 * }
 * }
 * </code>
 * <br />
 *
 * @author David Lidstone
 * @package pub007/dstruct
 */
abstract class ObjCollection implements \Iterator, \Countable
{
	
	/**
	 * Sort ascending.
	 *
	 * @var integer
	 */
	const SORT_OBJECTS_ASC = 1;
	
	/**
	 *
	 * Sort descending.
	 *
	 * @var integer
	 */
	const SORT_OBJECTS_DESC = - 1;
	
	/**
	 * Stores objects.
	 *
	 * @var array
	 */
	public $objs = [];

	protected $strictClassName = null;
	
	protected $strictMode = false;
	
	/**
	 * Array of objects.
	 *
	 * @return array
	 */
	public function getArray(): array
	{
		return $this->objs;
	}
	
	/**
	 * Number of objects currently in collection
	 *
	 * @return integer
	 */
	public function count(): int
	{
		return count($this->objs);
	}
	
	/**
	 * Add an object to the collection.
	 *
	 * Returns true if successful or the object already exists.
	 * If the collection is set to strict mode, checks the object is of the correct
	 * type. If not, and Exception will be thrown.
	 * If in strict mode but this is the first object added, the collection will
	 * now be locked to only accept the same type of object.
	 *
	 * @throws \Exception
	 * @param object $obj
	 * @return boolean
	 */
	public function add(object $obj): bool
	{
		if ($this->exists($obj)) {
			return true;
		}
		$key = $this->generateKey($obj); // does strict checks
		$this->objs[$key] = $obj;
		return true;
	}
	
	/**
	 * Remove an object from the collection.
	 *
	 * If the object is not in the collection, an \Exception will be thrown
	 *
	 * @throws \Exception
	 * @param mixed $obj
	 *        	Object, or its ID
	 * @return boolean
	 */
	public function remove(mixed $member, string $className = ''): bool
	{
		if ($this->exists($member, $className)) {
			if (is_object($member)) {
				$key = $this->generateKey($member);
			} else {
				$key = $className . "_" . $member;
			}
			unset($this->objs[$key]);
			return true;
		}
		return false;
	}
	
	/**
	 * Clear the collection.
	 */
	public function clear(): void
	{
		$this->objs = [];
	}
	
	/**
	 * Test whether an object exists in the collection.
	 *
	 * @param mixed $member
	 *        	Object, or its ID
	 * @param string $className
	 *        	Required if collection is set to strict
	 * @return boolean
	 * @throws DStructGeneralException
	 */
	public function exists($member, string $className = ''): bool
	{
		if (is_object($member)) {
			$key = $this->generateKey($member);
		} else {
			if ($this->strictClassName && ! $className) {
				throw new DStructGeneralException("ObjCollection::exists() - className is required if collection is set to strict");
			}
			if (! is_string($member) && ! is_integer($member)) {
				throw new DStructGeneralException("ObjCollection::exists() - member must return string or integer. Returned type: " . gettype($member));
			}
			$key = $className . "_" . $member;
		}
		return (array_key_exists($key, $this->objs)) ? true : false;
	}
	
	/**
	 * Returns an object from the collection based on its ID.
	 *
	 * @param integer|string $id
	 * @return object|false
	 */
	public function getByID(int $id): mixed
	{
		if (array_key_exists($id, $this->objs)) {
			return $this->objs[$id];
		}
		return false;
	}
	
	/**
	 * Returns the first object in the collection.
	 *
	 * @return ?object
	 */
	public function getFirst(): ?object
	{
		if (! $this->count()) {
			return null;
		}
		return $this->objs[array_key_first($this->objs)];
	}
	
	protected function generateKey(object $obj): string
	{
		$id = $obj->getID();
		$className = get_class($obj);
		
		if (! is_integer($id) && ! is_string($id)) {
			throw new DStructGeneralException("ObjCollection::generateKey() - getID() must return string or integer. Returned type: " . gettype($id));
		}
		
		if ($this->strictMode) {
			if (! $this->strictClassName) { // set the strict class to the first class we see
				$this->strictClassName = get_class($obj);
			} else {
				if ($this->strictClassName != $className) {
					throw new DStructGeneralException("ObjCollection::generateKey() - Strict Class Name did not match. Received [$className]");
				}
			}
		}
		
		return $className . "_" . $id;
	}
	
	public function isStrictMode(): bool
	{
		return $this->strictMode;
	}
	
	// ================== SORT - Sort Objects by attribute or method ===========
	
	/**
	 * Callback used by usort in {@link sortObjects()} to sort by the return from an objects method.
	 *
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
					return - 1 * $csort_cmp['direction'];
					break;
				default:
					return $csort_cmp['direction'];
					break;
			}
		} else {
			if ($keya > $keyb) {
				return $csort_cmp['direction'];
			}
			if ($keya < $keyb) {
				return - 1 * $csort_cmp['direction'];
			}
			return 0;
		}
	}
	
	/**
	 * Callback used by usort in {@link sortObjects()} to sort by objects attributes
	 *
	 * @param object $a
	 * @param object $b
	 * @return integer 0 or 1
	 */
	private static function csort_cmp_attribute(&$a, &$b): int
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
					return - 1 * $csort_cmp['direction'];
					break;
				default:
					return $csort_cmp['direction'];
					break;
			}
		} else {
			if ($keya > $keyb) {
				return $csort_cmp['direction'];
			}
			if ($a->$keya < $keyb) {
				return - 1 * $csort_cmp['direction'];
			}
			return 0;
		}
	}
	
	public function getStrictClassName(): ?string
	{
		return $this->strictClassName;
	}
	
	/**
	 * Pop the object off the end of the collection
	 *
	 * @param Object $obj
	 */
	public function pop(): object
	{
		return array_pop($this->objs);
	}
	
	/**
	 *
	 * @see ObjCollection::add()
	 */
	public function push(object $obj): bool
	{
		return $this->add($obj);
	}
	
	public function setStrictClassName(string $name)
	{
		if ($this->strictClassName && $this->strictClassName != $name) {
			throw new DStructGeneralException("ObjCollection::setStrictClassName() - Strict Class Name already set to [{$this->strictClassName}] Received [$name]");
		}
		// if strict class is set on a collection which is already populated,
		// ensure that there are no incorrect objects in the collection
		foreach ($this->objs as $obj) {
			if (get_class($obj) != $name) {
				throw new DStructGeneralException("ObjCollection::setStrictClassName() - Collection already contains mixed classes");
			}
		}
		$this->strictMode = true;
		$this->strictClassName = $name;
	}
	
	public function setStrictMode(): void
	{
		if ($this->strictMode) {
			return;
		}
		if (count($this->objs) && ! $this->strictClassName) {
			foreach ($this->objs as $obj) {
				if (! $this->strictClassName) {
					$this->strictClassName = get_class($obj);
					continue;
				}
				if (get_class($obj) != $this->strictClassName) {
					throw new DStructGeneralException("ObjCollection::setStrictMode() - Collection already contains mixed classes");
				}
			}
		}
		$this->strictMode = true;
	}
	
	/**
	 * Shift an object off the start of the collection
	 *
	 * @return object
	 */
	public function shift(): object
	{
		return array_shift($this->objs);
	}
	
	public function unshift(object $obj): void
	{
		// we only want one copy in this collection!
		// remove will just return false if it doesn't exist
		$this->remove($obj);
		$key = $this->generateKey($obj);
		$toAdd = [
			$key => $obj
		];
		$this->objs = $toAdd + $this->objs;
	}
	
	/**
	 * Sorts objects by attribute or return from a method.
	 *
	 * <b>WARNING:</b> This method may be slow! It is advisable to use another method if one is available,
	 * for instance, using ORDER BY in an SQL statement to create and add the objects in order.<br />
	 * The <var>$element</var> parameter can specify either an attribute or a method of
	 * the objects in the collection. Methods should be named in full e.g. "method()".<br />
	 * Although this uses the PHP function usort(), the array keys <i>are</i> preserved.<br />
	 * The constants {@link SORT_OBJECTS_ASC} and {@link SORT_OBJECTS_DESC} are provided
	 * to set the <var>$sort_direction</var> parameter.<br />
	 * If you set <var>$astime</var> to true, the method will try to use PHP's strtotime() function to
	 * attempt to convert strings into time, allowing you to sort dates in the expected order. You must
	 * ensure that all data will convert to strtotime, or that data will be evalutated as false!
	 * <br />
	 * Example:<br />
	 * <code>
	 * class Employee {
	 * public $name;
	 * public function __construct($name) {$this->name = $name;}
	 * public function getName() {return $this->name;}
	 * }
	 *
	 * $employees = new Employees; // create collection object
	 * $employees->add(new Employee('neil'));
	 * $employees->add(new Employee('David'));
	 * $employees->add(new Employee('Shane'));
	 *
	 * // order by name attribute
	 * $employees->sortObjects('name');
	 * // new order: David, neil, Shane
	 *
	 * // order by method return, and case sensitive, and passing a single parameter of true to the method
	 * $employees->sortObjects('getName()', Employees::SORT_ORDERS_ASC, false, array(true));
	 * // new order: David, Shane, neil
	 * </code>
	 *
	 * @param string $element
	 *        	Element of the objects to sort by
	 * @param integer $sort_direction
	 *        	1 (ascending) or -1 (descending). See above.
	 * @param boolean $caseinsensitive
	 *        	Case sensitivity of sort.
	 * @param array $params
	 *        	Parameters to be passed on if calling a method
	 * @param boolean $astime
	 *        	Try to use strtotime() to sort by date
	 * @todo If $element = getID(), use different sort
	 */
	public function sortObjects($element, $sort_direction = SORT_OBJECTS_ASC, $caseinsensitive = true, $params = '', $astime = false)
	{
		global $csort_cmp;
		// create an array with settings to be used by csort_comp()
		$csort_cmp = array(
			'direction' => $sort_direction,
			'caseinsensitive' => $caseinsensitive,
			'params' => $params,
			'astime' => $astime
		);
		
		// sort the object
		// cant just pass "method()", so we need to use 2 different callbacks to sort
		if (substr($element, - 2, 2) == '()') { // if ends in () sort by method
			$csort_cmp['key'] = substr($element, 0, strlen($element) - 2);
			usort($this->objs, array(
				'ObjCollection',
				"csort_cmp_method"
			));
		} else { // sort by attribute
			$csort_cmp['key'] = $element;
			usort($this->objs, array(
				'ObjCollection',
				"csort_cmp_attribute"
			));
		}
		
		// prevent warning if no $objs found
		$temparray = array();
		
		// rebuild keys
		foreach ($this->objs as $obj) {
			$temparray[$obj->getID()] = $obj;
		}
		$this->objs = $temparray;
		
		unset($temparray);
		unset($csort_cmp);
	}
	
	// =================== SORT - End ==================================
	/**
	 * Get previous object in collection.
	 *
	 * @return mixed object or false.
	 */
	public function prev(): void
	{
		prev($this->objs);
	}
	
	// extends iterator, so need...
	/**
	 * (non-PHPdoc)
	 *
	 * @see Iterator::valid()
	 */
	public function valid(): bool
	{
		return (! is_null(key($this->objs)));
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see Iterator::rewind()
	 */
	public function rewind(): void
	{
		reset($this->objs);
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see Iterator::current()
	 */
	#[\ReturnTypeWillChange]
	public function current()
	{
		return current($this->objs);
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see Iterator::key()
	 */
	#[\ReturnTypeWillChange]
	public function key()
	{
		return key($this->objs);
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see Iterator::next()
	 */
	public function next(): void
	{
		next($this->objs);
	}
}
