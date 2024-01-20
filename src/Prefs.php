<?php
namespace pub007\dstruct;
/**
 * Prefs class
 */
/**
 * Application settings and Constants.
 * 
 * Singleton object. Set application constants at design and
 * at runtime.
 * @package dstruct_common
 */
class Prefs {
	
	
	/**
	 * @var array Properties held by the class
	 */
	private $props = array();
	
	
	/**
	 * @var object Instance of Prefs class
	 */
	private static $instance;
	
	/**
	 * @var integer Counts prepared statement hits
	 * @see Prefs::countStatementHit()
	 * @see DB
	 */
	private static $statementhits = 0;
	
	/**
	 * DB Connection for {@link DatabaseCache}
	 * If you are using a DatabaseCache then you will need to define the
	 * name of the connection created in {@link Prefs::DB_CONNECTIONS} here.
	 * @var string
	 */
	const DB_CACHE = 'appcache';
	
	/**
	 * Default Cache
	 * Set the default cache system to be used by the framework. Can be:
	 * <var>Redis</var>,
	 * <var>DStructMemCache</var>,
	 * <var>Database</var>,
	 * another cache object which impliments {@link DStructCacheInterface} or leave blank for none. Not using APC (or using none) will not turn off
	 * the APC op-code cache.
	 * @var string
	 */
	const CACHE_DEFAULT = 'Redis';
	
	/**
	 * Enable script timing
	 * If this is true, then microtime() is available from Prefs with key: dstruct_timer_start
	 * @var bool
	 */
	const DSTRUCT_TIMER = false;
	
	
	
	//=====================================================
	
	
	/**
	 * Class constructor
	 * Only scalar values can be defined as class constants. Define
	 * any global compile time values stored in arrays etc here.
	 */
	private function __construct() {}
	
	/**
	 * Get instance of this Singleton class
	 * @return object Prefs
	 */
	public static function getInstance(): Prefs
	{
		if (empty(self::$instance)) {
			self::$instance = new Prefs;
		}
		return self::$instance;
	}
	
	/**
	 * 
	 * @return object Prefs
	 */
	public static function gi(): Prefs
	{
		return self::getInstance();
	}
	
	/**
	 * Set a key / value pair, only if doesn't exist
	 * @param string $key
	 * @param mixed $val
	 * @return bool False if already exists
	 */
	public function add(string $key, mixed $val): bool
	{
	    if (isset($this->props[$key])) {
	        return false;
	    }
	    $this->set($key, $val);
	    return true;
	}
	
	/**
	 * Set a key / value pair
	 * @param string $key
	 * @param mixed $val
	 */
	public function set(string $key, $val): void
	{
		$this->props[$key] = $val;
	}
	
	/**
	 * Retrieve a value by its key
	 * @param string $key
	 * @return mixed null if not found
	 */
	public function get(string $key): mixed
	{
		if (array_key_exists($key, $this->props)) {
			return $this->props[$key];
		}
		return null;
	}
	
	/**
	 * Used to count hits on prepared statements in {@link Base}
	 */
	public static function countStatementHit()
	{
		self::$statementhits++;
	}
	
	/**
	 * Get hits on prepared statements.
	 * @return int
	 */
	public static function getStatementHitCount(): int
	{
		return self::$statementhits;
	}

}