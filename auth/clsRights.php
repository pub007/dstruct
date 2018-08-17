<?php
/**
 * Rights class
 */
/**
 * Collection of Right objects
 * @package dstruct_auth
 */
class Rights extends ObjCollection {

/**
 *Add a {@link Right} to the collection.
 *@param Right $right
 */
public function add($right) {
	parent::add($right);
}

/**
 *Load all {@link Right} objects in the database into the collection.
 */
public function loadAll() {
	parent::clear();
	$rs = AuthDataManager::loadAllRights();
	foreach ($rs as $row) {
		parent::add(Right::loadByID($row['dsRightID'], $row));
	}
}

/**
 * Load {@Right} objects by {@link Group}.
 * @param Group $group
 */
public function loadByGroup(Group $group) {
	parent::clear();
	$rs = AuthDataManager::loadRightsByGroup($group->getID());
	foreach ($rs as $row) {
		parent::add(Right::loadByID($row['dsRightID'], $row));
	}
}

/**
 * Load {@link Right} objects by Right (Implied Rights).
 * @param Right $right
 */
public function loadByRight(Right $right) {
	parent::clear();
	$rs = AuthDataManager::loadImpliedRightsByRight($right->getID());
	foreach ($rs as $row) {
		parent::add(Right::loadByID($row['dsRightID'], $row));
	}
}

/**
 *Remove an object from the collection.
 *@param Right $right {@link Right} object
 */
public function remove($right) {
	parent::remove($right);
}
}
?>