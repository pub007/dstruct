<?php
namespace pub007\dstruct\auth;
/**
 * AuthContainerInterface class
 */
/**
 * To be compatible with the auth package, containers must impliment this interface.
 * 
 *Any potential AuthContainer must impliment the methods of this interface
 *(they don't <i>have</i> to impliment the interface explicitly though!).
 *@package dstruct_auth
 */
interface AuthContainerInterface {

/**
 * A user friendly name for the Container.
 * 
 * If <var>$raw</var> is false then the return should be encoded to be
 * output as html.
 * @param boolean $raw
 * @return string
 */
public function getDisplayName($raw = false);

/**
 * Must return an object of the type the AuthContainer represents.
 * @param string
 * @return object
 */
public static function loadByID($id);

/**
 * Returns the array of current permissions.
 * @return array
 * @see Perm::permissions()
 */
public function permissions();

/**
 * Attempt to authenticate the user.
 * The operation of the authenticate method is entirely up to the
 * implimenting class. This allows the container to integrate and authenticate with
 * whatever system you choose. The class must return a boolean with true as
 * a successfull authentication.
 * @param string $username
 * @param string $password
 * @return boolean
 * @see Perm::authenticate()
 */
public function authenticate($username, $password);

/**
 * Must return Object Collection of {@link Groups}.
 * @return object
 */
public function getGroups();

/**
 * Must return true or false, dependant on the container belonging to any clpGroups.
 * @return boolean
 */
public function hasGroups();

/**
 * Must return a unique and persistant ID
 * @return string
 */
public function getID();

/**
 *Tests whether a record with the same username already exists.
 *
 *This kind of call would usually be made on the Object Collection class,, but
 *the auth package gets no knowledge of these collections so must make the
 *call to the implimenting class which will usually pass the call on to the
 *collection.
 *@param string $username
 *@return boolean
 */
public static function usernameExists($username);

}
?>