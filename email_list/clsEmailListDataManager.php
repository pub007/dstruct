<?php

class EmailListDataManager extends Base {
    
	private static $load_by_id = ' WHERE EmailListID = ?';
	
	protected static function getTableName() {
	    return "emaillist";
	}
	
	public static function insert($data) {
		self::doStatement(self::generateInsert($data));
		$selector = DBSelector::getInstance();
		return $selector->getConnection()->lastInsertID();
	}
	
	public static function loadAll() {
		$rs = self::doStatement(self::generateSelect(), array());
		return new DBIterator($rs);
	}
	
	public static function load($id) {
		$rs = self::doStatement(self::generateSelect() . self::$load_by_id, array($id));
		return new DBIterator($rs);
	}
	
	public static function update($data, $idFields = false) {
		self::doStatement(self::generateUpdate($data, $idFields), $data);
	}
}