<?php
/**
 * ImpliedRights class
 */
/**
 * Collection of ImpliedRight objects
 * @package dstruct_auth
 */
class ImpliedRights extends ObjCollection {

/**
 * Load the impliedrights.
 * @param Right $right
 */
public function loadByRight(Right $right) {
	parent::clear();
	$rs = AuthDataManager::loadImpliedRightsByRight($right->getID());
}

}
?>