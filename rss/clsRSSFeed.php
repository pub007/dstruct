<?php
/**
 * RSSFeed class
 */
/**
 * Builds and outputs RSS feeds.
 * 
 * NOTE: Feed images are rarely dsplayed throughout most feed readers.
 * Date function requires RFC822
 * @package dstruct_rss
 * @author Shane
 * @todo This package could do with a re-write. Documentation isn't clear either.
 */
class RSSFeed {

/**
 * Feed title.
 * @see getTitle()
 * @var string
 */
private $title;

/**
 * Feed description.
 * @see getDescription()
 * @var string
 */
private $descrip;

/**
 * Feed URL
 * @see getURL()
 * @var string
 */
private $url;

/**
 * Feed image.
 * @see getImage()
 * @var string
 */
private $image;

/**
 * Feed image title.
 * @see getImageTitle()
 * @var string
 */
private $imagetitle;

/**
 * Feed image URL
 * @see getImageURL()
 * @var string
 */
private $imageurl;

/**
 * Feed image link.
 * @see getImageLink()
 * @var string
 */
private $imagelink;

/**
 * Feed image height
 * @see getImageHeight()
 * @var integer
 */
private $imageheight;

/**
 * Feed image width.
 * @see getImageWidth()
 * @var integer
 */
private $imagewidth;

/**
 * Feed image description.
 * @see getImageDescrip()
 * @var string
 */
private $imagedescrip;

/**
 * Feed language.
 * @see getLanguage()
 * @var string
 */
private $language;

/**
 * Feed copyright.
 * @see getCopyright()
 * @var string
 */
private $copyright;

/**
 * Managing editor of feed
 * @see getManagingEditor()
 * @var string
 */
private $managingeditor;

/**
 * Feed webmaster.
 * @see getWebMaster()
 * @var string
 */
private $webmaster;

/**
 * Feed publication date.
 * @see getPubDate()
 * @var integer
 */
private $pubdate;

/**
 * Date of last build.
 * @see getLastBuildDate()
 * @var string
 */
private $lastbuilddate;

/**
 * Category.
 *@see getCategory()
 *@var string
 */
private $category;

/**
 * Feed generator.
 *@see getGenerator()
 *@var string
 */
private $generator;

/**
 * Docs.
 *@see getDocs()
 *@var string
 */
private $docs;

/**
 * Time to cache the feed.
 *@see getTTL()
 *@var integer
 */
private $ttl;

/**
 * Feed rating.
 *@see getRating()
 *@var string
 */
private $rating;

/**
 * Text input.
 *@see getTextInput()
 *@var string
 */
private $textinput;

/**
 * Skip hours.
 *@see getSkiphours()
 *@var integer
 */
private $skiphours;

/**
 * Skip days.
 *@see getSkipdays
 *@var string
 */
private $skipdays;

/**
 * Items collection.
 * @see __construct()
 * @var RSSItems
 */
private $items = null;

/**
 * Set limit to number of items.
 * @see getLimit()
 * var integer.
 */
 private $itemamount;

/**
 *Gets the Title.
 *The title of the content of the channel
 *@return string
 */
public function getTitle() {return $this->title;}

/**
 *Gets the Description.
 *The description of the content of the channel
 *@return string
 */
public function getDescrip() {return $this->descrip;}

/**
 *Gets the URL.
 *Gets the url of the channel
 *@return string
 */
public function getURL() {return $this->url;}

/**
 *Gets the Image.
 *Gets an image for the content of the channel
 *@return string
 */
public function getImage() {return $this->image;}

/**
 *Gets the Title of the Image.
 *@return string
 */
public function getImageTitle() {return $this->imagetitle;}

/**
 *Gets the URL of the Image.
 *@return string
 */
public function getImageURL() {return $this->imageurl;}

/**
 *Gets the Link of the Image.
 *@return string
 */
public function getImageLink() {return $this->imagelink;}

/**
 *Gets the Height of the Image.
 *@return integer
 */
public function getImageHeight() {return $this->imageheight;}

/**
 *Gets the Width of the Image.
 *@return integer
 */
public function getImageWidth() {return $this->imagewidth;}

/**
 *Gets the Description of the Image.
 *@return string
 */
public function getImageDescrip() {return $this->imagedescrip;}

/**
 *Gets the language of the content in the channel.
 *@return string
 */
public function getLanguage() {return $this->language;}

/**
 *Gets the Copyright.
 *The copyright notice for the content of the channel
 *@return string
 */
public function getCopyright() {return $this->copyright;}

/**
 *Gets the ManagingEditor. 
 *An e-mail address for the editorial content producer
 *@return string
 */
public function getManagingEditor() {return $this->managingeditor;}

/**
 *Gets the WebMaster.
 *
 *An e-mail address for the web master
 *@return string
 */
public function getWebMaster() {return $this->webmaster;}

/**
 *Gets the pub date.
 *
 *A date that represents the publication date for the content in the channel
 *@return integer
 */
public function getPubDateUTS() {return $this->$pubdate;}

/**
 * Gets the last build date of this object.
 * 
 * Should be UTS date?
 * @return integer
 */
public function getLastBuildDate() {return $this->lastbuilddate;}

/**
 * Gets the Category.
 * 
 * Allows for the ability to add one or multiple categories that a channel belongs to
 * @return string
 */
public function getCategory() {return $this->category;}

/**
 *Gets the Generator.
 *
 *The program that creates the channel
 *@return string
 */
public function getGenerator() {return $this->generator;}

/**
 *Gets the docs.
 *
 *URL for the documentation for the format of the RSS feed
 *@return string
 */
public function getDocs() {return $this->docs;}

/**
 * Gets the ttl.
 * 
 * (Time to Live) which tells the length of time the channel can be cached.
 * In seconds?
 *@return integer
 */
public function getTTL() {return $this->ttl;}


/**
 * Gets the rating.
 * 
 * PICS rating for the channel
 * @return string
 */
public function getRating() {return $this->rating;}

/**
 * Gets the text input.
 *  
 * A text input field that can be displayed with the channel
 * @return string
 */
public function getTextInput() {return $this->textinput;}

/**
 * Gets the Skip Hours.
 *  
 * Tells aggregators to skip for specified hours
 * @return integer
 */
public function getSkipHours() {return $this->skiphours;}

/**
 * Gets the Skip Days.
 * 
 * Tells aggregators to skip for specified days.
 * @return integer
 */
public function getSkipDays() {return $this->skipdays;}

/**
 * Get number of items in feed?
 *@see setItemAmount()
 *@return string
 */
public function getItemAmount() {return $this->itemamount;}

/**
 * Set the Title.
 * 
 * Sets a title of the channel
 * @param string $title
 */
public function setTitle($title) {$this->title = $title;}

/**
 *Set the Description.
 *
 *Sets a description for the channel
 *@param string $descrip
 */
public function setDescrip($descrip) {$this->descrip = $descrip;}

/**
 *Set the URL.
 *
 *Sets the url for the channel
 *@param string $url
 */
public function setURL($url) {$this->url = $url;}
	
/**
 *Set the Title of the Image.
 *
 *Sets an image in the channel
 *@param string $imagetitle
 */
public function setImageTitle($imagetitle) {$this->imagetitle = $imagetitle;}

/**
 *Set the URL of the Image.
 *
 *Sets an image in the channel
 *@param string $imageurl
 */
public function setImageURL($imageurl) {$this->imageurl = $imageurl;}

/**
 *Set the Link of the Image.
 *
 *Sets an image in the channel
 *@param string $imagelink
 */
public function setImageLink($imagelink) {$this->imagelink = $imagelink;}
	
/**
 *Set the Height of the Image.
 *
 *Sets an image in the channel
 *@param integer $imageheight
 */
public function setImageHeight($imageheight) {$this->imageheight = $imageheight;}
	
/**
 *Set the Width of the Image.
 *
 *Sets an image in the channel
 *@param integer $imagewidth
 */
public function setImageWidth($imagewidth) {$this->imagewidth = $imagewidth;}
	
/**
 *Set the Description of the Image.
 *
 *Sets an image in the channel
 *@param string $imagedescrip
 */
public function setImageDescrip($imagedescrip) {$this->imagedescrip = $imagedescrip;}	
	
/**
 *Set the language.
 *
 *Sets the language of the content in the channel.
 *@param string $language
 *@todo What format??
 */
public function setLanguage($language) {$this->language = $language;}

/**
 *Set the copyright.
 *
 *The copyright notice for the content of the channel
 *@param string $copyright
 */
public function setCopyright($copyright) {$this->copyright = $copyright;}

/**
 *Set the managing editor.
 *
 *An e-mail address for the editorial content producer
 *@param string $managingeditor
 */
public function setManagingEditor($managingeditor) {$this->managingeditor = $managingeditor;}

/**
 *Set the web master.
 *
 *An e-mail address for the web master
 *@param string $webmaster
 */
public function setWebMaster($webmaster) {$this->webmaster = $webmaster;}

/**
 *Set the publication date of this item as UTS.
 *
 *The pubDate is the date that the item was published.
 *Entered in UTS and formated to RFC822
 *@param integer $pubdate
 */
public function setPubDateUTS($pubdate) {
	$this->pubdate = date('D, j F Y  H:i:s T', $pubdate); 	
} 

/**
 *Set the publication date of this item as string.
 *
 *The pubDate is the date that the item was published
 *Can be entered in any format
 *getPubDateString method changes to UTS and calls 
 *getPubDateUTS method to format
 *@param string $pubdate
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
 *Set the last build date.
 *
 *The last date and time that the content was changed
 *@param integer $lastbuilddate UTS timestamp
 */
public function setLastBuildDate($lastbuilddate) {
	$this->lastbuilddate = date('D, j F Y  H:i:s T', $lastbuilddate); 	
}

/**
 *Set the category.
 *
 *Allows for the ability to add one or multiple categories that a channel belongs to
 *@param string $category
 */
public function setCategory($category) {$this->category = $category;}

/**
 *Set the generator.
 *
 *The program that creates the channel
 *@param string $generator
 */
public function setGenerator($generator) {$this->generator = $generator;}

/**
 *Set the Docs.
 *
 *URL for the documentation for the format of the RSS feed
 *@param string $docs
 */
public function setDocs($docs) {$this->docs = $docs;}

/**
 *Set the ttl.
 *
 *(Time To Live) which tells the length of time the channel can be cached
 *@param integer $ttl In seconds?
 */
public function setTTL($ttl) {$this->ttl = $ttl;}

/**
 *Set the rating.
 *
 *PICs rating for the channel
 *@param string $rating
 */
public function setRating($rating) {$this->rating = $rating;}

/**
 *Set the text input.
 *
 *A text nput field that can be displayed with the channel
 *@param string $textinput
 */
public function setTextInput($textinput) {$this->textinput = $textinput;}

/**
 *Set the skip hours.
 *
 *Tells aggregators to skip for specified hour.
 *@param integer $skiphours
 *@todo What does this mean?
 */
public function setSkipHours($skiphours) {$this->skiphours = $skiphours;}

/**
 *Set the skip days.
 *
 *Tells aggregators to skip for specified day
 *@param integer $skipdays
 *@todo What does this mean?
 */
public function setSkipDays($skipdays) {$this->skipdays = $skipdays;}

/**
 *Set the Amount of items.
 * 
 *The number of Items to be displayed in the Feed,
 *Default is 200. 
 *@param integer $itemamount
 */
public function setItemAmount($itemamount = 6) {$this->itemamount = $itemamount;}

/**
 *Class constructor.
 */
public function __construct() {
	$this->items = new RSSItems;
}

/**
 *Adding an {@link RSSItem}.
 *
 *@param RSSItem $item requires a valid RSSItem 
 */
public function addItem(RSSItem $item) {
	if ($item->validate($item)) {
		return $this->items->addItem($item);
	} else {
		return false;
	}
}

/**
 * RSS Feed Validator.
 * 
 * Returns false if an RSS Feed title, description, URL or pubdate have not been entered.
 * @todo Should be more specific about what is missing!
 * @todo Should throw an error, rather than use trigger_error()
 * @return boolean Actually, only returns true if successful, otherwise, triggers and error.
 */
public function validate() {
	if (!$this->title) {trigger_error('clsRSSFeed - Expecting Title', E_USER_ERROR);}
	if (!$this->descrip) {trigger_error('clsRSSFeed - Expecting Description', E_USER_ERROR);}
	if (!$this->url) {trigger_error('clsRSSFeed - Expecting URL', E_USER_ERROR);}
	if (!$this->pubdate) {trigger_error('clsRSSFeed - Expecting Publish Date', E_USER_ERROR);}
	if ($this->imagetitle ||
		$this->imageurl ||
		$this->imagelink ||
		$this->imageheight ||
		$this->imagewidth ||
		$this->imagedescrip) {
		if (!$this->imagetitle || !$this->imageurl || !$this->imagelink) {
			trigger_error('RSSFeed - An Image requires a Title, URL and a Link', E_USER_ERROR);
		}
	}
	return true;
}

/**
 * Outputs feed.
 * 
 * Creates the rss feed xml.
 */
public function  output() {
	if ($this->validate()) 
	$output = '<?xml version="1.0" encoding="utf-8" ?>
		<rss version="2.0">
		<channel>';
	if ($this->title) {$output .= "<title>$this->title</title>";}
	if ($this->descrip) {$output .= "<description>$this->descrip</description>";}
	if ($this->url) {$output .= "<url>$this->url</url>";}
	if ($this->imagetitle) {
		$output .= "<image><title>$this->imagetitle</title>
					<url>$this->imageurl</url>
					<link>$this->imagelink</link>";
		if ($this->imageheight) {str_replace('&', '&amp;', $output .="<height>$this->imageheight</height>");}
		if ($this->imagewidth) {str_replace('&', '&amp;', $output .="<width>$this->imagewidth</width>");}
		if ($this->imagedescrip) {str_replace('&', '&amp;', $output .="<description>$this->imagedescrip</description>");}
		$output .="</image>";
	}									
	if ($this->language) {str_replace('&', '&amp;', $output .= "<language>$this->language</language>");}
	if ($this->copyright) {str_replace('&', '&amp;', $output .= "<copyright>$this->copyright</copyright>");}
	if ($this->managingeditor) {str_replace('&', '&amp;', $output .= "<managingeditor>$this->managingeditor</managingeditor>");}
	if ($this->webmaster) {str_replace('&','&amp;', $output .= "<webmaster>$this->webmaster</webmaster>");}
	if ($this->pubdate) {str_replace('&', '&amp;', $output .= "<pubDate>$this->pubdate</pubDate>");}
	if ($this->lastbuilddate) {str_replace('&', '&amp;', $output .= "<lastbuilddate>$this->lastbuilddate</lastbuilddate>");}
	if ($this->category) {str_replace('&', '&amp;', $output .= "<category>$this->category</category>");}
	if ($this->generator) {str_replace('&', '&amp;', $output .= "<generator>$this->generator</generator>");}
	if ($this->docs) {str_replace('&', '&amp;', $output .= "<docs>$this->docs</docs>");}
	if ($this->ttl) {str_replace('&', '&amp;', $output .= "<ttl>$this->ttl</ttl>");}
	if ($this->rating) {str_replace('&', '&amp;', $output .= "<rating>$this->rating</rating>");}
	if ($this->textinput) {str_replace('&', '&amp;', $output .= "<textinput>$this->textinput</textinput>");}
	if ($this->skiphours) {str_replace('&', '&amp;', $output .= "<skiphours>$this->skiphours</skiphours>");}
	if ($this->skipdays) {str_replace('&', '&amp;', $output .= "<skipdays>$this->skipdays</skipdays>");}
	
	$count = 0;
	
	foreach ($this->items as $item) {
		
		$output .= '<item>';
		if ($item->getId()) {$output .= '<id>' . str_replace('&','&amp;', $item->getId()) . '</id>';}
		if ($item->getGuid()) {$output .= '<guid>' . str_replace('&','&amp;', $item->getGuid()) . '</guid>';}
		if ($item->getTitle()) {$output .= '<title>' . str_replace('&', '&amp;', $item->getTitle()) . '</title>';}
		if ($item->getDescrip()) {$output .= '<description>' . str_replace('&', '&amp;', $item->getDescrip()) . '</description>';}
		if ($item->getLink()) {$output .= '<link>' . str_replace('&', '&amp;', $item->getLink()) . '</link>';}
		if ($item->getPubDate()) {$output .= '<pubDate>' . str_replace('&', '&amp;', $item->getPubDate()) . '</pubDate>';}
		if ($item->getAuthor()) {$output .= '<author>' . str_replace('&', '&amp;', $item->getAuthor()) . '</author>';}
		if ($item->getCategory()) {$output .= '<category>' . str_replace('&', '&amp;', $item->getCategory()) . '</category>';}
		if ($item->getComments()) {$output .= '<comments>' . str_replace('&', '&amp;', $item->getComments()) . '</comments>';}
		if ($item->getSource()) {$output .= '<source>' . str_replace('&', '&amp;', $item->getSource()) . '</source>';}
		$output .= '</item>';
		if ($count == $this->itemamount) {
			break;
		}else{
			$count++;
		}
		
	}
	$output .= '</channel>
			</rss>';
	return $output;
}			

/**
 * Save As File.
 * 
 * Saves the file to the specified filepath
 * Function checks for valid path
 * If the file already exists, then it is overwritten, else a new file is created
 * Checks for backslash at the end of the filepath, if returned false, then added
 * @param string $filepath
 * @param string $filename
 */
public function saveAsFile($filepath, $filename) {
	if (!is_dir($filepath)) {
		trigger_error('clsRSSFeed - Filepath NOT Valid',E_USER_WARNING);
	} else {
		if (substr($filepath,strlen($filepath)-1,1) != '/') {
			$filepath .= '/';
		}
		if (!is_dir($filepath)) {
			trigger_error('clsRSSFeed - Filepath NOT valid', E_USER_ERROR);
		} else {		
			$fh = fopen($filepath . $filename, 'w'); //if the file already exists then it is overwritten, else a new file is created
			fputs($fh, $this->output());
			fclose($fh); 
		}
	}	
}

/**
 * Sort the feed items.
 * 
 * @param string $element Element to sort by
 * @param string $sort_direction
 * @param string $caseinsensitive
 * @param string $params
 * @param string $astime
 * @see ObjCollection::sortObjects()
 */
public function sortItems($element, $sort_direction=ObjCollection::SORT_OBJECTS_ASC, $caseinsensitive = true, $params = '', $astime = false) {
	$this->items->sortObjects($element, $sort_direction, $caseinsensitive, $params, $astime);
}

}	
?>