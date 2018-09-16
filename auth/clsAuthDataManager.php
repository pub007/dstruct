<?php
/**
 * AuthDataManager class
 */
/**
 * DataManager class for Auth system.
 * @package dstruct_auth
 * @todo Split out into separate DataManagers?
 */
class AuthDataManager extends Base {

/**
 * SQL.
 * @var string
 */
private static $attach_authcontainer_to_group =
	'INSERT INTO clpgroup_authcontainer
		(AuthContainerName, dsGroupID, AuthContainerID)
	 VALUES
		(?, ?, ?)';

/**
 * SQL.
 * @var string
 */
private static $delete_group_right = 
	'DELETE FROM dsgroup_dsright
	 WHERE dsGroupID = ?
		AND dsRightID = ?
	 LIMIT 1';

/**
 * SQL
 * @var string
 */
private static $delete_implied_right = 
	'DELETE FROM dsimpliedright
	 WHERE dsRightID = ?
		AND ImpliedRight = ?';

/**
 * SQL
 * @var string
 */
private static $insert_authcontainer = 
	'INSERT INTO authcontainer (AuthContainerName) VALUES (?)';

/**
 * SQL
 * @var string
 */
private static $insert_group =
	'INSERT INTO dsgroup (Title) VALUES (?)';

/**
 * SQL
 * @var string
 */
private static $insert_group_right = 
	'INSERT INTO dsgroup_dsright
		(dsGroupID, dsRightID)
	 VALUES (?, ?)';

/**
 * SQL
 * @var string
 */
private static $insert_implied_right = 
	'INSERT INTO dsimpliedright
		(dsRightID, ImpliedRight)
	 VALUES (?, ?)';

/**
 * SQL
 * @var string
 */
private static $insert_right = 
	'INSERT INTO dsright
		(IdentName, Title)
	 VALUES
		(?, ?)';

/**
 * SQL
 * @var string
 */
private static $load_all_authcontainers = 
	'SELECT AuthContainerName
	 FROM authcontainer
	 ORDER BY AuthContainerName';

/**
 * SQL
 * @var string
 */
private static $load_all_groups = 
	'SELECT dsGroupID, Title
	 FROM dsgroup
	 ORDER BY Title';

/**
 * SQL
 * @var string
 */
private static $load_all_rights =
	'SELECT dsRightID, IdentName, Title
	 FROM dsright
	 ORDER BY IdentName';

/**
 * SQL
 * @var string
 */
private static $load_authcontainer_by_id = 
	'SELECT AuthContainerName
	 FROM authcontainer
	 WHERE AuthContainerName = ?';

/**
 * SQL
 * @var string
 */
private static $load_authcontainer_group =
	'SELECT dsGroup_AuthContainerID
	 FROM dsgroup_authcontainer
	 WHERE AuthContainerName = ?
		AND dsGroupID = ?
		AND AuthContainerID = ?';

/**
 * SQL
 * @var string
 */
private static $load_group = 
	'SELECT dsGroupID, Title
	 FROM dsgroup
	 WHERE dsGroupID = ?';

/**
 * SQL.
 * @var string
 */
private static $load_groups_by_auth_container =
	'SELECT dsGroupID
	 FROM dsgroup_authcontainer
	 WHERE AuthContainerName = ?
		AND AuthContainerID = ?';

/**
 * SQL.
 * 
 * We need to return a row with all the right details to create the object of we don't want round trips to the
// db for every object created. So... we do the join
 * @var string
 */
private static $load_implied_rights_by_right = 
	'SELECT dsimpliedright.ImpliedRight,
		dsright.dsRightID,
		dsright.IdentName, 
		dsright.Title
	 FROM dsimpliedright
	 LEFT JOIN dsright ON dsright.dsRightID = dsimpliedright.ImpliedRight
	 WHERE dsimpliedright.dsRightID = ?';

/**
 * SQL.
 * @var string
 */
private static $load_right = 
	'SELECT dsRightID, IdentName, Title
	 FROM dsright
	 WHERE dsRightID = ?';

/**
 * SQL.
 * @var string
 */
private static $load_rights_by_group =
	'SELECT dsgroup_dsright.dsRightID,
		dsright.IdentName, 
		dsright.Title
	 FROM dsgroup_dsright
	 LEFT JOIN dsright ON dsgroup_dsright.dsRightID = dsright.dsRightID
	 WHERE dsgroup_dsright.dsGroupID = ?';

/**
 * SQL.
 * @var string
 */
private static $remove_authcontainer_from_group =
	'DELETE FROM dsgroup_authcontainer
	 WHERE AuthContainerName = ?
		AND dsGroupID = ?
		AND AuthContainerID = ?';

/**
 * SQL.
 * @var string
 */
private static $update_auth_container = 
	'UPDATE authcontainer
	 SET AuthContainerName = ?
	 WHERE AuthContainerName = ?';

/**
 * SQL.
 * @var string
 */
private static $update_group = 
	'UPDATE dsgroup
	 SET Title = ?
	 WHERE dsGroupID = ?';

/**
 * SQL.
 * @var string
 */
private static $update_right =
	'UPDATE dsright
	 SET IdentName = ?,
		 Title = ?
	 WHERE dsRightID = ?';

/**
 * Attach a Group and an AuthContainer.
 * @param string $containername
 * @param string $containerid
 * @param integer $groupid
 * @return boolean
 */
public static function attachAuthContainerToGroup($containername, $containerid, $groupid) {
	// see if already exists
	$rs = self::doStatement(self::$load_authcontainer_group, array($containername, $groupid, $containerid));
	$rs = new DBIterator($rs);
	// if already exists then just exit
	if ($rs->count()) {return false;}
	self::doStatement(self::$attach_authcontainer_to_group, array($containername, $groupid, $containerid));
	$dbs = DBSelector::getInstance();
	$db = $dbs->getConnection();
	return $db->lastInsertId();
}

/**
 * Authenticate a user.
 * @param string $uname
 * @param string $pwd
 * @return DBIterator
 */
public static function authenticate($uname, $pwd) {
	$result = self::doStatement(self::$authenticate, array($uname, $pwd));
	return new DBIterator($result, PDO::FETCH_NUM);
}

/**
 * Delete a Right from a Group.
 * @param integer $groupid
 * @param integer $rightid
 */
public static function deleteGroupRight($groupid, $rightid) {
	self::doStatement(self::$delete_group_right, array($groupid, $rightid));
}

/**
 * Delete an Implied Right.
 * @param integer $rightid
 * @param integer $impliedid
 */
public static function deleteImpliedRight($rightid, $impliedid) {
	self::doStatement(self::$delete_implied_right, array($rightid, $impliedid));
}

public static function getTableName() {
    return 'fosteringcontactuser';
}

/**
 * Insert an AuthContainer.
 * @param string $id
 */
public static function insertAuthContainer($id) {
	self::doStatement(self::$insert_authcontainer, array($id));
}

/**
 * Insert a Group.
 * @param string $title
 * @return integer
 */
public static function insertGroup($title) {
	self::doStatement(self::$insert_group, array($title));
	$dbs = DBSelector::getInstance();
	$db = $dbs->getConnection();
	return $db->lastInsertId();
}

/**
 * Insert a Right for a Group.
 * @param integer $groupid
 * @param integer $rightid
 * @return integer
 */
public static function insertGroupRight($groupid, $rightid) {
	self::doStatement(self::$insert_group_right, array($groupid, $rightid));
	$dbs = DBSelector::getInstance();
	$db = $dbs->getConnection();
	return $db->lastInsertId();
}

/**
 * Insert and ImpliedRight.
 * @param integer $rightid
 * @param integer $impliedid
 * @return integer
 */
public static function insertImpliedRight($rightid, $impliedid) {
	self::doStatement(self::$insert_implied_right, array($rightid, $impliedid));
	$dbs = DBSelector::getInstance();
	$db = $dbs->getConnection();
	return $db->lastInsertId();
}

/**
 * Insert a Right.
 * @param array $data
 * @return integer
 */
public static function insertRight($data) {
	self::doStatement(self::$insert_right, $data);
	$dbs = DBSelector::getInstance();
	$db = $dbs->getConnection();
	return $db->lastInsertId();
}

/**
 * Load all AuthContainers.
 * @return DBIterator
 */
public static function loadAllAuthContainers() {
	$result = self::doStatement(self::$load_all_authcontainers, array());
	return new DBIterator($result, PDO::FETCH_NUM);
}

/**
 * Load all Groups.
 * @return DBIterator
 */
public static function loadAllGroups() {
	$result = self::doStatement(self::$load_all_groups, array());
	return new DBIterator($result);
}

/**
 * Load all Rights.
 * @return DBIterator
 */
public static function loadAllRights() {
	$result = self::doStatement(self::$load_all_rights, array());
	return new DBIterator($result);
}

/**
 * Load an AuthContainer by ID.
 * @param string $id
 * @return DBIterator
 */
public static function loadAuthContainerByID($id) {
	$result = self::doStatement(self::$load_authcontainer_by_id, array($id));
	return new DBIterator($result);
}

/**
 * Load Group by ID.
 * @param integer $id
 * @return DBIterator
 */
public static function loadGroup($id) {
	$result = self::doStatement(self::$load_group, array($id));
	return new DBIterator($result);
}

/**
 * Load the Groups for and AuthContainer.
 * @param string $containername
 * @param integer $containerid
 * @return DBIterator
 */
public static function loadGroupsByAuthContainer($containername, $containerid) {
	$result = self::doStatement(self::$load_groups_by_auth_container, array($containername, $containerid));
	return new DBIterator($result, PDO::FETCH_NUM);
}

/**
 * Load the ImpliedRights for a Right.
 * @param integer $id
 * @return DBIterator
 */
public static function loadImpliedRightsByRight($id) {
	$result = self::doStatement(self::$load_implied_rights_by_right, array($id));
	return new DBIterator($result);
}

/**
 * Load a Right by ID.
 * @param integer $id
 * @return DBIterator
 */
public static function loadRight($id) {
	$result = self::doStatement(self::$load_right, array($id));
	return new DBIterator($result);
}

/**
 * Load the Rights for a Group.
 * @param integer $groupid
 * @return DBIterator
 */
public static function loadRightsByGroup($groupid) {
	$result = self::doStatement(self::$load_rights_by_group, array($groupid));
	return new DBIterator($result);
}

/**
 * Load a user by ID.
 * @param integer $id
 * @return DBIterator
 */
public static function loadUser($id) {
	$result = self::doStatement(self::$load_user, array($id));
	return new DBIterator($result);
}

/**
 * Remove the link between a Group and AuthContainer.
 * @param unknown $containername
 * @param unknown $containerid
 * @param unknown $groupid
 */
public static function removeAuthContainerFromGroup($containername, $containerid, $groupid) {
	self::doStatement(self::$remove_authcontainer_from_group, array($containername, $groupid, $containerid));
}

/**
 * Update and AuthContainer.
 * @param string $newid
 * @param string $oldid
 */
public static function updateAuthContainer($newid, $oldid) {
	self::doStatement(self::$update_auth_container, array($newid, $oldid));
}

/**
 * Update a Group.
 * @param string $title
 * @param integer $id
 */
public static function updateGroup($title, $id) {
	self::doStatement(self::$update_group, array($title, $id));
}
/**
 * Update a Right.
 * @param array $data
 */
public static function updateRight($data) {
	self::doStatement(self::$update_right, $data);
}

}
?>