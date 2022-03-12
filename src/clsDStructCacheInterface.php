<?php
/**
 * DStructCacheInterface Interface
 */
/**
 * Provides consistant caching interface.
 * 
 * Cache objects can extend this interface and be used by the system or by
 * users in their scripts. Objects should be Singletons
 * @author David Lidstone
 * @package dstruct_common
 */
interface DStructCacheInterface {
	
/**
 * Add to the cache, but only if it doesn't already exist.
 * @param string $key
 * @param mixed $var
 * @param integer $expire Time the data is valid for in seconds
 * @return boolean TRUE if value was actually added, FALSE if otherwise
 */
function add($key, $var, $expire = 604800);

/**
 * Delete from the cache.
 * @param string $key
 * @return boolean
 */
function delete($key);

/**
 * Fetch a cached value.
 * @param string $key
 * @return mixed
 */
function get($key);

/**
 * Get an instance of the object.
 * 
 * Objects are singletons and so use this method to get an instance.
 * If you impliment this interface, it is recommended that you declare the __constructor()
 * as protected
 * @return object
 */
static function getInstance();

/**
 * Cache is available.
 * 
 * Is the cache server this object uses available to be used
 * by the system
 * @return boolean
 */
function hasServer();

/**
 * Hits on the cache in this script execution.
 * 
 * Not persistent. Counts the number of times this cache object
 * has been queried and returned existing data.
 * Please check the documentation for each classes implimentation
 * of this method, as the functionallity may vary across classes.
 * @return integer
 */
function hits();

/**
 * Misses on the cache in this script execution.
 * 
 * Not persistent. Counts the number of times this cache object
 * has been queried but had no data for that key.
 * Please check the documentation for each classes implimentation
 * of this method, as the functionallity may vary across classes.
 */
function misses();

/**
 * Add to the cache, overwriting if the key already exists.
 * @param string $key
 * @param mixed $var
 * @param integer $expire Time the data is valid for in seconds
 * @return boolean TRUE if value was actually added, FALSE if otherwise
 */
function set($key, $var, $expire = 604800);

/**
 * Writes to the cache in this script execution.
 * 
 * Not persistent. Counts the number of times this cache object
 * has been written to.
 * Please check the documentation for each classes implimentation
 * of this method, as the functionallity may vary across classes.
 */
function writes();
}
?>