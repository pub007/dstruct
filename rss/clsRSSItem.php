<?php
/**
 * RSSItem class
 */
/**
 * Items in the RSS feed.
 * @package dstruct_rss
 * @author Shane
 * @todo Could do with a rewrite. Why ID and GUID etc?
 */
class RSSItem {

/**
 *Object Id.
 *@see __construct()
 *@var integer
 */
private $id;

/**
 * Global Unique ID.
 * @see getGuid()
 * @var string
 */
private $guid;

/**
 * Feed item title
 * @see getTitle()
 * @var string
 */
private $title;

/**
 * Feed item description.
 *@see getDescrip()
 *@var string
 */
private $descrip;

/**
 * Feed item link.
 *@see getLink()
 *@var string
 */
private $link;

/**
 * Publication date.
 * @see getPubdates()
 * @var string
 */
private $pubdate;

/**
 * Item author.
 * @see getAuthor()
 * @var string
 */
private $author;

/**
 * Item category.
 * @see getCategory()
 * @var string
 */
private $category;

/**
 * Item comments.
 * @see getComments()
 * @var string
 */
private $comments;

/**
 * Item source.
 * @see getSource()
 * @var string
 */
private $source;

/**
 * Set the Id of this item.
 * @see setID()
 * @return integer
 */
public function getID() {return $this->id;}

/**
 * Get the guid of this item.
 * @see setGuid()
 * @return string
 */
public function getGuid() {return $this->guid;}

/**
 * Get the Title of this item.
 * @see setTitle()
 * @return string
 */
public function getTitle() {return $this->title;}

/**
 * Get the Description of this item.
 * @see setDescrip()
 * @return string
 */
public function getDescrip() {return $this->descrip;}

/**
 *Get the Link of this item.
 *@see setLink()
 *@return string
 */
public function getLink() {return $this->link;}

/**
 *Get the Pub Date of this item.
 *@see setPubDate()
 *@return string
 */
public function getPubDate() {return $this->pubdate;}

/**
 *Get the Author of this item.
 *@see setAuthor()
 *@return string
 */
public function getAuthor() {return $this->author;}

/**
 * Get the Category of this item.
 * @see setCategory()
 * @return string
 */
public function getCategory() {return $this->category;}

/**
 * Get the Comments of this item.
 * @see setComments()
 * @return string
 */
public function getComments() {return $this->comments;}

/**
 *Get the Source of this item.
 *@see setSource()
 *@return string
 */
public function getSource() {return $this->source;}

/**
 *Set the Id of this item.
 *
 *An unique identification for the item 
 *@param integer $id
 */
public function setID($id) {$this->id = $id;}

/**
 *Set the Guid of this item.
 *
 *The guid is an element that contains a string that uniquely identifies the item
 *@param string $guid
 */
public function setGuid($guid) {$this->guid = $guid;}

/**
 *Set the Title of this item.
 *@param string $title
 */
public function setTitle($title) {$this->title = $title;}

/**
 *Set the Description of this item.
 *
 *Contains the main data for the item, this element is used for the body of the weblog post in this case
 *@param string $descrip
 */
public function setDescrip($descrip) {$this->descrip = $descrip;}

/**
 *Set the Link of this item.
 *
 *Contains a full URL to the individual page in which the specific item exists in detail
 *@param string $link
 */
public function setLink($link) {$this->link = $link;}

/**
 *Set the pub date of this item.
 *
 *The pubDate is the date that the item was published
 *Entered in UTS and formated to RFC822 
 *@param integer $pubdate
 */
public function setPubDateUTS($pubdate) {
	$this->pubdate = date('D, j F Y  H:i:s', $pubdate) . " GMT";
 	
} 

/**
 * Set the pub date of this item.
 * 
 * The pubDate is the date that the item was published
 * Can be entered in any format recognised by strtotime()
 * getPubDateString method changes to UTS and calls 
 * getPubDateUTS method to format
 * @param string $pubdate
 */
public function setPubDateString($pubdate) {
	$pubdate = strtotime($pubdate);
	if ($pubdate == false) {
		return false;
	} else {
		$this->pubdate = $this->setPubDateUTS($pubdate);
		return true;
	}
}

/**
 *Set the Author of this item.
 *
 *Represents the author of the content that is presented within this item group
 *@param string $author
 */
public function setAuthor($author) {$this->author = $author;}

/**
 *Set the Category of this item.
 *
 *Allows the item to be included into one ore more category
 *@param string $category
 */
public function setCategory($category) {$this->category = $category;}

/**
 *Set the comments of this item.
 *
 *URL of page that contains comments related to the item
 *@param string $comments
 */
public function setComments($comments) {$this->comments = $comments;}

/**
 *Set the surce of this item.
 *
 *The RSS channel that the item came from
 *@param string $source
 */
public function setSource($source) {$this->source = $source;}


/**
 *Class Constructor.
 *
 *If an Id is supplied then it is set, if not then an Id is randomly generated and set
 *@param string $id
 */
public function __construct($id = false) {
	if ($id) {
		$this->id = $id;
	} else {
		$this->id = dechex(rand(100,10000));
	}
}

/**
 *Item Validator.
 *
 *Returns false if an Item title or an Item description have not been entered
 */
public function validate() {
	if (!$this->title && !$this->descrip) {
		trigger_error('clsRSSFeed - The RSS Feed requires <em>either</em> a <strong>Title</strong> or a <strong>Description</strong>', E_USER_ERROR);
	} else {
		return true;
	}
}

}


?>