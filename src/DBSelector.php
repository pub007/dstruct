<?php
namespace pub007\dstruct;
/**
 * DBSelector class
 */
/**
 * Container for multiple DB Connections and allows switching.
 * Singleton Class stores the database connection information and creates db objects
 * when they are first accessed via {@link DBSelector::useDB()} . This also
 * allows them to be swapped as the active connection by scripts.<br />
 * Also unsets connections if required. WARNING: see {@link unsetDB()}.
 * @package dstruct_common
 */
class DBSelector {

/**
 * Current connection
 * @var object
 */
private $current = null;

/**
 * Stores connections.
 * @var array
 */
private $props = array();

/**
 * Instance of this Singleton object/
 * @var object
 */
private static $instance;

/**
 * Class constructor.
 */
private function __construct() {}

/**
 * Get a DB Connection.
 * 
 * If the connection has not already been made, it connects to the DB. Also
 * sets the connection as the current connection.
 * @param string $key
 * @return boolean|multitype:
 */
public function getConnection($key = false) {
	if (!$key) {$key = $this->getCurrent();} // use default if no connection specified
	if (!array_key_exists($key, $this->props)) {return false;}
	if (!$this->isConnected($key)) {
		$reflect = new \ReflectionClass('DB');
		$db = $reflect->newInstanceArgs($this->props[$key]); // create a new db with the arguments originally passed
		$this->props[$key] = $db; // replace the array with the instantiated object
	}
	$this->current = $key;
	return $this->props[$key];
}

/**
 * The current database connection in use.
 *
 * This does NOT have to be an active connection... the default (1st listed)
 * connection is set as current when the variables are set.
 * @return string
 */
public function getCurrent() {return $this->current;}

/**
 * Get an instance of the singleton object.
 * @return object DBSelector
 */
public static function getInstance() {
	if (empty(self::$instance)) {
		self::$instance = new DBSelector;
	}
	return self::$instance;
}

/**
 * Is the DB Connection connected yet?
 * 
 * If no <var>$key</var> is not specified, the method will return the
 * state of the current connection.
 * @param string $key Key ref for the connection
 * @return boolean
 */
public function isConnected($key = false) {
	if (!$key) {$key = $this->getCurrent();} // use default if no connection specified
	if (array_key_exists($key, $this->props)) { // if key exists
		if (is_object($this->props[$key])) { // if it is object
			return true;
		}
	}
	return false;
}

/**
 * Store a DB Connection.
 * @param string $connstring
 * @see Prefs::DB_CONNECTIONS
 * @return boolean
 */
public function addConnectionString($connstring) {
	$conns = explode(':', $connstring);
	foreach ($conns as $conn) {
		$connfields = explode(',', $conn);
		$connfields[0] = trim($connfields[0]);
		$key = array_shift($connfields); // the first field should be the name of the connection to be used as the key
		$this->props[$key] = $connfields; // store the connection details
		if (!$this->current) {$this->current = $key;} // if there is no active connection setting, use this one
	}
	return true;
}


/**
 * Retrieve a DB Connection OR array (doesn't try to connect).
 * @param string $key Key ref for the connection
 * @return mixed Connection Object, array or False
 */
public function retrieveDB($key = false) {
	if (!$key) {$key = $this->getCurrent();} // use default if no connection specified
	if (array_key_exists($key, $this->props)) {
		return $this->props[$key];
	} else {
		return false;
	}
}

/**
 * Set the DB connection to use.
 * @param string $key Key ref for the connection
 * @return boolean Returns False if the key can not be found
 */
public function setCurrent($key) {
	if (array_key_exists($key, $this->props)) {
		$this->current = $key;
		return true;
	} else {
		return false;
	}
}

/**
 * Switch back to the previous DB used.
 * 
 * Will only work if used in conjunction with {@link DBSelector::switchToDB()}.
 * @see DBSelector::switchToDB()
 */
public function switchBackDB() {
	$prefs = Prefs::getInstance();
	if ($prefs->get('databasecacheolddb')) {
		$this->setCurrent($prefs->get('databasecacheolddb'));
	}
}

/**
 * Switch to a DB Connection.
 * 
 * This can be used to switch to a connection while remembering the current. You
 * can then call {@link DBSelector::switchBackDB()} to go back to the old
 * connection.
 * @see DBSelector::switchBackDB()
 * @param unknown $switchtokey
 */
public function switchToDB($switchtokey) {
	$prefs = Prefs::getInstance();
	$prefs->set('databasecacheolddb', $this->getCurrent());
	$this->setCurrent($switchtokey);
}

/**
 *Unsets a database connection - UNTESTED.
 *@param string $key Key ref for the connection
 *@return boolean True on success, False on failure (connection doesn't exist in object)
 */
public function unsetDB($key) {
	if (array_key_exists($key, $this->props)) {
		if (is_object($this->props[$key])) { // if it is a db object then
			$dbconn = $this->props[$key];
			//unset the object
			unset($dbconn);
		}
		// remove from array
		unset($this->props[$key]);
		// remove as current, if current
		if ($this->current == $key) {$this->current = null;}
		return true;
	} else {
		return false;
	}
}



}
?>