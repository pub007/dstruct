<?php
/**
 * TreeNodes class
 */
/**
 * {@link TreeNode} collection object.
 * @package dstruct_tree
 * @author David
 */
class TreeNodes extends ObjCollection {

/**
 * Loads all nodes of a tree which are accepting members.
 * @param Tree|integer $tree Tree object or its ID
 */
public function loadAllAcceptingMembers($tree) {
	parent::clear();
	if (is_object($tree)) {$tree = $tree->getID();}
	$rs = TreeNodeDataManager::loadAllAcceptingMembers($tree);
	foreach ($rs as $row) {
		parent::add(TreeNode::loadByID($row['TreeNodeID'], $row));
	}
}

// 
/**
 * Load nodes by their parent (node / Tree).
 * 
 * If no parent is provided, loads the top level nodes for the tree.
 * If no tree (or 0) is given, all nodes from all trees are loaded.
 * @param TreeNode|integer $parent TreeNode object or its ID
 * @param Tree|integer $tree Tree object or its ID
 */
public function loadByParent($parent = false, $tree = false) {
	parent::clear();
	if (is_object($parent)) {$parent = $parent->getID();}
	if (is_object($tree)) {$tree = $tree->getID();}
	$rs = TreeNodeDataManager::loadByParent($parent, $tree);
	foreach ($rs as $row) {
		parent::add(TreeNode::loadByID($row['TreeNodeID'], $row));
	}
}

/**
 * Load all nodes by their level for a given Tree.
 * @param integer $level The level to load
 * @param Tree $tree Tree object or its ID
 */
public function loadByLevel($level, $tree) {
	parent::clear();
	if (is_object($tree)) {$tree = $tree->getID();}
	$rs = TreeNodeDataManager::loadByLevel($level, $tree);
	foreach ($rs as $row) {
		parent::add(TreeNode::loadByID($row['TreeNodeID'], $row));
	}
}

}
?>