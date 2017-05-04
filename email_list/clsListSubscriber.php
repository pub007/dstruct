<?php

class ListSubscriber {
	
	private $data = array(
	    'id'=>null,
	    'Active' => 0,
	    'EmailAddress' => '',
	    'EmailList' => null,
	    'EmailListID' => null,
	    'Name' => '',
	    'VerifyCode' => null,
	    'VerifyPause' => 0,
 	);
	
	/**
	 * Class constructor.
	 * @param array $row Information to populate object.
	 */
	public function __construct($row = false) {
		if ($row != false) {
		    $this->data = $row;
		    $this->data['Active'] = Convert::MySQLDateTimeToUTS($this->data['Active']);
		    $this->data['VerifyPause'] = Convert::MySQLDateTimeToUTS($this->data['VerifyPause']);
		} else {
			$this->data['VerifyCode'] = uniqid(); // set a default
		}
	}
	
	public function getActive() {
		return $this->data['Active'];
	}
	
	public function getEmailAddress($raw = FALSE) {
		return $this->data['EmailAddress'];
	}
	
	public function getEmailList() {
		$this->loadEmailList();
		return $this->emaillist;
	}
	
	public function getID() {
		return $this->data['id'];
	}
	
	public function getName($raw = false) {
		return ($raw)? $this->data['Name'] : html_specialchars($this->data['Name']);
	}
	
	public function getVerifyCode() {
		return $this->data['VerifyCode'];
	}
	
	public function getVerifyPause() {
		return $this->data['VerifyPause'];
	}
	
	private function insert() {
		$this->data['id'] = ListSubscriberDataManager::insert($this->data);
	}
	
	/**
	 * Is the subscriber active
	 * @return boolean
	 */
	public function isActive() {
		if ($this->active == 0) {return false;} // may have subbed, but not verified
		return ($this->active < time()); // could be paused until a certain time
	}
	
	/////////  WARNING - NEEDS CHANGING AS NEED TO PICK WITH LIST and ADDRESS AS MAY BE MEMBER OF MORE THAN ONE LIST!!!! ///////////////////////////////
	/**
	 * Load the object by its Email.
	 *
	 * @param string $emailaddress
	 * @return false|Tree False if can't find object with that code
	 */
	public static function loadByEmail($emailaddress, EmailList $emaillist) {
		$rs = ListSubscriberDataManager::loadByEmail($emailaddress, $emaillist->getID());
		if (!$rs->count()) {return false;}
		foreach ($rs as $record) {$row = $record;}
		if ($object = ObjWatcher::exists(__CLASS__, $row['ListSubscriberID'])) {return $object;}
		$object = new self($row);
		ObjWatcher::add($object);
		return $object;
	}
	
	/// ####################################################################################
	
	
	/**
	 * Load the object by its ID.
	 *
	 * @param integer $id
	 * @param array $row Data to populate object
	 * @return false|Tree False if can't find list with that ID
	 */
	public static function loadByID($id, $row = false) {
		// check numeric, as MySQL will cast the variable to a ?double? and so variables such as
		// '1stdsf' will cast to 1 and return a record rather than false!!
		if (!is_numeric($id)) {return false;}
		if ($class = ObjWatcher::exists(__CLASS__, $id)) {return $class;}
		if (!$row) {
			$rs = ListSubscriberDataManager::load($id);
			if ($rs->count() == 0) {return false;}
			foreach ($rs as $record) {$row = $record;}
		}
		$class = new self($row);
		ObjWatcher::add($class);
		return $class;
	}
	
	/**
	 * Load the object by its Verify Code.
	 *
	 * @param string $vc
	 * @return false|Tree False if can't find object with that code
	 */
	public static function loadByVerifyCode($vc) {
		$rs = ListSubscriberDataManager::loadByVerifyCode($vc);
		if (!$rs->count()) {return false;}
		foreach ($rs as $record) {$row = $record;}
		if ($object = ObjWatcher::exists(__CLASS__, $row['ListSubscriberID'])) {return $object;}
		$object = new self($row);
		ObjWatcher::add($object);
		return $object;
	}
	
	private function loadEmailList() {
		if (!$this->emaillist) {
			$this->emaillist = EmailList::loadByID($this->data['EmailListID']);
		}
	}
	
	/**
	 * Save the object in the database.
	 */
	public function save() {
		($this->data['id'])? $this->update() : $this->insert();
	}
	
	/**
	 * Set subscriber as active
	 * 
	 * A UTS which indicates whether the subscriber is active or suspended.
	 * UTS > now is a temporary suspension and isActive() should return false.
	 * NOTE: If the UTS is 0, the subscriber may have been manually de-activated
	 * but should normally be deleted if the user unsubscribes.
	 * @param integer $active UTS
	 */
	public function setActive($active) {
		$this->data['Active'] = $active;
	}
	
	public function setEmailAddress($address) {
		$this->data['EmailAddress'] = $address;
	}
	
	public function setEmailList($emaillist) {
		if (is_object($emaillist)) {
			$this->data['EmailListID'] = $emaillist->getID();
			$this->emaillist = $emaillist;
		} else {
			$this->data['EmailListID'] = $emaillist;
		}
	}
	
	public function setName($name) {
		$this->data['Name'] = $name;
	}
	
	/**
	 * Update in the database.
	 */
	private function update() {
		ListSubscriberDataManager::update($this->data, $this->data['id']);
	}
	
	public function verify() {
		$this->active = time();
		$this->verifycode = uniqid(); // generate a new code
		$this->save(); // prevent getting out of sync
	}
	
}