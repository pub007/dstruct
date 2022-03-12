<?php
/**
 * URLTranslatorPath class
 */
/**
 * Translates URLs which use different paths.
 * 
 * Will translate between urls such as:
 * http://www.example.com <----> http://www.example.com/mobile
 * @package dstruct_mobile
 */
class URLTranslatorPath implements URLTranslatorInterface {

/**
 * Alternative site URL.
 * @var string
 */
private $alt;

/**
 * Main site URL.
 * @var string
 */
private $main;

/**
 * URL of interest.
 * @var string
 */
private $url;

/**
 * URL split into its parts.
 * @var array
 */
private $urlparts;

/**
 * Class constructor.
 * @param string $main URL of main site
 * @param string $alt URL of alternative 'mobile' site
 */
public function __construct($main, $alt) {
	$this->main = $main;
	$this->alt = $alt;
}

/**
 * (non-PHPdoc)
 * @see URLTranslatorInterface::onAlternativePage()
 */
public function onAlternativePage() {
	$path = $this->urlparts['path'];
	if (stripos($path, '/') === 0) {$path = substr($path, 1);} // remove leading slash. Requiried??
	$dirs = explode('/', $path); // get an array of the directories
	
	if ($dirs[0] == $this->alt) {return true;} // if first part of path = alternative directory
	return false;
}

/**
 * (non-PHPdoc)
 * @see URLTranslatorInterface::rewrite()
 */
public function rewrite() {
	$parts = $this->urlparts;
	
	if ($this->onAlternativePage()) {
		$switchfrom = $this->alt;
		$switchto = $this->main;
	} else {
		$switchfrom = $this->main;
		$switchto = $this->alt;
	}
	//echo "<br />\nFROM:$switchfrom<br />\nTO:$switchto<br />\n";
	//var_dump($parts);
	if ($switchfrom) { // remove old part and replace with new
		//echo 'DOING REGEX' . $parts['path'];
		// /\/$switchfrom/i
		$parts['path'] = preg_replace("~/$switchfrom~i", "$switchto", $parts['path'], 1);
	} else { // just add new path part
		$parts['path'] = '/' . $switchto . $parts['path'];
	}
	
	// recompile the url
	if (isset($parts['port'])) {$parts['port'] = ':' . $parts['port'];}
	if (isset($parts['query'])) {$parts['query'] = '?' . $parts['query'];}
	$parts['scheme'] = $parts['scheme'] . '://';
	
	$newurl = implode('', $parts);
	return $newurl;
}

/**
 * (non-PHPdoc)
 * @param string $url URL of the page of interest.
 * @see URLTranslatorInterface::setURL()
 */
public function setURL($url) {
	$this->url = $url;
	$this->urlparts = parse_url($url);
}

}
?>