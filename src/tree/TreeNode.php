<?php
/**
 * TreeNode class
 */
/**
 * Nodes of a {@link Tree} structure.
 * 
 * While the Tree is the overarching container of the nodes. The
 * nodes themselves are the actual structure and organisation and contain
 * the members. The members are usually programmer created objects which
 * impliment {@link TreeNodeMemberInterface}.
 * Maximum level of nodes = 255
 * 
 * @package dstruct_tree
 * @author David
 */
class TreeNode {

/**
 * This node accepts members?
 * @var integer 1 or 0
 */
private $acceptsmembers = 1;

/**
 * Children of the node.
 * @var TreeNodes
 */
private $children = NULL;

/**
 * ID of the node.
 * @var integer
 */
private $id;

/**
 * Depth level of the node.
 * @var integer
 */
private $level = 1;

/**
 * Members of this node.
 * @var Object Usually a user written object collection.
 */
private $members;

/**
 * Name of the node.
 * @var string
 */
private $name;

/**
 * Parent object of this node, if there is one.
 * @var object
 */
private $parent = NULL;

/**
 * ID of parent node if there is one.
 * @var integer
 */
private $parentid = 0;

/**
 * Sort order of this node.
 * @var integer
 * @see setSortOrder()
 */
private $sortorder = 0;

/**
 * Tree object this node is part of.
 * @var Tree
 */
private $tree = NULL;

/**
 * ID of the Tree object this node is part of.
 * @var integer
 */
private $treeid = 0;

/**
 * Class constructor
 * @param array $row Data to populate object with.
 */
public function __construct($row = false) {
	if ($row != false) {
		$this->id = $row['TreeNodeID'];
		$this->treeid = $row['TreeID'];
		$this->parentid = $row['TreeNodeParentID'];
		$this->name = $row['TreeNodeName'];
		$this->level = $row['Level'];
		$this->acceptsmembers = $row['AcceptsMembers'];
		$this->sortorder = $row['SortOrder'];
	}
}

// ?? do this? need to be able to accept name etc?
/**
 * Add a child directly to the node.
 * @todo Impliment addChild()
 */
public function addChild() {}

/**
 * Does node accept members?
 * @return boolean
 * @see setAcceptsMembers()
 */
public function getAcceptsMembers() {
	return ($this->acceptsmembers)? true : false;
}

/**
 * Get any children of the node.
 * @return TreeNodes
 */
public function getChildren() {
	$this->loadChildren();
	return $this->children;
}

/**
 * Get the ID of the node.
 * @return integer
 */
public function getID() {
	return $this->id;
}

/**
 * Get the depth of the node within the structure.
 * 
 * Maximum allowable level is 255.
 * @return integer
 */
public function getLevel() {
	return $this->level;
}

/**
 * Get the name of the node.
 * @param string $raw HTML encoded or raw.
 * @return string
 */
public function getName($raw = false) {
	if ($raw) {return $this->name;}
	return html_specialchars($this->name);
}

/**
 * Get the nodes which form the path to this node.
 * @return TreeNodes
 * @todo Impliment getNodesToRoot()
 */
public function getNodesToRoot() {}

/**
 * Get the parent of this node.
 * @return object
 */
public function getParent() {
	$this->loadParent();
	return $this->parent;
}

/**
 * Get the ID of the parent of this node.
 * @return integer
 */
public function getParentID() {
	return $this->parentid;
}

/**
 * Get nodes from the part of the Tree structure.
 * @todo Impliment getSiblings()
 */
public function getSiblings() {}

/**
 * Get the sort order of the node.
 * 
 * Trees can be ordered and given preference in the syste. A lower number is a
 * higher preference.
 * @todo Define the acceptable range
 * @return number
 */
public function getSortOrder() {
	return $this->sortorder;
}

/**
 * Get the Tree this node belongs to.
 * @return Tree
 */
public function getTree() {
	$this->loadTree();
	return $this->tree;
}

/**
 * Get the ID of the Tree this node belongs to.
 * @return integer
 */
public function getTreeID() {
	return $this->treeid;
}

/**
 * Does this node have any children?
 * @return boolean
 */
public function hasChildren() {
	$this->loadChildren();
	return ($this->children->count())? true : false;
}

/**
 * Insert into database.
 */
private function insert() {
	$this->id = TreeNodeDataManager::insert(array(
		$this->treeid,
		$this->parentid,
		$this->name,
		$this->level,
		$this->acceptsmembers,
		$this->sortorder
	));
}

/**
 * Load the TreeNode by its ID
 * @param integer $id ID of object to load
 * @param array $row Data to populate object
 * @return false|TreeNode False if can't find object with that ID
 */
public static function loadByID($id, $row = false) {
	// check numeric, as MySQL will cast the variable to a ?double? and so variables such as
	// '1stdsf' will cast to 1 and return a record rather than false!!
	if (!is_numeric($id)) {return false;}
	if ($class = ObjWatcher::exists(__CLASS__, $id)) {return $class;}
	if (!$row) {
		$rs = TreeNodeDataManager::load($id);
		if ($rs->count() == 0) {return false;}
		foreach ($rs as $record) {$row = $record;}
	}
	$class = new TreeNode($row);
	ObjWatcher::add($class);
	return $class;
}

/**
 * Lazy loader for the children of this node.
 */
private function loadChildren() {
	if (!$this->children) {
		$this->children = new TreeNodes;
		$this->children->loadByParent($this->id);
	}
}

/**
 * Lazy loader for the parent of this node.
 */
private function loadParent() {
	if (!$this->parent && $this->parentid) {
		$this->parent = TreeNode::loadByID($this->parentid);
	}
}

/**
 * Lazy loader for the Tree of this node.
 */
private function loadTree() {
	if (!$this->tree) {
		$this->tree = Tree::loadByID($this->treeid);
	}
}

/**
 * Save the node in the database.
 */
public function save() {
	if ($this->id) {
		$this->update();
	} else {
		$this->insert();
	}
}

/**
 * Set whether this node accepts members.
 * 
 * Nodes will usually be populated with members which are defined
 * by the user and impliment {@link TreeNodeMemberInterface}. It
 * may be desirable to restrict the ability for members to be added
 * to a node, however, Set this to false to stop any members being
 * added.
 * @param boolean $accepts
 */
public function setAcceptsMembers($accepts) {
	$this->acceptsmembers = ($accepts)? 1 : 0;
}

/**
 * Set the name of the node.
 * @param string $name
 */
public function setName($name) {
	$this->name = $name;
}

/**
 * Set the parent node, if any.
 * 
 * @param TreeNode|integer $parent Object or its ID
 * @throws DStructGeneralException
 */
public function setParent($parent) {
	if (is_object($parent)) {
		$this->parent = $parent;
		$this->parentid = $parent->getID();
	} else {
		$this->parentid = $parent;
		$this->loadParent();
	}
	$this->setTree($this->parent->getTreeID());
	$this->level = $this->parent->getLevel() + 1;
	if ($this->level > 255) {throw new DStructGeneralException('TreeNode::setParent() - Maximum 255 levels. Attempted to set: ' . html_specialchars($this->level));}
}

/**
 * Set the sort order of the node.
 * 
 * Nodes can be ordered and given preference in the system. A lower number is a
 * higher preference.
 * @param integer $sortorder
 * @todo Define the acceptable range
 * @throws DStructGeneralException
 */
public function setSortOrder($sortorder) {
	if (!is_numeric((string) $sortorder)) {throw new DStructGeneralException('TreeNode::setSortOrder() - $sortorder param must be numeric');}
	$this->sortorder = $sortorder;
}

/**
 * Set the Tree this node belongs to.
 * @param Tree|integer $tree The Tree object or its ID
 */
public function setTree($tree) {
	if (is_object($tree)) {
		$this->tree = $tree;
		$this->treeid = $tree->getID();
	} else {
		$this->tree = null;
		$this->treeid = $tree;
	}
}

/**
 * Update the node in the database.
 */
private function update() {
	TreeNodeDataManager::update(array(
		$this->treeid,
		$this->parentid,
		$this->name,
		$this->level,
		$this->acceptsmembers,
		$this->sortorder,
		$this->id
	));
}

}
?>