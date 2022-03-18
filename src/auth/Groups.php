<?php
namespace pub007\dstruct\auth;
/**
 * Groups class
 */
/**
 * Collection of Group objects
 * @package dstruct_auth
 */
class Groups extends ObjCollection {

/**
 * Add Group to collection.
 * @param Group $obj
 * @see ObjCollection::add()
 */
public function add($obj) {
	parent::add($obj);
}

/**
 * Attach an AuthContainer to a Group.
 * @param string $containername
 * @param string $containerid
 * @param mixed $group Group object or ID of a Group object
 */
public static function attachAuthContainerToGroup($containername, $containerid, $group) {
	$groupid = (is_object($group))? $group->getID() : $group;
	AuthDataManager::attachAuthContainerToGroup($containername, $containerid, $groupid);
}

/**
 *Load all groups into the collection.
 */
public function loadAll() {
	parent::clear();
	$rs = AuthDataManager::loadAllGroups();
	foreach ($rs as $row) {
		parent::add(Group::loadByID($row['dsGroupID'], $row));
	}
}

// takes array of IDs
/**
 *Load groups into the collection by their IDs.
 *@param array $groups
 */
public function loadByArray($groups) {
	parent::clear();
	foreach ($groups as $group) {
		parent::add(Group::loadByID($group));
	}
}

/**
 *Load the groups an authcontainer object belongs to.
 *@param object $container Container object
 */
public function loadByAuthContainer($container) {
	parent::clear();
	$rs = AuthDataManager::loadGroupsByAuthContainer(get_class($container), $container->getID());
	foreach ($rs as $row) {
		parent::add(Group::loadByID($row[0]));
	}
}

/**
 * Remove Group from collection.
 * @param Group $obj
 * @see ObjCollection::remove()
 */
public function remove($obj) {
	parent::remove($obj);
}

/**
 * Remove an AuthContainer from a Group
 * @param string $containername
 * @param string $containerid
 * @param mixed $group Group object or Group object's ID
 */
public static function removeAuthContainerFromGroup($containername, $containerid, $group) {
	$groupid = (is_object($group))? $group->getID() : $group;
	AuthDataManager::removeAuthContainerFromGroup($containername, $containerid, $groupid);
}

}
?>