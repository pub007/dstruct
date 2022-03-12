<?php
/**
 * TreeNodeMemberInterface interface
 */
/**
 * Programer defined objects which will be attached to a {@link TreeNode} should impliment this interface.
 * @package dstruct_tree
 * @author David
 */
interface TreeNodeMemberInterface {

/**
 * Get the {@link TreeNode} the object belongs to.
 * @return TreeNode
 */
public function getTreeNode() {}

}