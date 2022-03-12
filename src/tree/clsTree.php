<?php
/**
 * Tree class
 */
/**
 * Tree which contains Nodes.
 * 
 * The tree is the overarching container for a set of nodes.
 * The nodes are the actual structure within the tree. The nodes
 * in turn can have members which are the programmer-defined objects
 * the system is interested in 'positioning' of. Members should impliment
 * {@link TreeNodeMemberInterface}.
 * @package dstruct_tree
 * @author David
 */
class Tree {

/**
 * Tree ID.
 * @var integer
 */
private $id;

/**
 * Tree name.
 * @var string
 */
private $name = '';

/**
 * Max depth of nodes in this tree.
 * @var integer
 */
private $maxnodedepth = 1;

/**
 * Sort order of this tree.
 * @var integer
 * @see setSortOrder()
 */
private $sortorder = 0;

/**
 * Class constructor.
 * @param array $row Information to populate object.
 */
public function __construct($row = false) {
	if ($row != false) {
		$this->id = $row['TreeID'];
		$this->name = $row['TreeName'];
		$this->maxnodedepth = $row['MaxNodeDepth'];
		$this->sortorder = $row['SortOrder'];
	}
}

/**
 * Get the ID of this object.
 * @return integer
 */
public function getID() {
	return $this->id;
}

/**
 * Get name of this object
 * @param string $raw HTML formatted or raw.
 * @return string
 */
public function getName($raw = false) {
	if ($raw) {return $this->name;}
	return html_specialchars($this->name);
}

/**
 * Get maximum depth of nodes for this tree.
 * @return integer
 */
public function getMaxNodeDepth() {
	return $this->maxnodedepth;
}

/**
 * Returns all of the nodes in this tree which will accept members.
 * 
 * Useful if producing a 'flat' set of options e.g. a standard drop-down
 * As this just deligates to a TreeNodes object, this is just in Tree class for convenience - usually just make the call to TreeNodes yourself
 * @return TreeNodes
 */
public function getNodesAcceptingMembers() {
	$nodes = new TreeNodes;
	$nodes->loadAllAcceptingMembers($this);
	return $nodes;
}

/**
 * Get the sort order of the tree.
 * @return integer
 * @see setSortOrder()
 */
public function getSortOrder() {
	return $this->sortorder;
}

/**
 * Insert into database.
 */
private function insert() {
	$this->id = TreeDataManager::insert(array(
		$this->name,
		$this->maxnodedepth,
		$this->sortorder
	));
}

/**
 * Load the tree by its ID.
 * 
 * @param integer $id
 * @param array $row Data to populate object
 * @return false|Tree False if can't find tree with that ID
 */
public static function loadByID($id, $row = false) {
	// check numeric, as MySQL will cast the variable to a ?double? and so variables such as
	// '1stdsf' will cast to 1 and return a record rather than false!!
	if (!is_numeric($id)) {return false;}
	if ($class = ObjWatcher::exists(__CLASS__, $id)) {return $class;}
	if (!$row) {
		$rs = TreeDataManager::load($id);
		if ($rs->count() == 0) {return false;}
		foreach ($rs as $record) {$row = $record;}
	}
	$class = new Tree($row);
	ObjWatcher::add($class);
	return $class;
}

/**
 * Save the object in the database.
 */
public function save() {
	if ($this->id) {
		$this->update();
	} else {
		$this->insert();
	}
}

/**
 * Set the name of the tree
 * @param string $name
 */
public function setName($name) {
	$this->name = $name;
}

/**
 * Set the maximum depth of nodes in this tree.
 * @param integer $depth
 * @throws DStructGeneralException
 */
public function setMaxNodeDepth($depth) {
	if (!is_numeric((string) $depth)) {throw new DStructGeneralException('TreeNode::setMaxNodeDepth() - $depth param must be numeric');}
	$this->maxnodedepth = $depth;
}

/**
 * Set the sort order of the tree.
 * 
 * Trees can be ordered and given preference in the syste. A lower number is a
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
 * Update in the database.
 */
private function update() {
	TreeDataManager::update(array(
		$this->name,
		$this->maxnodedepth,
		$this->sortorder,
		$this->id
	));
}

}