<?php
/**
 * Auth class
 */
/**
 * Acts as a central Authorisation and permissions system for the script.
 *
 * Some theory:
 * 'Applications' are not directly supported as they are taken to mean
 * separate domains.<br />
 * Areas track users having authenticated in different 'log in pages' within
 * the same domain, e.g. an Administrators area and a Members area and prevents
 * users with valid credentials in one area moving to a different area and having the
 * same 'AuthID' as someone in the second area, therefore gaining access.<br />
 * AuthContainers are classes capable of authenticating a user (they should implement
 * {@link AuthContainerInterface}). They also need to be added to the database so
 * that they can be linked to {@link Group}s. The system can handle multiple
 * AuthContainers.
 * @package dstruct_auth
 */
class Auth {

/**
 * AuthContainers available to the Auth system.
 * @var array
 */
private $authcontainers = array();

/**
 * AuthContainer against which authentication has succeeded.
 * @var null|object
 */
private $activecontainer;

/**
 * Indicator for lazy loading of permissions.
 * @var boolean
 */
private $permissionsloaded = false;

/**
 * Permissions for user.
 * @var array
 */
private $permissions = array();

/**
 * Area the user has authenticated for.
 * @var string
 */
private $areaname = '';

/**
 * Has the user authenticated successfully?
 * @var boolean
 */
private $isauthenticated = false;

/**
 * Class constructor
 * Silently starts a session and checks to see whether the session has
 * made a valid authentication for the given area. If it has then {@link isAuthenticated()}
 * will return true.
 * @param string $areaname Area that this Auth object will pertain to
 */
public function __construct($areaname) {
	// get the authcontainers. This is set in Prefs
	$authcontainers = new AuthContainers;
	$this->authcontainers = $authcontainers->getAll();
	
	$this->areaname = $areaname;
	@session_start();
	if (Validate::iss($_SESSION['authid']) == true &&
		Validate::iss($_SESSION['areaname']) == $this->areaname &&
		Validate::iss($_SESSION['authcontainer']) == true
	) {
		$this->activecontainer = $this->authcontainers[$_SESSION['authcontainer']]->loadByID($_SESSION['authid']);
		
		if (!$this->activecontainer) {
			unset($_SESSION['authid'], $_SESSION['areaname'], $_SESSION['authcontainer']);
			throw new DStructGeneralException('Perm::__construct() - Unable to load active container. The user may have been deleted.');
		}
		$this->permissions = $this->activecontainer->permissions();
		$this->isauthenticated = true;
	}
}

/**
 *Authenticate a user within a given area.
 *
 *Calls authenticate() method of each AuthContainer until it finds a match. To authenticate, a user
 *within the AuthContainers collection must have a valid username and password <i>and</i> belong
 *to at least one group.
 *@param string $username The username of the client to authenticate
 *@param string $password
 *@param string $areaname The area that the client is to be authenticated within
 *@return boolean True on success, False on failure
 */
public function authenticate($username, $password, $areaname) {
	foreach ($this->authcontainers as $key => $container) {
		if ($container = $container->authenticate($username, $password)) {
			if ($container->hasGroups()) {
				$this->activecontainer = $container;
				$this->permissions = $this->activecontainer->permissions();
				$this->isauthenticated = true;
				session_regenerate_id();
				$_SESSION['authid'] = $this->activecontainer->getID();
				$_SESSION['areaname'] = $areaname;
				$_SESSION['authcontainer'] = get_class($this->activecontainer);
				return true;
			}
		}
	}
	return false;
}

/**
 *Returns the ID of the active container, or false if no active container.
 *@return string|false
 */
public function authID() {
	if (!$this->activecontainer) {return false;}
	return $this->activecontainer->getID();
}

/**
 *Returns name of the active container class or false if no active container.
 *@return string|false
 */
public function authName() {
	if (!$this->activecontainer) {return false;}
	return get_class($this->activecontainer);
}

/**
 *Returns the active container object or false if no active container.
 *@return object|false
 */
public function getActiveAuth() {
	if (!$this->activecontainer) {return false;}
	return $this->activecontainer;
}

/**
 * Get the user object.
 * @param string $container
 * @param string $id
 * @return boolean
 */
public function getUser($container, $id) {
	if (!array_key_exists($container, $this->authcontainers)) {return false;} // this authcontainer is not available
	return $container::loadByID($id);
}

/**
 *Test whether the authenticated user has a right.
 *
 * All rights are strings e.g. edit_gallery If a user has the right to 'edit the gallery' then
 * true would be returned, otherwise false.
 * @param string $rightname
 * @return boolean
 */
public function hasRight($rightname) {
	if (!$rightname) {throw new DStructGeneralException("clpPerm::hasRight() - no right name provided");}
	if (!$this->permissionsloaded) {$this->loadPermissions();}
	if (in_array($rightname, $this->permissions)) {return true;}
	// used to identify the script which failed the authentication as $_SERVER['HTTP_REFERER'] only shows last **HTTP** request!
	// Typically used in logging
	$_SESSION['clpfailscript'] = $_SERVER['SCRIPT_NAME'];
	return false;
}

/**
 * Is the user authenticated.
 *
 * Authentication is stored in $_SESSION and is then validated against the AuthContainers and the Area
 * to stop a user crossing over from one area to another by being authenticated on the first area but not
 * the second.
 * @return boolean
 */
public function isAuthenticated() {return $this->isauthenticated;}

/**
 * Load the current permissions into the object.
 */
private function loadPermissions() {
	if ($this->permissionsloaded) {return;}
	//$mem = DStructMemCache::getInstance();
	//if (!$this->permissions = $mem->get(md5(get_class($this->activecontainer).$this->areaname.$this->activecontainer->getID()))) {
		$this->permissions = $this->activecontainer->permissions();
	//	$mem->set(md5(get_class($this->activecontainer).$this->areaname.$this->activecontainer->getID()), $this->permissions);
	//}
	$this->permissionsloaded = true;
}

/**
 *Unsets the session and logs the user out.
 */
public function logOut() {
	$this->isauthenticated = false;
	unset($_SESSION['clpauthid'], $_SESSION['clpareaname'], $_SESSION['clpauthcontainer']);
	session_regenerate_id();
}

/**
 *Returns the array of current permissions.
 *@return array
 */
public function permissions() {
	$this->loadPermissions();
	return $this->permissions;
}

/**
 * Does a username exist in any of the authcontainers?
 * 
 * @param string $username
 * @return boolean
 */
public function usernameExists($username) {
	foreach ($this->authcontainers as $authcontainer) {
		// aarrgh... call should really be on the Collection Object for the container, but we have no knowledge of that!!
		if ($authcontainer->usernameExists($username)) {return true;}
	}
	return false;
}

}
?>