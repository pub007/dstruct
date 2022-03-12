<?php

class IMAP {
	
	private $imap;
	private $password = '';
	private $port = 993;
	private $server = 'localhost';
	private $selfvalidatedcertificate = false;
	private $settingstring = '/imap/ssl';
	private $ssl = TRUE;
	private $username = '';
	
	public function connect() {
		// for some reason, we have to build the string before inserting it
		// and opening the stream. Otherwise won't connect!
		$str = '{' . $this->server . ':' . $this->port . $this->settingsstring . '}INBOX';
		$this->imap = imap_open($str, $this->username, $this->password);
	}
	
	public function setPassword($password) {
		$this->password = $password;
	}
	
	public function setPort($port) {
		$this->port = $port;
	}
	
	public function setSSL($ssl) {
		$this->$ssl = $ssl? true:false;
	}
	
	public function setServer($server) {
		$this->server = $server;
	}
	
	public function setSettingsString($settingsstring) {
		$this->settingsstring = $settingsstring;
	}
	
	public function setUsername($username) {
		$this->username = $username;
	}
	
	
	
}