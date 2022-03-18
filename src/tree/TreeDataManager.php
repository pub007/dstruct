<?php
namespace pub007\dstruct\tree;
/**
 * TreeDataManager class
 */
/**
 * Manages database access for {@link Tree} class.
 * @package dstruct_tree
 * @author David
 */
class TreeDataManager extends Base {

/**
 * SQL.
 * @var string
 */
private static $insert =
	'INSERT INTO tree (TreeName, MaxNodeDepth, SortOrder) VALUES (?, ?, ?)';

/**
 * SQL.
 * @var string
 */
private static $load =
	'SELECT TreeID,
		TreeName,
		MaxNodeDepth,
		SortOrder
	 FROM tree
	 WHERE TreeID = ?';

/**
 * SQL.
 * @var string
 */
private static $load_all =
	'SELECT TreeID,
		TreeName,
		MaxNodeDepth,
		SortOrder
	 FROM tree
	 ORDER BY SortOrder';
	
/**
 * SQL.
 * @var string
 */
private static $update =
	'UPDATE tree
	 SET TreeName = ?,
		 MaxNodeDepth = ?,
		 SortOrder = ?
	 WHERE TreeID = ?';

/**
 * Insert Tree data.
 * @param array $data
 * @return integer
 */
public static function insert($data) {
	self::doStatement(self::$insert, $data);
	$dbs = DBSelector::getInstance();
	$db = $dbs->getConnection();
	return $db->lastInsertID();
}

/**
 * Load data for Tree object.
 * @param integer $id The id of the Tree to load
 * @return DBIterator
 */
public static function load($id) {
	$rs = self::doStatement(self::$load, array($id));
	return new DBIterator($rs);
}

/**
 * Load all Trees.
 * @return DBIterator
 */
public static function loadAll() {
	$rs = self::doStatement(self::$load_all, array());
	return new DBIterator($rs);
}

/**
 * Update Tree
 * @param array $data Data for object being updated
 */
public static function update($data) {
	self::doStatement(self::$update, $data);
}

}
?>