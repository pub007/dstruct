<?php
/**
 * APCCache class
 */
/**
 * Singleton class wrapping APC caching.
 * @see DStructCacheInterface
 * @package dstruct_common
 *
 */
class APCCache implements DStructCacheInterface {

/**
 * null or instance of the class.
 * @var mixed
 */
private static $instance;

/**
 * Cache hits.
 * @see DStructCacheInterface::hits()
 * @var integer
 */
private $hits = 0;

/**
 * Cache misses.
 * @see DStructCacheInterface::misses()
 * @var integer
 */
private $misses = 0;

/**
 * Cache writes.
 * @see DStructCacheInterface::writes()
 * @var integer
 */
private $writes = 0;

/**
 * Failed writes.
 * @var integer
 */
private $failedwrites = 0;

/**
 * Cache is available.
 * @see DStructCacheInterface::hasServer()
 * @var boolean
 */
private $hasserver = false;

/**
 * Class constructor
 * Object is singleton. Creating instance of class checks whether APC is
 * available or not: {@link APCCache::hasServer()}
 */
protected function __construct() {
	$this->hasserver = (extension_loaded('apc'))? true : false;
}

/**
 * Add to cache.
 * @see DStructCacheInterface::add()
 * @param string $key
 * @param mixed $var
 * @param integer $expire Time the data is valid for in seconds
 * @return boolean TRUE if value was actually added, FALSE if otherwise
 */
public function add($key, $var, $expire = 604800) {
	$result = apc_add($key, $var, $expire);
	if ($result) {
		$this->writes++;
	} else {
		$this->failedwrites++;
	}
	return $result;
}

/**
 * Delete from cache.
 * @see DStructCacheInterface::delete()
 * @param string $key
 * @return boolean
 */
public function delete($key) {
	return apc_delete($key);
}

/**
 * Get an instance of this Singleton class.
 * @see DStructCacheInterface::getInstance()
 * @return object
 * @todo rewrite to handle pools
 */
public static function getInstance() {
	if (empty(self::$instance)) {self::$instance = new APCCache;}
	return self::$instance;
}

/**
 * Fetch from cache.
 * @see DStructCacheInterface::get()
 * @param string $key
 * @return mixed
 */
public function get($key) {
	$result = apc_fetch($key);
	if ($result) {
		$this->hits++;
	} else {
		$this->misses++;
	}
	return $result;
}

/**
 * Cache hits.
 * @see DStructCacheInterface::hits()
 * @return integer
 */
public function hits() {return $this->hits;}

/**
 * Cache misses.
 * @see DStructCacheInterface::misses()
 * @return integer
 */
public function misses() {return $this->misses;}

/**
 * Cache writes.
 * @see DStructCacheInterface::writes()
 */
public function writes() {return $this->writes;}

/**
 * Cache is available.
 * @return boolean
 */
public function hasServer() {return $this->hasserver;}

/**
 * Add to the cache, overwriting if the key already exists.
 * Default expiration is one week (604800).
 * @param string $key
 * @param mixed $var
 * @param integer $expire Time the data is valid for in seconds
 * @return boolean TRUE if value was actually added, FALSE if otherwise
 * @see DStructCacheInterface::set()
 */
public function set($key, $var, $expire = 604800) { // expire = 1 week
	$result = apc_store($key, $var, $expire);
	if ($result) {
		$this->writes++;
	} else {
		$this->failedwrites++;
	}
	return $result;
}

}
?>