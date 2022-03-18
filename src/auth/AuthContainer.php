<?php
namespace pub007\dstruct\auth;
/**
 * AuthContainer class
 */
/**
 * An available AuthContainer registered on the Auth system.
 * 
 * The Auth system requires the programmer to create a class which extends
 * {@link AuthContainerInterface}. These do the work of authentication against
 * anything the programmer wishes. The AuthContainer class 'registers' the
 * class written by the programmer allowing it to be used by the Auth system.
 * @package dstruct_auth
 */
class AuthContainer {

/**
 * ID of the object.
 * @var string
 */
private $id;

/**
 * Class constructor.
 * @param string $id
 */
public function __construct($id = false) {
	$this->id = $id;
}

/**
 * Get the object ID.
 * @return string
 */
public function getID() {return $this->id;}

/**
 * Insert into the database.
 * @throws DStructGeneralException
 */
public function insert() {
	if (!$this->id) {throw new DStructGeneralException('AuthContainer::insert() - Unable to insert without an ID');}
	AuthDataManager::insertAuthContainer($this->id);
}

/**
 * Load the object by the ID.
 * @param string $id
 * @return boolean|multitype:|AuthContainer
 */
public static function loadById($id) {
	$rs = AuthDataManager::loadAuthContainerByID($id);
	if ($rs->count() == 0) {return false;}
	if ($class = ObjWatcher::exists(__CLASS__, $id)) {return $class;}
	$class = new AuthContainer($id);
	ObjWatcher::add($class);
	return $class;
}

/**
 * Set the object ID.
 * @param string $id
 */
public function setID($id) {$this->id = $id;}

}
?>