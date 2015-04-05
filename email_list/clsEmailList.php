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
	
	/**
	 * Active subscribers to the list
	 * @var mixed
	 */
	private $activesubscribers;
	
	/**
	 * Email address to receive admin notices
	 * @var string
	 */
	private $administratoremail = '';
	
	/**
	 * Email address of the list
	 * @var string
	 */
	private $emailaddress = '';
	
	/**
	 * Email List ID.
	 * @var integer
	 */
	private $id;
	
	/**
	 * String to connect to IMAP host
	 * e.g. {imap.zoho.com:993/imap/ssl}INBOX
	 * @var String
	 */
	private $imaphost = '';
	
	/**
	 * Host for the list
	 * e.g. smtp.example.com
	 * Do not include port as there is a separate variable
	 * @var string
	 */
	private $host = '';
	
	/**
	 * Name of the list
	 * @var string
	 */
	private $name = '';

	/**
	 * SMTP port number
	 * @var int
	 */
	private $port = 0;
	
	/**
	 * Subscribers for this list
	 * @var mixed
	 */
	private $subscribers;
	
	/**
	 * IMAP / SMTP username
	 * Assumed to be the same... otherwise we have problems (or
	 * at least need another field).
	 * @var string
	 */
	private $username = '';
	
	/**
	 * Password
	 * Encrypted string.
	 * @var string
	 */
	private $password = '';
	
	private $processedDir = '';
	
	/**
	 * Class constructor.
	 * @param array $row Information to populate object.
	 */
	public function __construct($row = false) {
		if ($row != false) {
			$this->id = $row['EmailListID'];
			$this->name = $row['Name'];
			$this->emailaddress = $row['EmailAddress'];
			$this->imaphost = $row['IMAPHost'];
			$this->username = $row['Username'];
			$this->password = $row['Password'];
			$this->host = $row['Host'];
			$this->port = $row['Port'];
			$this->administratoremail = $row['AdministratorEmail'];
			$this->processedDir = $row['ProcessedDir'];
		}
	}
	
	public function deleteSubscriber(ListSubscriber &$subscriber) {
		$watcher = ObjWatcher::instance();
		$watcher->remove($subscriber);
		ListSubscriberDataManager::delete($subscriber->getID());
		unset($subscriber);
	}
	
	public function getAdministratorEmail() {
		return $this->administratoremail;
	}
	
	public function getActiveSubscribers() {
		$this->loadActiveSubscribers();
		return $this->activesubscribers;
	}
	
	public function  getEmailAddress() {
		return $this->emailaddress;
	}
	
	public function getHost() {
		return $this->host;
	}
	
	public function getID() {
		return $this->id;
	}
	
	public function getIMAPHost($raw = false) {
		return $raw? $this->imaphost : html_specialchars($this->imaphost);
	}
	
	public function getName($raw = false) {
		return $raw? $this->name : html_specialchars($this->name);
	}
	
	public function getPassword($raw = false) {
		$password = $this->simple_decrypt($this->password);
		return $raw? $password : html_specialchars($password);
	}
	
	public function getPort() {
		return $this->port;
	}
	
	public function getProcessedDir($raw = false) {
		return $raw? $this->processedDir : html_specialchars($this->processedDir);
	}
	
	public function getSubscribers() {
		$this->loadSubscribers();
		return $this->subscribers;
	}
	
	public function getUsername ($raw = false) {
		return $raw? $this->username : html_specialchars($this->username);
	}
	
	private function insert() {
		$this->id = EmailListDataManager::insert(array(
				$this->name,
				$this->emailaddress,
				$this->imaphost,
				$this->username,
				$this->password,
				$this->host,
				$this->port,
				$this->administratoremail,
				$this->processedDir,
		));
	}
	
	private function loadActiveSubscribers() {
		if (!$this->activesubscribers) {
			$this->activesubscribers = new ListSubscribers();
			$this->activesubscribers->loadActiveByList($this);
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
		if (!$this->subscribers) {
			$this->subscribers = new ListSubscribers();
			$this->subscribers->loadByList($this);
		}
	}
	
	/**
	 * Save the object in the database.
	 */
	public function save() {
		($this->id)? $this->update() : $this->insert();
	}
	
	public function setAdministratorEmail($administratoremail) {
		$this->administratoremail = $administratoremail;
	}
	
	public function setEmailAddress($address) {
		$this->emailaddress = $address;
	}
	
	public function setHost($host) {
		$this->host = $host;
	}
	
	/**
	 * Set string for connecting to IMAP
	 * e.g. {imap.zoho.com:993/imap/ssl}INBOX
	 * @param string $imaphost
	 */
	public function setIMAPHost($imaphost) {
		$this->imaphost = $imaphost;
	}
	
	/**
	 * Set the name of the list
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}
	
	public function setUsername($username) {
		$this->username = $username;
	}
	
	public function setPassword($password) {
		$this->password = $this->simple_encrypt($password);
	}
	
	public function setPort($port) {
		$this->port = $port;
	}
	
	/**
	 * Set the directory for the email to be moved to once processed
	 * 
	 * e.g. [Gmail]/Trash
	 * 
	 * @param string $processedDir
	 */
	public function setProcessedDir($processedDir) {
		$this->processedDir = $processedDir;
	}
	
	/**
	 * Update in the database.
	 */
	private function update() {
		EmailListDataManager::update(array(
			$this->name,
			$this->emailaddress,
			$this->imaphost,
			$this->username,
			$this->password,
			$this->host,
			$this->port,
			$this->administratoremail,
			$this->processedDir,
			$this->id
		));
	}
	
	// http://blog.justin.kelly.org.au/simple-mcrypt-encrypt-decrypt-functions-for-p/
	private function simple_encrypt($text)
	{
		return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, Prefs::SALT_EMAIL_LISTS, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
	}
	
	private function simple_decrypt($text)
	{
		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, Prefs::SALT_EMAIL_LISTS, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
	}
	
	
}