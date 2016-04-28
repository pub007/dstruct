<?php
/**
 * Prefs class
 */
/**
 * Application settings and Constants.
 * 
 * Singleton object. Set application constants at design and
 * at runtime.
 * @package dstruct_common
 */
class Prefs {


/**
 * @var array Properties held by the class
 */
private $props = array();


/**
 * @var object Instance of Prefs class
 */
private static $instance;

/**
 * @var integer Counts prepared statement hits
 * @see Prefs::countStatementHit()
 * @see DB
 */
private static $statementhits = 0;

/**
 * Application Name
 * The application name is used by caches to identify the application to which
 * the cache data belongs. Caches are often shared across applications (for instance
 * running on the same server) so use a
 * unique application name to prevent corruption etc. Alternatively, an application
 * running on multiple servers may want this string the same so that data is shared.
 * It is still important to set this constant if you do not use a cache within your script
 * as the framework caches information behind the scenes to improve speed.
 * @var string Application Name
 */
const APP_NAME = 'my_application';

/**
 * Development (DEV) or production mode
 * 
 * Effects things such as error reporting. Can be used in your scripts to control
 * behaviour - for example missing out some code for a package not available on
 * your local system, controlling logging etc.
 * @var boolean Whether the application is in 'development' or 'production'
 */
const DEV = TRUE;

/**
 * <var>DB_CONNECTIONS</var> defines connection strings to databases. These are fed to the system when inc_common.php is loaded
 * but they are LAZY connected. The framework uses PDO.
 * Options:-
 * connectionname,dbtype,host,username,password,schema,port,defaultcharset
 * @var string
 */
const DB_CONNECTIONS = 'defaultdb,mysql,localhost,root,password,dbname,3306,UTF-8';

/*
// production
DEV = FALSE;
const DB_CONNECTIONS = '';
*/

/**
 * DB Connection for {@link DatabaseCache}
 * If you are using a DatabaseCache then you will need to define the
 * name of the connection created in {@link Prefs::DB_CONNECTIONS} here.
 * @var string
 */
const DB_CACHE = 'appcache';

/**
 * Default Cache
 * Set the default cache system to be used by the framework. Can be:
 * <var>APC</var>,
 * <var>DStructMemCache</var>,
 * <var>Database</var>,
 * another cache object which impliments {@link DStructCacheInterface} or leave blank for none. Not using APC (or using none) will not turn off
 * the APC op-code cache.
 * @var string
 */
const CACHE_DEFAULT = 'APC';

/**
 * Enable script timing
 * If this is true, then microtime() is available from Prefs with key: dstruct_timer_start
 * @var bool
 */
const DSTRUCT_TIMER = false;

/**
 * Define available Memcache servers
 * Comma separated list of available servers to be used
 * by the framework cache.
 * @see DStructMemCache
 * @var string
 */
const MEMCACHE_SERVERS = '';

/**
 * Default email server
 * If a default email server is available, the DNS name or IP can be entered here.
 * All email settings can be overridden or set at runtime and therefore settings
 * here are all optional.
 * @see SMTPEmail
 * @var string
 */
const EMAIL_DEFAULT_HOST = 'mailserver.example.com';

/**
 * Default email server port
 * @see Prefs::EMAIL_DEFAULT_HOST
 * @var integer
 */
const EMAIL_DEFAULT_PORT = 587;

/**
 * Default username for email account
 * @see Prefs::EMAIL_DEFAULT_HOST
 * @var string
 */
const EMAIL_DEFAULT_USERNAME = 'address@example.com';

/**
 * Default email account password
 * @see Prefs::EMAIL_DEFAULT_USERNAME
 * @var string
 */
const EMAIL_DEFAULT_PASSWORD = 'password';

/**
 * Email address which receives default error messages
 * DStruct can send error messages using PHP's register_shutdown_function() to
 * notify you when an error occurs (not compile errors etc). Enter the address
 * you wish to receive these, or leave it blank to disable this feature.
 * @var mixed email address or blank
 */
const ERROR_DEFAULT_RECIPIENT = '';

/**
 * Subject line for default error messages
 * @see Prefs::ERROR_DEFAULT_RECIPIENT
 * @var string
 */
const ERROR_EMAIL_SUBJECT_LINE = '500 Server Error';

/**
 * Prepend the Application Name to the subject line of default error messages
 * @see Prefs::ERROR_DEFAULT_RECIPIENT
 * @var boolean
 */
const ERROR_PREPEND_APP_NAME = true; // add Prefs::APP_NAME to the start of the subject

/**
 * Set identifier used by {@link ClickController}.
 * 
 * Session identifier is preferred, but can use the user's IP if set to false. Unless there
 * is some very good reason not to use the session then leave as true
 * @see ClickController
 * @var boolean
 */
const CLICK_CONTROL_IDENTIFIER_IS_SESSION = true;

/**
 * Default currency symbol
 * @var string
 */
const CURRENCY_SYMBOL = '£';

/**
 * Default 'long' timestamp format
 * Typically used to display timestamps to users
 * As used by PHP's date() function
 * @var string
 */
const FORMAT_DISPLAY_TS = 'jS F Y H:i:s';

/**
 * Default 'short' timestamp format
 * Typicall used in form fields etc
 * As used by PHP's date() function
 * @var string
 */
const FORMAT_FORM_TS = 'd/m/Y H:i:s';

/**
 * Default 'long' date format
 * Typically used to display dates to users
 * As used by PHP's date() function
 * @var string
 */
const FORMAT_DISPLAY_DATE = 'jS F Y';

/**
 * Default 'short' date format
 * Typically used to display datess to users
 * As used by PHP's date() function
 * @var string
 */
const FORMAT_FORM_DATE = 'd/m/Y';

//=====================================================


/**
 * Class constructor
 * Only scalar values can be defined as class constants. Define
 * any global compile time values stored in arrays etc here.
 */
private function __construct() {
	// arrays not allowed as class constants, so...
	// put them as declared
	
	// Directories in 'lib' to be scanned by the autoloader should be put here
	$this->props['autoloader_directories'] = array(
		'DStruct/dstruct_common',
		'DStruct/presentation'
	);
	
	// 
	$this->props['ignored_errors'] = array(
		// ignore @session_start errors
		array('errno'  => 8,
			  'errstr' => 'A session had already been started - ignoring session_start()'
		),
		// ignore error from PEAR::SMTPEmail which will not be fixed due to PHP4 compatibility
		array('errno'  => 2048,
			  'errstr' => 'Non-static method PEAR::isError() should not be called statically, assuming $this from incompatible context'
		)
	);
	
	$this->props['upload_allowed_image_extensions'] = array('jpg', 'jpeg', 'gif', 'png');
	$this->props['upload_allowed_image_mimetypes'] = array('image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/pjpeg');
	$this->props['upload_allowed_extensions'] = array(
		'jpg', 'jpeg', 'gif', 'png', 'bmp', 'wbmp', 'mpg', 'wav', 'wma', 'txt', 'rtf', 'csv',
		'mpeg', 'mpg', 'mp3', 'mp4', 'wmv', 'avi', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
		'zip', 'odt', 'odp', 'ods', 'pdf');
	$this->props['upload_allowed_mimetypes'] = array(
		'image/jpg', 'image/jpeg', 'image/pjpeg', 'image/gif', 'image/png', 'image/bmp', 'image/x-windows-bmp',
	    'text/plain', 'text/richtext', 'text/comma-separated-values', 'text/csv', 'application/csv',
	    'audio/mpeg', 'audio/mp3', 'audio/x-wav', 'audio/x-ms-wma', 'video/x-ms-wmv', 'video/mpeg',
	    'video/mp4', 'video/x-ms-wmv', 'video/avi', 'video/msvideo', 'video/x-msvideo',
	    'application/msword', 'application/pdf', 'application/mspowerpoint', 'application/powerpoint',
	    'application/excel', 'application/vnd.ms-excel', 'application/zip', 'application/vnd.oasis.opendocument.text',
	    'application/vnd.oasis.opendocument.presentation', 'application/vnd.oasis.opendocument.spreadsheet'
	);
	
	// user-agents used to detect devices to be provided with mobile content when using the dstruct\mobile package.
	// No Palm support - too many possibilities
	$this->props['mobile_agents'] = array(
		'Android', 'iPhone', 'BlackBerry', 'Opera Mini', 'IEMobile', 
		'HTC', 'MOT-', 'Nokia', 'SymbianOS', 'SAMSUNG', 
		'SonyEricsson', 'LG-', 'LG/', 'SIE-'
	);
}

/**
 * Get instance of this Singleton class
 * @return object Prefs
 */
public static function getInstance() {
	if (empty(self::$instance)) {
		self::$instance = new Prefs;
	}
	return self::$instance;
}

/**
 * Set a key / value pair
 * @param string $key
 * @param mixed $val
 */
public function set($key, $val) {
	$this->props[$key] = $val;
}

/**
 * Retrieve a value by its key
 * @param string $key
 * @return mixed:
 */
public function get($key) {
	if (array_key_exists($key, $this->props)) {return $this->props[$key];}
	return false;
}

/**
 * Used to count hits on prepared statements in {@link Base}
 */
public static function countStatementHit() {self::$statementhits++;}

/**
 * Get hits on prepared statements.
 * @return integer
 */
public static function getStatementHitCount() {return self::$statementhits++;}

}
?>