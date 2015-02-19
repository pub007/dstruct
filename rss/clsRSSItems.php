<?php
/**
 * RSSItems class
 */
/**
 * RSSItem collection object.
 * @package dstruct_rss
 * @author Shane
 */
class RSSItems extends ObjCollection {

/**
 * Add item to collection.
 * @param RSSItem $item
 * @return boolean
 */
public function addItem(RSSItem $item){
	parent::add($item);
	return true;
}

}
?>