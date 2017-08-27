<?php
/**
 * MobileController class
 */
/**
 * Controller class for mobile package.
 * 
 * NOTE: This package uses user-agent sniffing to detect mobile clients. This is far
 * from an exact science and new mobile / 'other' devices are growing at an increasing
 * rate. Please test thoroughly if you have specific devices you want to send content
 * to.  
 * The mobile package aids handling mobile versions of sites. It provides simple
 * mobile device detection based on user-agent as well as a set of translators
 * to map between the main and mobile sites. For example simple mappings such as
 *  http://www.example.com/mypage/ <---> http://www.example.com/mobile/mypage/ and
 *  more complex ones where pages are specific to the type of site such as:
 *  http://www.example.com/mypage/  <---> http://www.example.com/mobile/mypage/
 *  http://www.example.com/mypage/a  ---> http://www.example.com/mobile/mypage/
 *  http://www.example.com/mypage/  <---  http://www.example.com/mobile/mypage/b
 * @package dstruct_mobile
 */
class MobileController {


//private $pagetranslator;
/**
 * URL Translater being used
 * @var object
 */
private $urltranslator;

/**
 * URL to translate.
 * @var string
 */
private $url;

/**
 * Class constructor.
 * 
 * You must have a 'mobile_agent' setting in {@link Prefs} which contains the user-agents
 * to detect as 'mobile' browsers. 
 * @param object $urltranslator Translator class to use.
 * @param string $url would usually be passed if using as a link-clicked switcher
 * @throws DStructGeneralException
 */
public function __construct($urltranslator, $url = false) {
	$prefs = Prefs::getInstance();
	if (!$prefs->get('mobile_agents')) {throw new DStructGeneralException('MobileController::__construct() - Unable to find list of mobile agents');}
	if ($url) {
		$this->url = $url;
	} else {
		// grab the url
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
			$url = 'https://';
		} else {
			$url = 'http://';
		}
		$url .= $_SERVER['SERVER_NAME'];
		if ($_SERVER['SERVER_PORT'] != '80' && $_SERVER['SERVER_PORT'] != '443') {$url .= ':' . $_SERVER['SERVER_PORT'];}
		$url .= $_SERVER['SCRIPT_NAME']; // would rather use REQUEST_URI but not available on IIS
		$this->url = $url;
	}
	
	$this->urltranslator = $urltranslator;
	$this->urltranslator->setURL($this->url);
	
	@session_start();
}

/**
 * Check to see whether the user is in correct location.
 * 
 * If not then redirect user. Ignores any previous checks.
 */
public function checkForRedirect() {
	if (!isset($_SESSION['mobile_checked']) || $_SESSION['mobile_checked'] != 'true') {
		$_SESSION['mobile_checked'] = 'true';
		if ($this->isMobileAgent() != $this->urltranslator->onAlternativePage()) {$this->doSwitch();}
	}
}

/**
 * Switch the user between mobile or normal sites.
 * 
 * This must be called BEFORE there is any output to the browser as
 * otherwise you will get an error.
 */
public function doSwitch() {
	// translate the url location
	$loc = $this->urltranslator->rewrite($this->url);
	$loc = $this->getInversePage($loc);
	
	header("location: " . $loc);
	exit;
}

/**
 * Get the page from mobile if main, and main if on mobile site.
 * @param string $url Page we want to find the inverse mapping of
 * @return string
 */
public function getInversePage($url) {
	$prefs = Prefs::getInstance();
	$pagetranslations = ($this->onMobilePage())? $prefs->getProperty('mobile_page_translations_alt_to_main')
											   : $prefs->getProperty('mobile_page_translations_main_to_alt');
	
	// get the page and path
	$parts = parse_url($url);
	$path = $parts['path'];
	$page = strrchr($path, '/');
	$page = substr($page, 1);
		
	// get the new page if exists
	if ($page) {
		$newpage = (array_key_exists($page, $pagetranslations))? $pagetranslations[$page] : false;
	} else {
		$newpage = false;
	}
	
	if ($newpage) { // if found then rewrite
		$parts['path'] = substr($parts['path'], 0, strlen($parts['path']) - strlen($page)); // get path without the page
		$parts['path'] .= $newpage;
		
		// recompile the url
		if (isset($parts['port']))  {$parts['port']  = ':' . $parts['port'];}
		if (isset($parts['query'])) {$parts['query'] = '?' . $parts['query'];}
		$parts['scheme'] = $parts['scheme'] . '://';
		
		$url = implode('', $parts);
	}
	
	return $url;
}

/**
 * Is the user-agent a mobile one?
 * @return boolean
 */
public function isMobileAgent() {
	$ua = Validate::iss($_SERVER['HTTP_USER_AGENT']);
	if (!$ua) {return false;} // HTTP_USER_AGENT doesn't ALWAYS exist so we assume it is not a mobile phone we are dealing with
	$prefs = Prefs::getInstance();
	$mobile_agents = $prefs->get('mobile_agents');
	foreach($mobile_agents as $agent) {
		if (stripos($ua, $agent) !== false) {return true;}
	}
	return false;
}

/**
 * Is the user on the mobile version?
 */
public function onMobilePage() {
	return $this->urltranslator->onAlternativePage();
}

}
?>