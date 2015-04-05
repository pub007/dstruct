<?php

class EmailListDataManager extends Base {
	
	private static $sf =
		'SELECT EmailListID,
			Name,
			EmailAddress,
			IMAPHost,
			Username,
			Password,
			Host,
			Port,
			AdministratorEmail,
			ProcessedDir
		 FROM emaillist
		 ';
	
	private static $insert = 'INSERT INTO emaillist (Name, EmailAddress, IMAPHost, Username, Password, Host, Port, AdministratorEmail, ProcessedDir) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
	
	private static $load_by_id = 'WHERE EmailListID = ?';
	
	private static $update = 'UPDATE emaillist SET
			Name = ?,
			EmailAddress = ?,
			IMAPHost = ?,
			Username = ?,
			Password = ?,
			Host = ?,
			Port = ?,
			AdministratorEmail = ?,
			ProcessedDir = ?
			WHERE EmailListID = ?';
	
	public static function insert($data) {
		self::doStatement(self::$insert, $data);
		$selector = DBSelector::getInstance();
		return $selector->getConnection()->lastInsertID();
	}
	
	public static function loadAll() {
		$rs = self::doStatement(self::$sf, array());
		return new DBIterator($rs);
	}
	
	public static function load($id) {
		$rs = self::doStatement(self::$sf . self::$load_by_id, array($id));
		return new DBIterator($rs);
	}
	
	public static function update($data) {
		self::doStatement(self::$update, $data);
	}
}