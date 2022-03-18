<?php
namespace pub007\dstruct\filetools;
/**
 * Class FSPermissions
 */
/**
 * Alter permissions on a *nix file system.
 * The system optionally will call clearstatcache() and it is recommended that you
 * are familiar with this PHP method: {@link @link http://php.net/manual/en/function.clearstatcache.php}.
 * Only tested on Centos5. May require a system administrator
 * to give some permissions on your server. This is beyond the scope
 * of this documentation.
 * @package dstruct_filetools
 * @todo Test and document on Ubuntu
 * @todo Some permissions changes were needed to allow this to work. Test and document.
 */
class FSPermissions {

/**
 * File or folder of interest.
 * @var string
 */
private $path = '';

/**
 * Info from PHP's stat() method.
 * @var array
 * @link http://php.net/manual/en/function.stat.php
 */
private $stat;

/**
 * Class constructor.
 * @param string $path The path to the file or folder of interest
 * @throws DStructGeneralException
 */
public function __construct($path) {
	if (!file_exists($path)) {throw new DStructGeneralException('FilePermissions::__construct() - File or Directory does not exist:' . html_specialchars($path));}
	$this->path = $path;
}

/**
 * Update the permissions.
 * 
 * <var>$options</var> is an array with the optional elements:
 * chmod = Octal representation of the permsission e.g. 0755 not 755
 * group = Group name or number
 * owner = Owner name or number
 * If no options are provided, the defaults in {@link Prefs} are used.
 * @param array $options See main text for info.
 * @link http://php.net/manual/en/function.chmod.php
 */
public function change($options = false) {
	$setting = (isset($options['chmod']))? $options['chmod'] : false;
	$this->changeMod($setting);
	$setting = (isset($options['group']))? $options['group'] : false;
	$this->changeGroup($setting);
	$setting = (isset($options['owner']))? $options['owner'] : false;
	$this->changeOwner($setting);
}

/**
 * Update the group.
 * 
 * <var>$setting</var> is the name or number of the Group to change to. If
 * there is no setting provided, the default in {@link Prefs} is used.
 * @param string $setting
 */
public function changeGroup($setting = false) {
	if (!$setting) {$setting = Prefs::FILE_DEFAULT_GROUP;}
	$execstring = "sudo /bin/chgrp $setting \"" . $this->path . '"';
	shell_exec($execstring);
}

/**
 * chmod the file / folder.
 * @param octal $setting e.g. 0755 not 755
 * @throws DStructGeneralException
 */
public function changeMod($setting = false) {
	if (!$setting) {$setting = Prefs::FILE_DEFAULT_PERMISSIONS;}
	if (!is_numeric($setting)) {throw new DStructGeneralException('FSPermission::changeMod() - value must be numeric');}
	chmod($this->path, $setting);
}

/**
 * Update the owner.
 * @param string $setting Name or number of new owner
 */
public function changeOwner($setting = false) {
	if (!$setting) {$setting = Prefs::FILE_DEFAULT_OWNER;}
	$execstring = "sudo /bin/chown $setting \"" . $this->path . '"';
	shell_exec($execstring);
}

/**
 * Get the current Group of file / folder.
 * @param boolean $refreshcache Clear the stat cache?
 * @return integer The number of the current Group
 * @link http://php.net/manual/en/function.clearstatcache.php
 */
public function getGroup($refreshcache = true) {
	$this->loadStatInfo($refreshcache);
	return $this->stat['gid'];
}

/**
 * Get the current Owner of file / folder.
 * @param boolean $refreshcache Clear the stat cache?
 * @return integer The number of the current Owner
 * @link http://php.net/manual/en/function.clearstatcache.php
 */
public function getOwner($refreshcache = true) {
	$this->loadStatInfo($refreshcache);
	return $this->stat['uid'];
}

/**
 * Refresh the info about the file / folder.
 * @param boolean $refreshcache Call clearstatcache()?
 * @link http://php.net/manual/en/function.clearstatcache.php
 */
private function loadStatInfo($refreshcache) {
	if ($refreshcache) {clearstatcache(true, $this->path);}
	$this->stat = stat($this->path);
}

}
?>