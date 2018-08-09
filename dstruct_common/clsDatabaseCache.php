<?php
// usually, the AutoLoader will load classes, but in this case, the database cache
// can be loaded before the autoloader because the cache is required for the
// autoloader to work!
require_once 'clsBase.php';
require_once 'clsDB.php';

/**
 * DatabaseCache class
 */
/**
 * Facade for caching in the database.
 * 
 * Requires {@link Prefs::DB_CACHE} to be set to a database connection. The class will
 * automatically switch to this database and then back everytime it uses the database.
 * Database caching can be useful if you want to share information across multiple instances
 * (like multiple servers) which are part of the same system. It can also allow
 * more persistance. However, it is usually better (faster!) to use an in-memory
 * cache such as APC if there is a single instance or MemCache for multiple
 * instances.
 * @package dstruct_common
 * @todo Easy way to set up the database?
 */
class DatabaseCache extends Base implements DStructCacheInterface {

/**
 * Instance of the class.
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
 * Server is available.
 * @var boolean
 */
private $hasserver = false;

/**
 * SQL for adding to cache.
 * @var string
 */
private $sql_add = 'INSERT INTO appcache
					(AppCacheID, CacheVal, CacheExpire) 
					VALUES (?, ?, ?)
					ON DUPLICATE KEY UPDATE AppCacheID = AppCacheID';

/**
 * SQL to clear cache.
 * @var string
 */
private $sql_clear = 'TRUNCATE TABLE appcache';

/**
 * SQL to delete entry from cache.
 * @var string
 */
private $sql_delete = 'DELETE FROM appcache WHERE AppCacheID = ?';

/**
 * SQL to get entry from cache.
 * @var string
 */
private $sql_get = 'SELECT CacheVal, CacheExpire
					FROM appcache
					WHERE AppCacheID = ?';

/**
 * SQL to scavenge expired entries in cache.
 * @var string
 */
private $sql_scavenge = 'DELETE FROM appcache WHERE CacheExpire < ?';

/**
 * SQL to set entry into cache.
 * @var string
 */
private $sql_set = 'REPLACE INTO appcache
					SET AppCacheID = ?,
						CacheVal = ?,
						CacheExpire = ?';

/**
 * Class constructor.
 * Class is a Singleton so use {@link getInstance()}. 
 */
protected function __construct() {
	// attempt to contact db, and set $this->hasServer()
	$this->dbs = DBSelector::getInstance();
	try {
		if (defined('Prefs::DB_CACHE')) {
			if (Prefs::DB_CACHE) {
				// attempt to connect to the db
				// we don't know when the call is made so user may be connected to a
				// different db (quite likely), so we need to test using 'switch'
				$this->dbs->switchToDB(Prefs::DB_CACHE);
				$this->dbs->getConnection(); // we don't need to get the connection, as we don't actually need it now.
				$this->dbs->switchBackDB();
				$this->hasserver = true;
			}
		}
	// we catch and 'cancel' the error because hasServer() should be used to determine availability
	} catch (DStructGeneralException $e) {
		
	}
}

/**
 * Add entry to cache.
 * 
 * Will add an entry if <var>$key</var> does not already exist.
 * @param string $key Key in the cache
 * @param string $var to cache
 * @param integer $expire Time in seconds. Default = 604800 = one week
 * @return boolean True for success
 * @see DStructCacheInterface::add()
 */
public function add($key, $var, $expire = 604800) {
	if (!$key) {throw new DStructGeneralException('DatabaseCache::add() - $key param can not be null, zero or false');}
	if ($this->get($key)) {
		$this->failedwrites++;
		return false;
	} else {
		return $this->set($key, $var, $expire);
	}
}

/**
 * Clear the cache. SEE WARNING BELOW.
 * 
 * WARNING: This will currently clear the <i>entire</i> cache, not just the
 * entries for this application! Use with extreme caution.
 * @todo restrict to cache entries which are part of this application!
 */
public function clear() {
	$this->dbs->switchToDB(Prefs::DB_CACHE);
	self::doStatement($this->sql_clear, array());
	$this->dbs->switchBackDB();
}

/**
 * Delete from cache.
 * @param string $key Key in the cache
 * @see DStructCacheInterface::delete()
 */
public function delete($key) {
	if (!$key) {throw new DStructGeneralException('DatabaseCache::delete() - $key param can not be null, zero or false');}
	$this->dbs->switchToDB(Prefs::DB_CACHE);
	$result = self::doStatement($this->sql_delete, array($key));
	$this->dbs->switchBackDB();
	return $result->rowCount();
}

/**
 * Get instance of the Singleton object.
 * @return DatabaseCache
 */
public static function getInstance() {
	if (empty(self::$instance)) {self::$instance = new DatabaseCache;}
	return self::$instance;
}

/**
 * Get data from cache.
 * @param string $key Key in the cache
 * @return string
 * @see DStructCacheInterface::get()
 */
public function get($key) {
	if (!$key) {throw new DStructGeneralException('DatabaseCache::get() - $key param can not be null, zero or false');}
	$this->dbs->switchToDB(Prefs::DB_CACHE);
	$result = self::doStatement($this->sql_get, array($key));
	$this->dbs->switchBackDB();
	$row = $result->fetch(PDO::FETCH_ASSOC);
	
	if ($row) {
		if ($row['CacheExpire'] < time()) {
			$this->misses++;
			return false;
		}
		$this->hits++;
		return $row['CacheVal'];
	} else {
		$this->misses++;
		return false;
	}
}

public static function getTableName() {
    return 'AppCache';
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
 * Scavenge any expired cache entries.
 * @return integer The number of records deleted
 */
public function scavenge() {
	$this->dbs->switchToDB(Prefs::DB_CACHE);
	$result = self::doStatement($this->sql_scavenge, array(time()));
	$this->dbs->switchBackDB();
	return $result->rowCount();
}

/**
 * Sets a new entry or overwrites an existing one.
 * @param string $key Key in the cache
 * @param string $var
 * @param integer $expire Time in seconds. Default = 604800 = one week
 * @see DStructCacheInterface::set()
 */
public function set($key, $var, $expire = 604800) { // expire = 1 week
	if (!$key) {throw new DStructGeneralException('DatabaseCache::set() - $key param can not be null, zero or false');}
	$this->dbs->switchToDB(Prefs::DB_CACHE);
	self::doStatement($this->sql_set, array($key, $var, time() + $expire));
	$this->dbs->switchBackDB();
	$this->writes++;
	return true;
}

}
?>