<?php
namespace pub007\dstruct;
/**
 * DB class
 */
/**
 *Extends PDO class
 *
 *Sets database connections to automatically use utf-8 and provides some
 *useful debugging behaviour, lazy shortcuts etc
 *@package dstruct_common
 */
class DB extends PDO {
	/**
	 * Count the queries through this db connection.
	 * @var integer
	 * @see getQueryCount()
	 */
	private static $querycount = 0;
	
	/**
	 * Number of new statements prepared.
	 * @var unknown
	 * @see getNewStatementCount()
	 */
	private static $newstatements = 0;
	
	/**
	 * Get the number of queries called.
	 * Mainly useful for optimisation.
	 * @return integer
	 */
	public function getQueryCount() {return self::$querycount;}
	
	/**
	 * Get the number of new statements prepared.
	 * Mainly useful for optimisation.
	 * @return integer
	 */
	public function getNewStatementCount() {return self::$newstatements;}
	
	/**
	 *Calls parent constructor and returns a PDO object set to UTF-8.
	 *
	 *Any errors setting up the connection etc are echoed to the browser!
	 *@param string $dbtype Database type to access e.g. MySQL, SQLITE etc
	 *@param string $host Hostname or IP address
	 *@param string $uname Database user name
	 *@param string $pwd Database password
	 *@param string $schema Database schema to connect to
	 *@param integer $port Port to attempt connection on
	 *@param string $charset Characterset to use
	 *@return object PDO database object
	 *@todo Check <var>$dbtype</var> has an available driver?
	 */
	public function __construct($dbtype, $host, $uname, $pwd, $schema, $port = '3306', $charset='UTF-8') {

		$dsn = $dbtype . ':host=' . $host . ';dbname=' . $schema . ';charset=' . $charset . ';port=' . $port;
		//echo $dsn;
		try {
			parent::__construct($dsn, $uname, $pwd);
		} catch (PDOException $e) {
			throw new DStructGeneralException('Failed to obtain database handle: ' . $e->getMessage());
			exit;
		}
		
		$this->queryNoOverload('SET NAMES utf8;');
		
		return $this;
	}
	
	/**
	 *Executes query and automatically echos any error to browser. Development tool.
	 *
	 *Should not be used in a production environment in general as using prepared statements is safer
	 *and potentially faster (if statement is re-used). Remember to always escape any data which can
	 *not be trusted with PDO::quote().<br />
	 *Increments db::$querycount
	 *@see DB::queryNoOverload
	 *@param string $query SQL string
	 *@return object
	 */
	public function query($query) {
		//echo $query. "<br />\n";
		$result = parent::query($query);
		self::$querycount++;
		if ($this->errorCode() != '0000') {
			$errors = $this->errorInfo();
			$errmsg = "PDO Error Code: " . $errors[0] . "\n" .
					  "SQL Error Code: " . $errors[1] . "\n" .
					  "SQL Error: " . $errors[2] . "\n" .
					  "SQL Query: " . html_specialchars($query);
			throw new DStructGeneralException($errmsg);
		}
		return $result;
	}
	
	/**
	 * Overloads PDO::Prepare to allow counting of statements.
	 * See PDO manual for usage.
	 * @param integer $statement SQL statement to prepare
	 * @param array $driver_options See PDO manual
	 */
	public function prepare($statement, $driver_options = array()) {
		self::$newstatements++;
		return parent::prepare($statement, $driver_options);
	}
	
	/**
	 *Bypasses overloading of PDO::query().
	 *
	 *PDO::query() is overloaded when using the {@link query()} function of
	 *this class, so this provides a way of
	 *accessing it without the overloaded code
	 *@param string $query SQL Statement
	 *@return object
	 */
	public function queryNoOverload($query) {parent::query($query);}
	
}
?>