<?php
namespace pub007\dstruct\auth;
/**
 * AuthContainers class
 */
/**
 * Collection of AuthContainer objects.
 *@package dstruct_auth
 */
class AuthContainers {

/**
 * Collection of AuthContainer objects
 * @var array
 */
private static $authcontainers = array();

/**
 * Load all auth containers into the collection.
 * @return object
 */
public function getAll() {
	if (count(self::$authcontainers) == 0) {
		$prefs = Prefs::getInstance();
		$authcontainers = $prefs->get('authcontainers');
		foreach ($authcontainers as $container) {
			self::$authcontainers[$container] = new $container;
		}
	}
	return self::$authcontainers;
}

}
?>