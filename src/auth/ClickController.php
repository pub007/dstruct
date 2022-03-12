<?php
/**
 * ClickController class
 */
/**
 * Control and count clicks.
 * 
 * Typically used by log in forms to prevent brute force attacks. For example, can
 * count user log in attempts and script can then lock the form for a period of time if they fail
 * to use correct credentials within that limit.
 * @package dstruct_auth
 */
class ClickController {

/**
 * Clicks the user is allowed.
 *@var integer
 */
private $allowed_clicks;

/**
 * The currently in use caching object - required to use ClickController.
 * @var object
 */
private $cache;

/**
 * Number of clicks recorded.
 * @var integer
 */
private $clickcount;

/**
 * Timeout for entries.
 * 
 * In seconds. Default is 172800 - two days.
 * @var integer
 */
private $timeout = 172800; // 2 days

/**
 * TTL for cache entries.
 * 
 * In seconds. Default is 173100 - two days, five minutes. The
 * entry in the cache will be invalidated at this time - essentially
 * five minutes after the {@link $timeout} as it is no longer needed.
 * @var integer
 */
private $ttl = 173100; // 2 days, 5mins

/**
 * Used to differentiate between users.
 * @var string
 */
private $useridentifier;

/**
 * Class constructor.
 * 
 * Attempts to grab the default cache object and will set
 * an identifier for the user (session or the IP).
 * @see Prefs::CLICK_CONTROL_IDENTIFIER_IS_SESSION
 * @throws DStructGeneralException If can not find 'global' cache
 */
public function __construct() {
	$prefs = Prefs::getInstance();
	$cache = $prefs->get('cache');
	if (!isset($cache) || !$cache->hasServer()) {throw new DStructGeneralException('ClickController::__construct() - No cache available');}
	$this->cache = $cache;
	if (defined('Prefs::CLICK_CONTROL_IDENTIFIER_IS_SESSION')) {
		if (Prefs::CLICK_CONTROL_IDENTIFIER_IS_SESSION) {
			@session_start();
			if (isset($_SESSION['click_control_identifier'])) {
				$this->useridentifier = $_SESSION['click_control_identifier'];
			} else {
				$this->useridentifier = md5(rand() . 'randsalt');
				$_SESSION['click_control_identifier'] = $this->useridentifier;
			}
			return;
		}
	}
	$this->useridentifier = $_SERVER['REMOTE_ADDR'];
}

/**
* Check to see whether the user is allowed to attempt a click.
* 
* See if click can be authorised by the current click count being a lower value than
* the allowed clicks set and the current timestamp being less than the timestamp stored in the 
* cache under the current object key, returning true or false.
* @param string $userkey Key used to identify the clickable object in the script
* @return boolean
*/
public function isClickAllowed($userkey){
	$key = $this->getKey($userkey);
	// if no record of previous click(s) then allow click
	if (!$this->cache->get($key)) {return true;}
	// if record has expired then delete the old record and allow click
	if(time() >= $this->getTimestamp($userkey)){
		$this->cache->delete($key);
		return true;
	}
	// if the user still has clicks allowed, then allow click
	if($this->getClickCount($userkey) < $this->getAllowedClicks()){return true;}
	// user not authorised
	return false;
}

/**
* Delete the cache entry.
* 
* This will have the effect of resetting the clicks etc.
* @param string $userkey Key used to identify the clickable object in the script
*/
public function clear($userkey){
	$this->cache->delete($this->getKey($userkey));
}

/**
* Record a click.
* @param string $userkey Key used to identify the clickable object in the script
*/
public function click($userkey){
	$key = $this->getKey($userkey);
	if($this->cache->get($key)){
		$this->cache->set($key, $this->getClickCount($userkey) + 1 . '_' . $this->timeout, $this->ttl);
	} else {
		$this->cache->set($key, '1_' . $this->timeout, $this->ttl);
	}
}

/**
 *Return the number of allowed clicks currently set.
 *
 *@see setAllowedClicks()
 *@return integer
 */
public function getAllowedClicks(){
	if($this->allowed_clicks){return $this->allowed_clicks;}
}

/**
 *Return the number of clicks stored in the cache.
 *
 *@param string $userkey Key used to identify the clickable object in the script
 *@return integer
 */
public function getClickCount($userkey){
	$key = $this->getKey($userkey);
	if($this->cache->get($key)){
		$value = explode('_', $this->cache->get($key));
		return $value[0];
	}
}

/**
 * Return the key to be stored in the cache.
 * 
 * The key that is used in the cache is a concatenation of the application name
 * ({@link Prefs::APP_NAME}), the <var>$userkey</var> and the identifier (either
 * the session or the IP). 
 * @param string $userkey Key used to identify the clickable object in the script
 * @return string
 */
private function getKey($userkey){
	return Prefs::APP_NAME . '_' . $userkey . '_' . $this->useridentifier;
}

/**
 *Return the time in UTS of the last attempt which was stored in the cache.
 *@param string $userkey Key used to identify the clickable object in the script
 *@return mixed Integer or false
 */
public function getTimestamp($userkey){
	$key = $this->getKey($userkey);
	if($this->cache->get($key)){
		$value = explode('_', $this->cache->get($key));
		return $value[1];
	}
	return false;
}
	
/**
 * Return the UTS value that has been set.
 * 
 * Is the time between the web users final allowed click before
 * access is granted to click again
 *@see setTimeOut()
 *@return integer
 */
public function getTimeOut(){return $this->timeout;}

/**
 * Sets the number of click before allowClick() returns false.
 * 
 * @param integer $allowed_clicks
 */
public function setAllowedClicks($allowed_clicks) {
	$this->allowed_clicks = $allowed_clicks;
}

/**
* Sets the TimeOut in UTS.
* 
* TimeOut to block access to a web user once the number of click
* attempts matches the number of allowed clicks set.
* @param integer $timeout
*/
public function setTimeOut($timeout){
	$this->ttl = $timeout + 300; // will timeout the cache entry itself 5 mins after the record is of any use to us?????
	$this->timeout = time() + $timeout;
}

}
?>