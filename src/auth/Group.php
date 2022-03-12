<?php
/**
 * Group class
 */
/**
 * Users belong to one or more Group which in turn grants permissions.
 * @package dstruct_auth
 */
class Group {

/**
 * ID of the object.
 * @var integer
 */
private $id;

/**
 * Name of the group.
 * @var string
 */
private $title;

/**
 * The group permissions.
 * @var array
 */
private $permissions = array();

/**
 * The Rights object of the group.
 * @var null|object
 */
private $rights = null;

/**
 * Class constructor.
 * @param string $row Row from the database
 */
public function __construct($row = false) {
	if ($row != false) {
		$this->id = $row['dsGroupID'];
		$this->title = $row['Title'];
	}
}

/**
 *Get the object ID.
 *@return integer
 */
public function getID() {return $this->id;}

/**
 *Add a {@link Right} to this group.
 *@param Right $right
 */
public function addRight(Right $right) {
	$this->loadRights();
	AuthDataManager::insertGroupRight($this->id, $right->getID());
	$this->rights->add($right);
}

/**
 *Get a collection object of the {@link Right} objects for this group.
 *@return object
 */
public function getRights() {
	$this->loadRights();
	return $this->rights;
}

/**
 *The name of the group.
 *@param boolean $raw Return raw or html encoded
 */
public function getTitle($raw = false) {
	if ($raw) {return $this->title;}
	return html_specialchars($this->title);
}

/**
 *Load object by its ID.
 *@param integer $id
 *@param array $row Array of data from database
 *@return false|object False if object not found
 */
public static function loadByID($id, $row = false) {
	if (!is_numeric($id)) {return false;}
	if ($class = ObjWatcher::exists(__CLASS__, $id)) {return $class;}
	if (!$row) {
		$rs = AuthDataManager::loadGroup($id);
		if ($rs->count() == 0) {return false;}
		foreach ($rs as $record) {$row = $record;}
	}
	$class = new Group($row);
	ObjWatcher::add($class);
	return $class;
}

/**
 * Load the Rights for this group.
 */
private function loadRights() {
	if (!$this->rights) {
		$this->rights = new Rights;
		$this->rights->loadByGroup($this);
	}
}

/**
 *Return the permissions associated with this group.
 *@return array
 */
public function permissions() {
	$rights = $this->getRights();
	foreach ($rights as $right) {
		$this->permissions = array_merge($this->permissions, $right->permissions());
	}
	$this->permissions = array_unique($this->permissions);
	return $this->permissions;
}

/**
 *Remove a {@link Right} from the group
 *@param Right $right {@link Right}
 */
public function removeRight(Right $right) {
	$this->loadRights();
	AuthDataManager::deleteGroupRight($this->id, $right->getID());
	$this->rights->remove($right);
}

/**
 * Save to the database.
 */
public function save() {
	if ($this->id) {
		AuthDataManager::updateGroup($this->title, $this->id);
	} else {
		$this->id = AuthDataManager::insertGroup($this->title);
	}
}

/**
 *Set the title for this group.
 *@param string $title
 */
public function setTitle($title) {$this->title = $title;}

}
?>