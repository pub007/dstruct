<?php

class ListSubscriber {
	
	/**
	 * Is subscriber active?
	 *
	 * @var integer uts
	 */
	private $active = 0;
	private $emailaddress = '';
	private $id;
	private $emaillist;
	private $emaillistid;
	private $name = '';
	private $verifycode;
	private $verifypause = 0;
	
	/**
	 * Class constructor.
	 * @param array $row Information to populate object.
	 */
	public function __construct($row = false) {
		if ($row != false) {
			$this->id = $row['ListSubscriberID'];
			$this->emaillistid = $row['EmailListID'];
			$this->name = $row['Name'];
			$this->emailaddress = $row['EmailAddress'];
			$this->active = Convert::MySQLDateTimeToUTS($row['Active']);
			$this->verifycode = $row['VerifyCode'];
			$this->verifypause = Convert::MySQLDateTimeToUTS($row['VerifyPause']);
		} else {
			$this->verifycode = uniqid(); // set a default
		}
	}
	
	public function getActive() {
		return $this->active;
	}
	
	public function getEmailAddress($raw = FALSE) {
		return $this->emailaddress;
	}
	
	public function getEmailList() {
		$this->loadEmailList();
		return $this->emaillist;
	}
	
	public function getID() {
		return $this->id;
	}
	
	public function getName($raw = false) {
		return ($raw)? $this->name : html_specialchars($this->name);
	}
	
	public function getVerifyCode() {
		return $this->verifycode;
	}
	
	public function getVerifyPause() {
		return $this->verifypause;
	}
	
	private function insert() {
		if (!$this->emaillistid) {throw new DStructGeneralException('ListSubscriber::insert() - Missing an email list for subscriber');}
		$this->id = ListSubscriberDataManager::insert(array(
				$this->emaillistid,
				$this->name,
				$this->emailaddress,
				Convert::UTSToMySQLDateTime($this->active),
				$this->verifycode,
				Convert::UTSToMySQLDateTime($this->verifypause)
		));
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
			$this->emaillist = EmailList::loadByID($this->emaillistid);
		}
	}
	
	/**
	 * Save the object in the database.
	 */
	public function save() {
		($this->id)? $this->update() : $this->insert();
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
		$this->active = $active;
	}
	
	public function setEmailAddress($address) {
		$this->emailaddress = $address;
	}
	
	public function setEmailList($emaillist) {
		if (is_object($emaillist)) {
			$this->emaillistid = $emaillist->getID();
			$this->emaillist = $emaillist;
		} else {
			$this->emaillistid = $emaillist;
		}
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	/**
	 * Update in the database.
	 */
	private function update() {
		ListSubscriberDataManager::update(array(
		$this->name,
		$this->emailaddress,
		Convert::UTSToMySQLDateTime($this->active),
		$this->verifycode,
		Convert::UTSToMySQLDateTime($this->verifypause),
		$this->id
		));
	}
	
	public function verify() {
		$this->active = time();
		$this->verifycode = uniqid(); // generate a new code
		$this->save(); // prevent getting out of sync
	}
	
}