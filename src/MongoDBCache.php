<?php
namespace pub007\dstruct;

/**
 * RedisCache class
 */
/**
 * Singleton class wrapping Redis caching.
 *
 * @see DStructCacheInterface
 * @package dstruct_common
 *         
 */
class MongoDBCache implements DStructCacheInterface
{

	private $collection = null;

	/**
	 * Array containing instance of the class.
	 *
	 * @var array
	 */
	private static $instances = [];

	/**
	 * Cache hits.
	 *
	 * @see DStructCacheInterface::hits()
	 * @var integer
	 */
	private $hits = 0;

	/**
	 * Cache misses.
	 *
	 * @see DStructCacheInterface::misses()
	 * @var integer
	 */
	private $misses = 0;

	/**
	 * Cache writes.
	 *
	 * @see DStructCacheInterface::writes()
	 * @var integer
	 */
	private $writes = 0;

	/**
	 * Failed writes.
	 *
	 * @var integer
	 */
	private $failedwrites = 0;

	/**
	 * Cache is available.
	 *
	 * @see DStructCacheInterface::hasServer()
	 * @var boolean
	 */
	private $hasserver = false;

	/**
	 * Instance of the cache
	 *
	 * @var null|object
	 */
	private $r = null;

	/**
	 * Class constructor
	 * Object is singleton.
	 * Creating instance of class checks whether Redis is
	 * available or not: {@link RedisCache::hasServer()}
	 *
	 * @return false|RedisCache
	 */
	protected function __construct(string $cacheName)
	{
		$this->hasserver = true;
		if (! extension_loaded('mongodb')) {
			$this->hasserver = false;
			error_log("MongoDBCache::__construct(): MongoDB extension is not loaded");
			return false;
		}
		$this->collection = new DocStore('cache', 'cache')
		
		// get config
		$config = Prefs::gi()->get('redis_config');
		if (! $config) {
			$this->hasserver = false;
			error_log("RedisCache::__construct(): Prefs key 'redis_config' not defined");
			return false;
		}

		if (! $config = $config[$cacheName]) {
			$this->hasserver = false;
			error_log("RedisCache::__construct(): Redis config named [$cacheName] not defined");
			return false;
		}
		// currently only supporting one server
		$r = new Redis();
		$r->connect($config['host'], $config['port']);
		if ($r->ping() !== '+PONG') {
			$this->hasserver = false;
			error_log("RedisCache::__construct(): Unable to connect to server");
		}
		$this->r = $r;
		return $this->hasserver;
	}

	/**
	 * Add to cache, but only if doesnt already exist.
	 *
	 * @see DStructCacheInterface::add()
	 * @param string $key
	 * @param mixed $var
	 * @param integer $expire
	 *        	Time the data is valid for in seconds
	 * @return boolean TRUE if value was actually added, FALSE if otherwise
	 */
	public function add($key, $var, $expire = 604800)
	{
		$result = $this->r->set($key, $var, array(
			'nx',
			'ex' => $expire
		)); // setnx may be depricated in future.
		if ($result) {
			$this->writes ++;
		} else {
			$this->failedwrites ++;
		}
		return $result;
	}

	/**
	 * Delete from cache.
	 *
	 * @see DStructCacheInterface::delete()
	 * @param string $key
	 * @return boolean
	 */
	public function delete($key)
	{
		return ($this->r->del($key) === 0) ? false : true;
	}

	/**
	 * Get an instance of this class.
	 *
	 * @see DStructCacheInterface::getInstance()
	 * @param
	 *        	string Name of the cache to return instance of. Empty string for default / first in config
	 * @return object
	 * @todo rewrite to handle pools
	 */
	public static function getInstance(string $cacheName = '')
	{
		if (! in_array($cacheName, self::$instances)) {
			self::$instances[$cacheName] = new RedisCache($cacheName);
		}
		return self::$instances[$cacheName];
	}

	public static function gi(string $cacheName = '')
	{
		return self::getInstance($cacheName);
	}

	/**
	 * Fetch from cache.
	 *
	 * @see DStructCacheInterface::get()
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		$result = $this->r->get($key);
		if ($result) {
			$this->hits ++;
		} else {
			$this->misses ++;
		}
		return $result;
	}

	/**
	 * Cache hits.
	 *
	 * @see DStructCacheInterface::hits()
	 * @return integer
	 */
	public function hits()
	{
		return $this->hits;
	}

	/**
	 * Cache misses.
	 *
	 * @see DStructCacheInterface::misses()
	 * @return integer
	 */
	public function misses()
	{
		return $this->misses;
	}

	/**
	 * Cache writes.
	 *
	 * @see DStructCacheInterface::writes()
	 */
	public function writes()
	{
		return $this->writes;
	}

	/**
	 * Cache is available.
	 *
	 * @return boolean
	 */
	public function hasServer()
	{
		return $this->hasserver;
	}

	/**
	 * Add to the cache, overwriting if the key already exists.
	 * Default expiration is one week (604800).
	 *
	 * @param string $key
	 * @param mixed $var
	 * @param integer $expire
	 *        	Time the data is valid for in seconds. Default = 1 week
	 * @return boolean TRUE if value was actually added, FALSE if otherwise
	 * @see DStructCacheInterface::set()
	 */
	public function set($key, $var, $expire = 604800)
	{
		$result = $this->r->set($key, $var, array(
			'ex' => $expire
		));
		if ($result) {
			$this->writes ++;
		} else {
			$this->failedwrites ++;
		}
		return $result;
	}
}
?>