<?php
/**
 * Bootstrap file for DStruct
 */
/**
 *
 */
// set an app root - equivalent to Server.Mappath in ASP
define('APP_ROOT', realpath(dirname(__FILE__) . '/../..//') . '/');

require_once APP_ROOT.'lib/DStruct/prefs/clsPrefs.php';
$dstruct_prefs = Prefs::getInstance();

// if doing page timing, otherwise comment out. Misses the initial setup so will not be completely accurate
$dstruct_prefs->set('dstruct_timer_start', microtime(true));

if (defined('Prefs::ERROR_DEFAULT_RECIPIENT' && Prefs::ERROR_DEFAULT_RECIPIENT)) {
	register_shutdown_function('dstruct_shutdown_fn'); // do this as early as possible to help find errors
}

// setup any db connections defined (Lazy loaded).
// required before using any caching as using the DatabaseCache as the default cache will require connections
if (defined('Prefs::DB_CONNECTIONS') && Prefs::DB_CONNECTIONS) {
	require_once APP_ROOT.'lib/DStruct/dstruct_common/clsDBSelector.php';
	$dbselector = DBSelector::getInstance();
	$dbselector->addConnectionString(Prefs::DB_CONNECTIONS);
	unset($dbselector);
}

// Load a cache class or a 'null' class if no cache is defined in Prefs
// Caches (even multiple types) can still be created if desired - this is just the default.
// We need to do this before the autoloader as it uses a cache for speed if possible
require_once APP_ROOT.'lib/DStruct/dstruct_common/clsDStructCacheInterface.php';

if (defined('Prefs::CACHE_DEFAULT') && Prefs::CACHE_DEFAULT) {
	$cachename = Prefs::CACHE_DEFAULT.'Cache';
	require_once APP_ROOT."lib/DStruct/dstruct_common/cls$cachename.php";
	$dstruct_prefs->set('cache',$cachename::getInstance());
	unset($cachename);
} else {
	require_once APP_ROOT.'lib/DStruct/dstruct_common/clsNullCache.php';
	$dstruct_prefs->set('cache',NullCache::getInstance());
}

// register our autoloader
spl_autoload_register('dstruct_autoloader');

unset($dstruct_prefs);






         // ====== FUNCTIONS ======= //

/**
 * Shutdown function set by DStruct.
 * 
 * PHP calls the shutdown function at the end of processing the script. Any un-caught
 * errors will be seen in this function and are emailed if required.
 * @see Prefs::DEV
 */
function dstruct_shutdown_fn() {
	if(is_null($e = error_get_last()) === false)
	{
		require_once APP_ROOT.'lib/DStruct/prefs/clsPrefs.php';
		
		$prefs = Prefs::getInstance();
		$ignorederrors = $prefs->get('ignored_errors');
		
		// if any errors are set to be ignored in prefs, just exit;
		foreach ($ignorederrors as $errno => $errstr) {
			if ($errno == $e['type'] && $errstr == $e['message']) {exit;}
		}
		
		require_once APP_ROOT.'lib/DStruct/dstruct_common/clsSMTPEmail.php';
		$smtp = new SMTPEmail;
		
		if (Prefs::ERROR_PREPEND_APP_NAME) {
			$emailsubject =  Prefs::APP_NAME . ' ' .Prefs::ERROR_EMAIL_SUBJECT_LINE;
		} else {
			$emailsubject = Prefs::ERROR_EMAIL_SUBJECT_LINE;
		}
		
		$smtp->setTo(Prefs::ERROR_DEFAULT_RECIPIENT);
		$smtp->setSubject($emailsubject);
		$body = 'APP_NAME: ' . Prefs::APP_NAME . "\r\nError occured:\r\n\r\n" . print_r($e, true);
		if (isset($_SERVER)) {
			$body .= "\r\n\r\n\r\n" . print_r($_SERVER, true);
		}
		if (isset($_SESSION)) {
			$body .= "\r\n\r\n\r\n" . print_r($_SESSION, true);
		}
		
		$smtp->setBody($body);
		$smtp->send();
	}
}

/**
 * DStruct Autoloader.
 * 
 * Attempts to autoload classes and then caches the results. See the
 * 'autoloader_directories' property defined in the class constructor
 * for Prefs for more information.
 * @param string $class_name
 */
function dstruct_autoloader($class_name) {
	$prefs = Prefs::getInstance();
	$cache = $prefs->get('cache');
	
	$key = Prefs::APP_NAME . '_autoldr_' . $class_name;
	
	if ($cache->hasServer()) {
		if ($path = $cache->get($key)) {
			require_once($path);
			return;
		}
	}
	
	$directories = $prefs->get('autoloader_directories');
   
	//for each directory
	foreach($directories as $directory) {
		$path = APP_ROOT.'lib/'.$directory.'/cls'.$class_name . '.php';
		if(file_exists($path)) {
			require_once($path);
			if ($cache->hasServer()) {$cache->set($key, $path);}
			return;
		}
	}
}

?>