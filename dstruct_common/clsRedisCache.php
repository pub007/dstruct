<?php
/**
 * RedisCache class
 */
/**
 * Singleton class wrapping Redis caching.
 * @see DStructCacheInterface
 * @package dstruct_common
 *
 */
class RedisCache implements DStructCacheInterface {

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

private $r = null;

/**
 * Class constructor
 * Object is singleton. Creating instance of class checks whether Redis is
 * available or not: {@link RedisCache::hasServer()}
 */
protected function __construct() {
	$this->hasserver = true;
	if (!extension_loaded('redis')) {
	    $this->hasserver = false;
	    error_log("RedisCache::__construct(): Redis extension is not loaded");
	}
	if (!defined('Prefs::REDIS_CACHE_SERVERS')) {
	    $this->hasserver = false;
	    error_log("RedisCache::__construct(): Prefs::REDIS_CACHE_SERVERS not defined");
	}
	// currently only supporting one server
	$r = new Redis();
	$con = Prefs::REDIS_CACHE_SERVERS;
	$r->connect($con['address'], $con['port']);
	if ($r->ping() !== '+PONG') {
	    $this->hasserver = false;
	    error_log("RedisCache::__construct(): Unable to connect to server");
	}
	$this->r = $r;
	return $this->hasserver;
}

/**
 * Add to cache, but only if doesnt already exist.
 * @see DStructCacheInterface::add()
 * @param string $key
 * @param mixed $var
 * @param integer $expire Time the data is valid for in seconds
 * @return boolean TRUE if value was actually added, FALSE if otherwise
 */
public function add($key, $var, $expire = 604800) {
    $result = $this->r->set($key, $var, array('nx', 'ex'=>$expire)); // setnx may be depricated in future.
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
	return ($this->r->del($key) === 0) ? false : true;
}

/**
 * Get an instance of this Singleton class.
 * @see DStructCacheInterface::getInstance()
 * @return object
 * @todo rewrite to handle pools
 */
public static function getInstance() {
	if (empty(self::$instance)) {self::$instance = new RedisCache();}
	return self::$instance;
}

/**
 * Fetch from cache.
 * @see DStructCacheInterface::get()
 * @param string $key
 * @return mixed
 */
public function get($key) {
    $result = $this->r->get($key);
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
 * @param integer $expire Time the data is valid for in seconds. Default = 1 week
 * @return boolean TRUE if value was actually added, FALSE if otherwise
 * @see DStructCacheInterface::set()
 */
public function set($key, $var, $expire = 604800) {
    $result = $this->r->set($key, $var, array('ex'=>$expire));
	if ($result) {
		$this->writes++;
	} else {
		$this->failedwrites++;
	}
	return $result;
}

}
?>