<?php
namespace pub007\dstruct;
/**
 * SMTPEmail class
 */
/**
 * Wraps PEAR's Mail and Mail_mime packages.
 * 
 * Provides a little extra functionality.<br />
 * Requires PEAR mail and mail_mime (if you want to add attachements) packages installed.<br />
 * Can send single attachements and plain text or html/plain
 * mails. This functionality is automatic dependant on what content
 * has been added to the mail before send.<br />
 * @package dstruct_common
 */
class SMTPEmail {

/**
 * The data being attached.
 * @var array
 */
private $attachments = array();

/**
 * The given mime-type of the attachement.
 * @var string
 */
private $attachmentmime;

/**
 * Email body text.
 * @var string
 */
private $body;

/**
 * The HTML body text.
 * @var string
 */
private $bodyhtml;

/**
 * SMTP host.
 * @var string
 */
private $host = 'localhost';

/**
 * SMTP account user name.
 * @var string
 */
private $username = 'user';

/**
 * @ignore
 */
private $password = '';

/**
 * Port for SMTP.
 * @var inteter
 */
private $port = 25;

/**
 * Email to field.
 * @var string
 */
private $to;

/**
 *Email from field.
 *@var string
 */
private $from;

/**
 *Email replyto field.
 *@var string
 */
private $replyto;

/**
 *Email subject
 *@var string
 */
private $subject;

/**
 *Page to redirect to on successful send.
 *@var string
 */
private $redirectlocation;

/**
 * Use javascript to redirect the user after send?
 * 
 * If set to false, uses header() to redirect to the
 * redirect location.
 * If set to true, echos a javascript block with a 
 * window.location call
 * @var boolean
 */
private $javascriptredirect = false;

/**
 * Class constructor.
 */
public function __construct() {
	$this->setDefaultCredentials();
}

/**
 *Attach a file to the email
 *@param string $file Path and name of the file
 *@param string $mime File's mime-type
 */
public function attach($file, $mime = 'application/octet-stream') {
	$this->attachments[] = array(
			'filepath' => $file,
			'mime' => $mime
	);
}

/**
 * Reset the object.
 * 
 * If <var>$clearcredentials</var> is true then will also reset
 * the credentials to the defaults (will use the ones if set in {@link Prefs})
 * and the replyto field.
 * @param string $clearcredentials Reset credentials to defaults
 */
public function clear($clearcredentials = false) {
	$this->clearAttachments();
	$this->attachmentmime =
	$this->body =
	$this->bodyhtml =
	$this->to = 
	$this->subject =
	$this->redirectlocation = 
		null;
		
	if ($clearcredentials) {
		$this->replyto = null;
		$this->setDefaultCredentials();
	}
}

public function clearAttachments() {
	$this->attachments = array();
}

/**
 *Email body.
 *@return string
 */
public function getBody() {return $this->body;}

/**
 *From field of email.
 *@return string
 */
public function getFrom() {return $this->from;}

/**
 *SMTP host setting.
 *@return string
 */
public function getHost() {return $this->host;}

/**
 * Whether to use header() or a javascript window.location script to redirect the user on successful send.
 * @return boolean
 * @see setJavascriptRedirect()
 */
public function getJavascriptRedirect() {return $this->javascriptredirect;}

/**
  * Set the port of the SMTP MTA.
  * 
  * Port would usually be 25, but many ISPs and companies use other ports for authenticated emails. This
  * allows sending from outside the 'trusted hosts' if credentials are provided.
  * @return integer
  */
public function getPort() {return $this->port;}

/**
 * Location user will be redirected to on successful send.
 * @return string
 */
public function getRedirect() {return $this->redirectlocation;}

/**
 *Email ReplyTo field.
 *@return string
 */
public function getReplyTo() {return $this->replyto;}

/**
 *Subject field of email.
 *@return string
 */
public function getSubject() {return $this->subject;}

/**
 *To field of email.
 *@return string
 */
public function getTo() {return $this->to;}

/**
 *SMTP user name setting.
 *@return string
 */
public function getUserName() {return $this->username;}

/**
 * Commits the SMTP send using PEAR mail.
 * 
 * N.B. Also attempts to redirect user depending on vars {@link $redirectlocation}
 * and {@link SMTPEmail::$javascriptredirect}.<br />
 * Automatically selects plain text or mime email depending on what
 * content or attachements have been added.
 * @return boolean
 */
public function send() {
	// require PEAR module Mail
	// also need Net_SMTP installed on server
	require_once 'Mail.php';
	
	//make sure there is something in the required fields
	if (!$this->host) {throw new DStructGeneralException('SMTP::send() - No HOST provided');}
	if (!$this->username) {throw new DStructGeneralException('SMTP::send() - No USERNAME provided');}
	if (!$this->to) {throw new DStructGeneralException('SMTP::send() - You must provide a TO field');}
	if (!$this->from) {throw new DStructGeneralException('SMTP::send() - You must provide a FROM field');}
	if (!$this->body) {throw new DStructGeneralException('SMTP::send() - You must send something in the body');}
	
	if (!$this->replyto) {$this->replyto = $this->from;}
	
	//create an array for the extended mail information
	//include date as per RFC822 or otherwise locally delivered mails have 1970
	//date and end up at the bottom of inbox!
	$headers = array ('From' => $this->from,
		'To' => $this->to,
		'Subject' => $this->subject,
		'Reply-To' => $this->replyto,
		'Date' => date("r"));
	
	// if we need to send a mime-mail (HTML or attachments);
	if ($this->bodyhtml || count($this->attachments)) {
		require_once 'Mail/mime.php';
		$mime = new Mail_mime("\n"); // usually \r\n but Mail requires \n
		
		$mime->setTXTBody($this->body);
		$mime->setHTMLBody($this->bodyhtml);
		foreach ($this->attachments as $attachment) {
			var_dump($attachment);
			$mime->addAttachment($attachment['filepath'], $attachment['mime']);
		}
		$body = $mime->get();
		$headers = $mime->headers($headers);
		
	} else { // not a mime mail
		$body = $this->body;
		
	}
	
	// following failure of name resolution by fsockopen, fopen etc when linux kernels were changed, we 
	// want to reliably find the IP of the host and use that instead. gethostbyname() seems reliable!
	if (filter_var($this->host, FILTER_VALIDATE_IP)) {
		$hostip = $this->host;
	} else {
		$hostip = gethostbyname($this->host);
	}
	
	$smtp = Mail::factory('smtp',
		array ('host' => $hostip,
		'auth' => true,
		'username' => $this->username,
		'password' => $this->password,
		'port' => $this->port));
	
	//send the mail using SMTP
	$mail = $smtp->send($this->to, $headers, $body);
	
	//catch any error through PEAR
	if (PEAR::isError($mail)) {
		throw new DStructGeneralException('SMTP::send() - PEAR::mail says: ' . $mail->getMessage());
	} else { // redirect if required
		if ($this->redirectlocation) {
			if ($this->javascriptredirect == false) {
				//redirect to thanks page
				//don't bother returning true
				header("location: " . $this->redirectlocation);
				exit;
			} else {
				//write javascript redirect
				echo '<script type="text/javascript">
					<!--
					window.location = "'.$this->redirectlocation.'"
					//-->
					</script>'."\n";
				return true;
			}
		} else {
			return true;
		}
	}
}

/**
 *Set email Body.
 *@param string
 */
public function setBody($body) {$this->body = $body;}

/**
 *Set the HTML body of the email
 *Will automatically trigger creating a mime email if set.
 *@param string $html
 */
public function setBodyHTML($html) {$this->bodyhtml = $html;}

/**
 * Set the credentials from {@link Prefs} constants.
 */
private function setDefaultCredentials() {
	if (defined('Prefs::EMAIL_DEFAULT_USERNAME')) {
		// if the username is also a valid email address, we can use it as the default FROM var
		if (Validate::isEmailAddress(Prefs::EMAIL_DEFAULT_USERNAME)) {$this->from = Prefs::EMAIL_DEFAULT_USERNAME;}
		$this->username = Prefs::EMAIL_DEFAULT_USERNAME;
	}
	if (defined('Prefs::EMAIL_DEFAULT_HOST')) {$this->host = Prefs::EMAIL_DEFAULT_HOST;}
	if (defined('Prefs::EMAIL_DEFAULT_PASSWORD')) {$this->password = Prefs::EMAIL_DEFAULT_PASSWORD;}
	if (defined('Prefs::EMAIL_DEFAULT_PORT')) {$this->port = Prefs::EMAIL_DEFAULT_PORT;}
}

/**
 *Set email From field.
 *@param string
 */
public function setFrom($from) {$this->from = $from;}

/**
 * Set SMTP host e.g. smtp.example.com
 * @param string
 */
public function setHost($host) {$this->host = $host;}

/**
 * Set javascript redirect.
 * 
 * True echos a javascript window.location call to the browser.
 * False uses a PHP header() call, which is the default.
 * @param boolean
 */
public function setJavascriptRedirect($javascriptredirect) {$this->javascriptredirect = $javascriptredirect;}

/**
 *Set SMTP password.
 *@param string
 */
public function setPassword($password) {$this->password = $password;}

/**
 * Set Host port.
 *
 * Port would usually be 25, but many ISPs and companies use other ports for authenticated emails. This
 * allows sending from outside the 'trusted hosts' if credentials are provided.
 * @param integer
 */
public function setPort($port) {$this->port = $port;}

/**
 * Set the email recipient.
 * @param string $to
 */
public function setRecipient($to) {$this->to = $to;}

/**
 * Set location to redirect user on successful send.
 * 
 * WARNING: Be careful with this setting as it is echoed straight out to the browser and under
 * certain circumstances could be a security hole. This method does no checking on the validity
 * or security of the input so you must do this in your script.
 * @param string
 */
public function setRedirect($redirectlocation) {$this->redirectlocation = $redirectlocation;}

/**
 * Set email ReplyTo field.
 * @param string
 */
public function setReplyTo($replyto) {$this->replyto = $replyto;}

/**
 * Set email Subject
 * @param string
 */
public function setSubject($subject) {$this->subject = $subject;}

/**
 * Set Email To field
 * @param string
 */
public function setTo($to) {$this->to = $to;}

/**
 * Set SMTP username
 * @param string
 */
public function setUserName($username) {$this->username = $username;}

}
?>