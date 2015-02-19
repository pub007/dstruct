<?php
/**
 * ObjWatcher object
 */
/**
 * Identity Map object.
 * 
 * Helps prevent duplication of objects within the system, preventing
 * bugs with overwriting changes etc.
 * All watched objects should return a unique string (usually it will
 * actually be an integer, but strings are supported). via a getID()
 * method.
 * @package dstruct_common
 */
class ObjWatcher {

/**
 * The watched objects
 * @var array
 */
private $objs = array();

/**
 * Times watched objects were found.
 * @var integer
 */
private static $cachehits = 0;

/**
 * Instance of this object
 * @var object
 */
private static $instance;

/**
 * Class constructor.
 */
private function __construct() {}

/**
 * Get an instance of this Singleton object.
 * @return object
 */
public static function instance() {
	if (!self::$instance) {
		self::$instance = new ObjWatcher();
	}
	return self::$instance;
}

/**
 * Objects watched.
 * @return integer
 * @todo changed - needs checking
 */
public static function getObjectCount() {
	return count(self::$objs);
}

/**
 * Number of times watched objects were found.
 * @return integer
 */
public static function getCacheHits() {
	return self::$cachehits;
}

/**
 * Generate key to identify object.
 * 
 * Watched objects are identified by their class and their ID
 * @param object $obj
 * @return string
 */
private function globalKey($obj) {
	$key = get_class($obj) . '.' . $obj->getID();
	return $key;
}

/**
 * Add an object to be watched.
 * @param object $obj
 */
public static function add($obj) {
	$inst = self::instance();
	$globalkey = $inst->globalKey($obj);
	$inst->objs[$globalkey] = $obj;
}

/**
 * Is an object already being watched?
 * 
 * This takes class name and id as the check should be made
 * before the new object would have been instanced!
 * @param string $classname
 * @param string $id
 * @return mixed The object, if it is found, or false.
 */
public static function exists($classname, $id) {
	$inst = self::instance();
	$key = $classname . '.' . $id;
	if (array_key_exists($key, $inst->objs)) {
		self::$cachehits++;
		return $inst->objs[$key];
	}
	return false;
}

/**
 * Remove an object from being watched.
 * @param object $obj
 * @return boolean True on success, false if object not found.
 */
public static function remove($obj) {
	$inst = self::instance();
	$key = $inst->globalKey($obj);
	if (array_key_exists($key, $inst->objs)) {
		unset($inst->objs[$key]);
		return true;
	} else {
		return false;
	}
}

}
?>