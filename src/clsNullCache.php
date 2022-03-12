<?php
/**
 * NullCache class
 */
/**
 * If there is not cache available, then the bootstrap uses this class.
 * 
 * NullCache impliments DStructCacheInterface, but will always show that
 * no cache is available. Any attempted writes or reads will fail as
 * exected with failed reads / writes with any other DStruct cache
 * object.
 * @package dstruct_common
 */
class NullCache implements DStructCacheInterface {

/**
 * Instance of this Singleton class.
 * @var object
 */
private static $instance;

/**
 * Cache misses.
 * @var integer
 */
private $misses = 0;

/**
 * Failed writes.
 * @var integer
 */
private $failedwrites = 0;

/**
 * Cache server available
 * @var boolean
 */
private $hasserver = false;

/**
 * Class constructor
 */
private function __construct() {}

/**
 * Add method.
 * 
 * Will always return false.
 * @param string $key
 * @param string $var data to add
 * @param integer $expire
 * @see DStructCacheInterface::add()
 */
public function add($key, $var, $expire = 604800) {
	$this->failedwrites++;
	return false;
}

/**
 * Delet from cache
 * @param string $key
 * @see DStructCacheInterface::delete()
 */
public function delete($key) {
	return false;
}

/**
 * Get instance of this Singleton class.
 * @return object
 */
public static function getInstance() {
	if (empty(self::$instance)) {self::$instance = new NullCache;}
	return self::$instance;
}

/**
 * (non-PHPdoc)
 * @param string $key
 * @see DStructCacheInterface::get()
 */
public function get($key) {
	$this->misses++;
	return false;
}

/**
 * (non-PHPdoc)
 * @see DStructCacheInterface::hits()
 */
public function hits() {return 0;}

/**
 * (non-PHPdoc)
 * @see DStructCacheInterface::misses()
 */
public function misses() {return $this->misses;}

/**
 * (non-PHPdoc)
 * @see DStructCacheInterface::writes()
 */
public function writes() {return 0;}

/**
 * (non-PHPdoc)
 * @see DStructCacheInterface::hasServer()
 */
public function hasServer() {return false;}

/**
 * (non-PHPdoc)
 * @param string $key
 * @param string $var
 * @param integer $expire
 * @see DStructCacheInterface::set()
 */
public function set($key, $var, $expire = 604800) { // expire = 1 week
	$this->failedwrites++;
	return false;
}

}
?>