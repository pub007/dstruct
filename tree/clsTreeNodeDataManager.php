<?php
/**
 * TreeNodeDataManager class
 */
/**
 * DataManager for {@link TreeNode} class
 * @package dstruct_tree
 * @author David
 */
class TreeNodeDataManager extends Base {

/**
 * SQL.
 * @var string
 */
private static $insert =
	'INSERT INTO treenode (TreeID, TreeNodeParentID, TreeNodeName, Level, AcceptsMembers, SortOrder) 
	 VALUES (?, ?, ?, ?, ?, ?)';

/**
 * SQL.
 * @var string
 */
private static $load =
	'SELECT TreeNodeID,
		TreeID,
		TreeNodeParentID,
		TreeNodeName,
		Level,
		AcceptsMembers,
		SortOrder
	 FROM treenode
	 WHERE TreeNodeID = ?';

/**
 * SQL.
 * @var string
 */
private static $load_all_accepting_members =
	'SELECT TreeNodeID,
		TreeID,
		TreeNodeParentID,
		TreeNodeName,
		Level,
		AcceptsMembers,
		SortOrder
	 FROM treenode
	 WHERE TreeID = ?
	 AND AcceptsMembers = 1';

/**
 * SQL.
 * @var string
 */
private static $load_by_level =
	'SELECT TreeNodeID,
		TreeID,
		TreeNodeParentID,
		TreeNodeName,
		Level,
		AcceptsMembers,
		SortOrder
	 FROM treenode
	 WHERE TreeID = ?
	 AND Level = ?';

/**
 * SQL.
 * @var string
 */
private static $update =
	'UPDATE treenode
	 SET TreeID = ?,
		 TreeNodeParentID = ?,
		 TreeNodeName = ?,
		 Level = ?,
		 AcceptsMembers = ?,
		 SortOrder = ?
	 WHERE TreeNodeID = ?';

/**
 * Insert object data into database.
 * @param array $data Data for object
 * @return integer ID of the inserted row
 */
public static function insert($data) {
	self::doStatement(self::$insert, $data);
	$dbs = DBSelector::getInstance();
	$db = $dbs->getConnection();
	return $db->lastInsertID();
}

/**
 * Load the object data
 * @param integer $id ID to load data for.
 * @return DBIterator
 */
public static function load($id) {
	$rs = self::doStatement(self::$load, array($id));
	return new DBIterator($rs);
}

/**
 * Load all node objects accepting members
 * @param integer $id ID of {@link Tree} to load nodes for.
 * @return DBIterator
 */
public static function loadAllAcceptingMembers($id) {
	$rs = self::doStatement(self::$load_all_accepting_members, array($id));
	return new DBIterator($rs);
}

/**
 * Load nodes by level.
 * @param integer $level Level of nodes to load.
 * @param integer $tree ID of {@link Tree} to load nodes for.
 * @return DBIterator
 */
public static function loadByLevel($level, $tree) {
	$rs = self::doStatement(self::$load_by_level, array($level, $tree));
	return new DBIterator($rs);
}

/**
 * Load node by its parent.
 * @param integer $parent ID of parent node
 * @param integer $tree ID of Tree
 * @return DBIterator
 */
public static function loadByParent($parent, $tree) {
	$sql = 'SELECT TreeNodeID,
				TreeID,
				TreeNodeParentID,
				TreeNodeName,
				Level,
				AcceptsMembers,
				SortOrder
			FROM treenode';
	
	$where = '';
	$data = array();
	
	if ($parent) {
		$where = ' WHERE TreeNodeParentID = ?';
		$data[] = $parent;
	}
	
	if ($tree) {
		if ($where) {
			$where .= ' AND TreeID = ? AND TreeNodeParentID = 0';
		} else {
			$where .= ' WHERE TreeID = ? AND TreeNodeParentID = 0';
		}
		$data[] = $tree;
	}
	
	$sql .= $where . ' ORDER BY SortOrder';
	
	$rs = self::doStatement($sql, $data);
	return new DBIterator($rs);
}

/**
 * Update the object's data in the database.
 * @param array $data
 */
public static function update($data) {
	self::doStatement(self::$update, $data);
}

}
?>