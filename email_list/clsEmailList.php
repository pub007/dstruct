<?php
/**
 * EmailList class
 */
/**
 * Email Lists.
 *
 * Email Lists to which people subscribe.
 * @package email_list
 * @author David Lidstone
 */
class EmailList {
	
    private $data = array(
        'id'=>null,
        'Name'=>null,
        'EmailAddress'=>null,
        'IMAPHost'=>null,
        'Username'=>null,
        'Password'=>null,
        'Host'=>null,
        'Port'=>null,
        'AdministratorEmail'=>null,
        'ProcessedDir'=>null,
    );
    private $cs = null; // list subscribers object
    
	/**
	 * Class constructor.
	 * @param array $row Information to populate object.
	 */
	public function __construct($row = false) {
		if ($row != false) {
			$this->data = $row;
		}
	}
	
    public function __toString() {
	    return print_r($this->data, true);
	}
	
	public function deleteSubscriber(ListSubscriber &$subscriber) {
		$watcher = ObjWatcher::instance();
		$watcher->remove($subscriber);
		ListSubscriberDataManager::delete($subscriber->getID());
		$this->removeActiveSubscriber($subscriber);
		unset($subscriber);
	}
	
	// TODO: Test this function
	// Why is this in own method?
	protected function removeActiveSubscriber(ListSubscriber $subscriber) {
	    $this->cs['activesubscribers']->remove($subscriber);
	}
	
	public function getAdministratorEmail() {
		return $this->data['AdministratorEmail'];
	}
	
	public function getActiveSubscribers() {
		$this->loadActiveSubscribers();
		return $this->cs['ActiveSubscribers'];
	}
	
	public function  getEmailAddress() {
		return $this->data['EmailAddress'];
	}
	
	public function getHost() {
		return $this->data['Host'];
	}
	
	public function getID() {
		return (isset($this->data['id']))? $this->data['id'] : false;
	}
	
	public function getIMAPHost($raw = false) {
		return $raw? $this->data['IMAPHost'] : html_specialchars($this->data['IMAPHost']);
	}
	
	public function getName($raw = false) {
		return $raw? $this->data['Name'] : html_specialchars($this->data['Name']);
	}
	
	public function getPassword($raw = false) {
		$password = Generate::decrypt($this->data['Password']);
		return $raw? $password : html_specialchars($password);
	}
	
	public function getPort() {
		return $this->data['Port'];
	}
	
	public function getProcessedDir($raw = false) {
		return $raw? $this->data['ProcessedDir'] : html_specialchars($this->data['ProcessedDir']);
	}
	
	public function getSubscribers() {
		$this->loadSubscribers();
		return $this->cs['subscribers'];
	}
	
	public function getUsername ($raw = false) {
		return $raw? $this->data['Username'] : html_specialchars($this->data['Username']);
	}
	
	private function insert() {
		$this->data['id'] = EmailListDataManager::insert($this->data);
	}
	
	private function loadActiveSubscribers() {
		if (!$this->cs['activesubscribers']) {
			$this->cs['activesubscribers'] = new ListSubscribers();
			$this->cs['activesubscribers']->loadActiveByList($this);
		}
	}
	
	/**
	 * Load the list by its ID.
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
			$rs = EmailListDataManager::load($id);
			if ($rs->count() == 0) {return false;}
			foreach ($rs as $record) {$row = $record;}
		}
		$class = new self($row);
		ObjWatcher::add($class);
		return $class;
	}
	
	private function loadSubscribers() {
		if (!$this->cs['subscribers']) {
			$this->cs['subscribers'] = new ListSubscribers();
			$this->cs['subscribers']->loadByList($this);
		}
	}
	
	/**
	 * Save the object in the database.
	 */
	public function save() {
		($this->data['id'])? $this->update() : $this->insert();
	}
	
	public function setAdministratorEmail($administratoremail) {
		$this->data['AdministratorEmail'] = $administratoremail;
	}
	
	public function setEmailAddress($address) {
		$this->data['EmailAddress'] = $address;
	}
	
	public function setHost($host) {
		$this->data['Host'] = $host;
	}
	
	/**
	 * Set string for connecting to IMAP
	 * e.g. {imap.zoho.com:993/imap/ssl}INBOX
	 * @param string $imaphost
	 */
	public function setIMAPHost($imaphost) {
		$this->data['IMAPHost'] = $imaphost;
	}
	
	/**
	 * Set the name of the list
	 * @param string $name
	 */
	public function setName($name) {
		$this->data['Name'] = $name;
	}
	
	public function setUsername($username) {
		$this->data['Username'] = $username;
	}
	
	public function setPassword($password) {
		$this->data['Password'] = Generate::encypt($password);
	}
	
	public function setPort($port) {
		$this->data['Port'] = $port;
	}
	
	/**
	 * Set the directory for the email to be moved to once processed
	 * 
	 * e.g. [Gmail]/Trash
	 * 
	 * @param string $processedDir
	 */
	public function setProcessedDir($processedDir) {
		$this->data['ProcessedDir'] = $processedDir;
	}
	
	/**
	 * Update in the database.
	 */
	private function update() {
	    EmailListDataManager::update($this->data, $this->data['id']);
	}
	
	
}