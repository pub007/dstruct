<?php
/**
 * Right class
 */
/**
 *A right to do or access something.
 *
 *A right is accessed from the system by its {@link $identname}, not from its
 *numeric ID.
 *@package dstruct_auth
 */
class Right {

/**
 * Object ID
 * @var integer
 */
private $id;

/**
 * The identifying name for the Right.
 * @var string
 * @see getIdentName()
 */
private $identname;

/**
 * Name of the Right.
 * @var string
 * @see getTitle()
 */
private $title;

/**
 * Collection of ImpliedRight objects.
 * @var null|object
 */
private $impliedrights;

/**
 * Class constructor.
 * @param mixed $row Database row or false.
 */
public function __construct($row = false) {
	if ($row != false) {
		$this->id = $row['dsRightID'];
		$this->identname = $row['IdentName'];
		$this->title = $row['Title'];
	}
}

/**
 * Add an Implied Right inherited when user has this right.
 * @param Right $right
 */
public function addImpliedRight(Right $right) {
	$this->loadImpliedRights();
	AuthDataManager::insertImpliedRight($this->id, $right->getID());
	$this->impliedrights->add($right);
}

/**
 * Return the ID of this object.
 * @return integer
 */
public function getID() {return $this->id;}

/**
 * Identifier used when accessing this right from script.
 * 
 * This would typically be something readable such as 'access_reports' or
 * 'edit_invoices'.
 * @return string
 */
public function getIdentName() {return $this->identname;}

/**
 *Any rights implied by ownership of this right.
 *@return object A collection object
 */
public function getImpliedRights() {
	$this->loadImpliedRights();
	return $this->impliedrights;
}

/**
 * Return the Title of this right.
 * 
 * Typically something which would be displayed to users such as 'Access Reports'
 * or 'Edit Invoices'.
 * @param boolean $raw Return raw or html encoded
 * @return string
 */
public function getTitle($raw = false) {
	if ($raw) {return $this->title;}
	return html_specialchars($this->title);
}

/**
 *Load object by its ID.
 *@param integer $id
 *@param array $row Data for creating an instance of this class
 *@return false|object False if object not found
 */
public static function loadByID($id, $row = false) {
	if (!is_numeric($id)) {return false;}
	if (!$row) {
		$rs = AuthDataManager::loadRight($id);
		if ($rs->count() == 0) {return false;}
		foreach($rs as $record) {$row = $record;}
	}
	if ($class = ObjWatcher::exists(__CLASS__, $id)) {return $class;}
	$class = new Right($row);
	ObjWatcher::add($class);
	return $class;
}

/**
 *Lazy loader for Object Collection.
 */
private function loadImpliedRights() {
	if ($this->impliedrights) {return;}
	$this->impliedrights = new Rights;
	$this->impliedrights->loadByRight($this);
}

/**
 * Returns array of permissions granted and implied by this right.
 * @return array
 */
public function permissions() {
	$this->loadImpliedRights();
	$this->permissions[] = $this->identname;
	$rights = $this->getImpliedRights();
	foreach ($rights as $right) {
		if (!in_array($right->getIdentName(), $this->permissions)) {$this->permissions[] = $right->getIdentName();}
	}
	return $this->permissions;
}

/**
 * Remove an Implied Right granted by ownership of this right.
 * @param Right $right
 */
public function removeImpliedRight(Right $right) {
	$this->loadImpliedRights();
	AuthDataManager::deleteImpliedRight($this->id, $right->getID());
	$this->impliedrights->remove($right);
}

/**
 * Save in the database.
 */
public function save() {
	if ($this->id) {
		AuthDataManager::updateRight(array(
			$this->identname,
			$this->title,
			$this->id
		));
	} else {
		$this->id = AuthDataManager::insertRight(array(
			$this->identname,
			$this->title
		));
	}
}

/**
 * Set the IdentName by which this right is identified in script.
 *
 * Should be all lower-case and only contain alpha-numerics and underscore
 * @param string $name
 */
public function setIdentName($name) {$this->identname = $name;}

/**
 * Set the easily identifiable name for this right.
 * @param string $title
 */
public function setTitle($title) {$this->title = $title;}

}
?>