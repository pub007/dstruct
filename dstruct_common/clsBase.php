<?php
/**
 * Abstract class Base
 */
/**
 *Prepares and caches database statements.
 *Usually extended by a DataManager class.
 *@package dstruct_common
 */
abstract class Base {

/**
 * Contains any prepared statements.
 * @var array
 */
static $statements = array();

/**
 *Prepare an SQL statement and return the handle.
 *
 *If previously prepared, returns the cached statement
 *handle.<br />
 *This method is usually called by {@link doStatement()}.
 *@param string $statement SQL statement to prepare
 *@return object PDO::Statement
 */
protected static function prepareStatement($statement) {
	$dbs = DBSelector::getInstance();
	$db = $dbs->getConnection(); // get the current connection. Lazy connects if necessary.
	// if we have already done this, return the statement handle
	if (array_key_exists($statement, self::$statements)) {
		Prefs::countStatementHit();
		return self::$statements[$statement];
	} else { // if not, then prepare, add to array and return the statement handle
		$statement_handle = $db->prepare($statement);
		self::$statements[$statement] = $statement_handle;
		return $statement_handle;
	}
}

/**
 *Prepares and executes an SQL statement.
 *
 *Caches prepared statements via {@link prepareStatement()}.
 *@param string $statement SQL statement
 *@param array $values Values for SQL statement
 *@return object Executed PDO::Statement
 */
protected static function doStatement($statement, $values = array()) {
	$statement_handle = self::prepareStatement($statement);
	
	$result = $statement_handle->execute($values);
	if ($statement_handle->errorCode() != '0000') {
		$errors = $statement_handle->errorInfo();
		$values = implode("\n", $values);
		$errmsg = "PDO Error Code: " . $errors[0] . "\n" .
				  "SQL Error Code: " . $errors[1] . "\n" .
				  "SQL Error: " . $errors[2] . "\n" .
				  "Statement: " . $statement_handle->queryString . "\n" .
				  "Values:\n" . $values;
		throw new DStructGeneralException($errmsg);
	}
	return $statement_handle;
}

protected static function generateInsert($data) {
    $qs = array_fill('?', count($data));
    return "INSERT INTO " . child::$tableName . " (" . implode(', ', array_keys($data)) . ") . VALUES (" . implode(', ', $qs) . ")";
}

protected static function generateUpdate($data, $idFields = false) {
    $sql = "UPDATE " . self::$tablename . " SET ";
    $sql .= implode(" = ?,\n", array_keys($data));
    if (!$idFields) {
        $sql .= " = ? WHERE " . self::$tableName . "ID = ?";
    } else {
        $sql .= " = ? WHERE ";
        foreach ($idFields as $field => $d) {
            $sql .= " AND $field = ?";
        }
        $sql = substr($sql, 5, strlen($sql)); // TODO: Check
    }
}

protected static function generateSelect($data = false) {
    if (!$data) {
        return "SELECT * FROM " . static::getTableName();
    }
    return "SELECT " . implode(", ", array_keys($data)) . " FROM " . static::getTableName();
}

protected abstract static function getTableName();
}
}
?>