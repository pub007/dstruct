<?php
/**
 * DStructMemCache class
 */
/**
 * Wraps PHP's MemCache class.
 * 
 * Wrapping MemCache gives a consistent object as defined by DStructCacheInterface.
 * @package dstruct_common
 */
class DStructMemCache extends MemCache implements DStructCacheInterface {

/**
 * Instance of this class.
 * @var object
 */
private static $instance;

/**
 * Cache hits.
 * @var integer
 */
private $hits = 0;

/**
 * Cache misses.
 * @var integer
 */
private $misses = 0;

/**
 * Cache writes.
 * @var integer
 */
private $writes = 0;

/**
 * Failed cache writes.
 * @var integer
 */
private $failedwrites = 0;

/**
 * Is the cache available?
 * @var boolean
 */
private $hasserver = false;

/**
 * Class constructor.
 * @todo Check connecting to servers works as expected (what is returned when adding server).
 */
private function __construct() {
	if (Prefs::MEMCACHE_SERVERS) {
		$servers = explode(',', Prefs::MEMCACHE_SERVERS);
		foreach($servers as $server) {
			$this->hasserver = $this->addServer($server); // IS THIS CORRECT!!!??????????
		}
	}
}

/**
 * Add entry to cache.
 * 
 * Will add an entry if <var>$key</var> does not already exist.
 * @param string $key Key in the cache
 * @param string $var to cache
 * @param array $flags Use MEMCACHE_COMPRESSED to store the item compressed (uses zlib).
 * @param integer $expire Time in seconds. Default = 604800 = one week
 * @return boolean True for success
 * @see Memcache::add()
 */
public function add($key, $var, $flags = 0, $expire = 604800) {
	$result = @parent::add($key, $var, $flags, $expire);
	if ($result) {
		$this->writes++;
	} else {
		$this->failedwrites++;
	}
	return $result;
}

/**
/**
 * Delete from cache.
 * @param string $key Key in the cache
 * @see Memcache::delete()
 */
public function delete($key) {
	return parent::delete($key);
}

/**
 * Get and instance of this Singleton object. 
 * @return object
 * @todo rewrite to handle pools
 */
public static function getInstance() {
	if (empty(self::$instance)) {
		self::$instance = new clMem;
	}
	return self::$instance;
}

/**
 * Get data from cache.
 * @param string $key Key in the cache
 * @param integer $flags See {@link http://php.net/manual/en/memcache.get.php}
 * @return string
 * @see Memcache::get()
 */
public function get($key, $flags = 0) {
	$result = @parent::get($key, $flags);
	if ($result) {
		$this->hits++;
	} else {
		$this->misses++;
	}
	return $result;
}

/**
 * Cache hits.
 * @return integer
 * @see DStructCacheInterface::hits()
 */
public function hits() {return $this->hits;}

/**
 * Cache misses.
 * @return integer
 * @see DStructCacheInterface::misses()
 */
public function misses() {return $this->misses;}

/**
 * Cache writes.
 * @return integer
 * @see DStructCacheInterface::writes()
 */
public function writes() {return $this->writes;}

/**
 * Server is available?
 * @return boolean
 * @see DStructCacheInterface::hasServer()
 */
public function hasServer() {return $this->hasserver;}

/**
 * Sets a new entry or overwrites an existing one.
 * @param string $key Key in the cache
 * @param string $var
 * @param integer $flags See {@link http://php.net/manual/en/memcache.get.php}
 * @param integer $expire Time in seconds. Default = 604800 = one week
 * @see Memcache::set()
 */
public function set($key, $var, $flags = 0, $expire = 604800) { // expire = 1 week
	$result = @parent::set($key, $var, $flags, $expire);
	if ($result) {
		$this->writes++;
	} else {
		$this->failedwrites++;
	}
	return $result;
}

}
?>