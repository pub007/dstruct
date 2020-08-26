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
protected static function doStatement($statement, $values = []) {
	$statement_handle = self::prepareStatement($statement);
	// we need to use the order rather than bindings. Not sure why bindings would not work, even when keys
	// appeared to match exactly. 
	$v = array_values($values);
	$result = $statement_handle->execute($v);
	if ($statement_handle->errorCode() != '0000') {
		$errors = $statement_handle->errorInfo();
		$v = implode("\n", $v);
		$errmsg = "PDO Error Code: " . $errors[0] . "\n" .
				  "SQL Error Code: " . $errors[1] . "\n" .
				  "SQL Error: " . $errors[2] . "\n" .
				  "Statement: " . $statement_handle->queryString . "\n" .
				  "Values:\n" . $v;
		throw new DStructGeneralException($errmsg);
	}
	return $statement_handle;
}

protected static function generateInsert($data) {
    $qs = array_fill('?', count($data));
    return "INSERT INTO " . static::getTableName() . " (" . implode(', ', array_keys($data)) . ") . VALUES (" . implode(', ', $qs) . ")";
}

/**
 * 
 * @param array $data
 * @param boolean $idFields If selecting rows by AND
 * @param boolean|array $idsIn If updating multiple rows by ID
 */
protected static function generateUpdate($data, $boundFields = false, $unBoundFields = false) {
	$sql = "UPDATE " . static::getTableName() . " SET ";
    $sql .= implode(" = ?,\n", array_keys($data));
    $sql .= " = ? WHERE ";
    $where = '';
    if ($boundFields) {
    	foreach ($boundFields as $col => $data) {
    		$where .= " AND $col = ?";
    	}
    }
    if ($unBoundFields) {
    	foreach ($unBoundFields as $field) {
    		$where .= " AND $field";
    	}
    }
    $where = substr($where, 5, strlen($where));
    return $sql .= $where;
}

protected static function generateSelect($data = false) {
    if (!$data) {
        return "SELECT * FROM " . static::getTableName();
    }
    return "SELECT " . implode(", ", array_keys($data)) . " FROM " . static::getTableName();
}

protected abstract static function getTableName();

/**
 * Delete from database
 * 
 * @param mixed $data id or array of IDs
 */
public static function delete($data) {
	static::doStatement(static::generateDelete($data));
}

public static function insert($data) {
	static::doStatement(static::generateInsert($data));
	$selector = DBSelector::getInstance();
	return $selector->getConnection()->lastInsertID();
}

public static function load($id) {
	$rs = static::doStatement(static::generateSelect() . " WHERE " . static::getTableName() . "id = ?", [$id]);
	return new DBIterator($rs);
}

public static function loadAll() {
	$rs = static::doStatement(static::generateSelect(), []);
	return new DBIterator($rs);
}

public static function update($data, $id) {
	$sql = static::generateUpdate($data, array(static::getTableName()."id"=>$id));
	$data[static::getTableName()."id"] = $id;
	return static::doStatement($sql, $data);
}

}
?>