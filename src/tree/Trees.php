<?php
/**
 * Trees class
 */
/**
 * Object collection of {@link Tree} class.
 * @package dstruct_tree
 * @author David
 */
class Trees extends ObjCollection {

/**
 * Load all {@link Tree} objects.
 */
public function loadAll() {
	parent::clear();
	$rs = TreeDataManager::loadAll();
	foreach ($rs as $row) {
		parent::add(Tree::loadByID($row['TreeID'], $row));
	}
}
	
}
?>