<?php
namespace pub007\dstruct;

/**
 * ObjWatcher object
 */
/**
 * Identity Map object.
 *
 * Helps prevent duplication of objects within the system, preventing
 * bugs with overwriting changes etc.
 * All watched objects of a class should return a unique string (usually it will
 * actually be an integer, but strings are supported). via a getID()
 * method.
 *
 * @package dstruct_common
 */
class ObjWatcher
{

	/**
	 * The watched objects
	 *
	 * @var array
	 */
	private static $objs = [];

	/**
	 * Times watched objects were found.
	 *
	 * @var integer
	 */
	private static $cachehits = 0;

	/**
	 * Instance of this object
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Class constructor.
	 */
	private function __construct()
	{}

	/**
	 * Objects watched.
	 *
	 * @return integer
	 * @todo changed - needs checking
	 */
	public static function getObjectCount(): int
	{
		return count(self::$objs);
	}

	/**
	 * Number of times watched objects were found.
	 *
	 * @return integer
	 */
	public static function getCacheHits(): int
	{
		return self::$cachehits;
	}

	/**
	 * Generate key to identify object.
	 *
	 * Watched objects are identified by their class and their ID
	 *
	 * @param object $obj
	 * @return string
	 */
	private static function globalKey(object $obj): string
	{
		$key = get_class($obj) . '.' . $obj->getID();
		return $key;
	}

	/**
	 * Add an object to be watched.
	 *
	 * @param object $obj
	 */
	public static function add(object $obj)
	{
		$globalkey = self::globalKey($obj);
		self::$objs[$globalkey] = $obj;
	}

	/**
	 * Is an object already being watched?
	 *
	 * This takes class name and id as the check should be made
	 * before the new object would have been instanced!
	 *
	 * @param string $classname
	 * @param string $id
	 * @return mixed The object, if it is found, or false.
	 */
	public static function exists(string $classname, string $id): mixed
	{
		$key = $classname . '.' . $id;
		if (array_key_exists($key, self::$objs)) {
			self::$cachehits ++;
			return self::$objs[$key];
		}
		return false;
	}

	/**
	 * Remove an object from being watched.
	 *
	 * @param object $obj
	 * @return boolean True on success, false if object not found.
	 */
	public static function remove(object $obj): bool
	{
		$key = self::globalKey($obj);
		if (array_key_exists($key, self::$objs)) {
			unset(self::$objs[$key]);
			return true;
		}
		return false;
	}
}
