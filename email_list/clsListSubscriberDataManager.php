<?php

class ListSubscriberDataManager extends Base {
	
	private static $sf = 'SELECT ListSubscriberID,
			EmailListID,
			Name,
			EmailAddress,
			Active,
			VerifyCode,
			VerifyPause
			FROM listsubscriber
			';
	
	private static $insert = 'INSERT INTO listsubscriber (EmailListID, Name, EmailAddress, Active, VerifyCode, VerifyPause)
			VALUES (?, ?, ?, ?, ?, ?)';
	
	private static $load = 
		'WHERE ListSubscriberID = ?';
	
	private static $load_by_email =
		'WHERE EmailAddress = ?
		 AND EmailListID = ?';
	
	private static $load_by_list =
		'WHERE EmailListID = ?';
	
	private static $load_active_by_list =
		"WHERE EmailListID = ?
		 AND Active < NOW()
		 AND Active <> '1970-01-01 00:00:01'";
	
	private static $update = 'UPDATE listsubscriber
			SET Name = ?,
				EmailAddress = ?,
				Active = ?,
				VerifyCode = ?,
				VerifyPause = ?
			WHERE ListSubscriberID = ?';
	
	public static function insert($data) {
		self::doStatement(self::$insert, $data);
		$selector = DBSelector::getInstance();
		return $selector->getConnection()->lastInsertID();
	}
	
	public function load($id) {
		$rs = self::doStatement(self::$sf . self::$load, array($id));
		return new DBIterator($rs);
	}
	
	public function loadActiveByList($id) {
		$rs = self::doStatement(self::$sf . self::$load_active_by_list, array($id));
		return new DBIterator($rs);
	}
	
	public function loadByEmail($emailaddress, $listid) {
		$rs = self::doStatement(self::$sf . self::$load_by_email, array($emailaddress, $listid));
		return new DBIterator($rs);
	}
	
	public function loadByList($id) {
		$rs = self::doStatement(self::$sf . self::$load_by_list, array($id));
		return new DBIterator($rs);
	}
	
	public function update($data) {
		self::doStatement(self::$update, $data);
	}
	
}