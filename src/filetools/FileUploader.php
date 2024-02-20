<?php
namespace pub007\dstruct\filetools;


/**
 * Class FileUploader
 */
/**
 * Simplified wrapper for file uploading.
 *
 * @package dstruct_filetools
 * @author David Lidstone
 */
class FileUploader
{

	/**
	 * Overwrite existing files.
	 *
	 * @var integer
	 * @see FileUploader::setOverwriteMode()
	 * @todo sort methods alphabetically
	 */
	const OVERWRITE_NO = 0;

	/**
	 * Don't overwrite existing files.
	 *
	 * @var integer
	 * @see FileUploader::setOverwriteMode()
	 */
	const OVERWRITE_YES = 1;

	/**
	 * Append a number to the file name if there is an existing file.
	 *
	 * @var integer
	 * @see FileUploader::setOverwriteMode()
	 */
	const OVERWRITE_APPEND = 2;

	/**
	 * The key for the first upload is set to default.
	 *
	 * @var integer
	 */
	private $defaultkey = 0;

	/**
	 * Allowable mimetypes.
	 *
	 * @var array
	 */
	private $mimetypes = array();

	/**
	 * Allowable file extensions.
	 *
	 * @var array
	 */
	private $extensions = array();

	/**
	 * Overwrite Mode.
	 *
	 * @var integer
	 * @see FileUploader::setOverwriteMode()
	 */
	private $overwritemode = 0;

	/**
	 * Separator in overwrite mode.
	 *
	 * @var String
	 * @see FileUploader::setOverwriteChar()
	 */
	private $overwritechar = '_';

	/**
	 * The save path.
	 * This should be defined from the root of the filesystem.
	 *
	 * @var string
	 */
	private $savepath = '';

	/**
	 * Number of files uploaded.
	 *
	 * @var integer
	 */
	private $totalfiles = 0;

	/**
	 * The name of the file after it is saved.
	 *
	 * @var string
	 */
	private $newname = '';

	/**
	 * Whether to throw an error if there is no file uploaded.
	 *
	 * @var unknown
	 * @see FileUploader::isError()
	 */
	private $requirefile = false;

	/**
	 * PHP's possible upload errors.
	 *
	 * @var array
	 * @todo Can't we use the built in constants???
	 */
	private $uploaderrors = array(
		UPLOAD_ERR_INI_SIZE => 'The uploaded file is too large.',
		UPLOAD_ERR_FORM_SIZE => 'The uploaded file is too large.',
		UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded.',
		UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
		UPLOAD_ERR_NO_TMP_DIR => 'Error finding temporary folder.',
		UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
		UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.'
	);

	/**
	 * Class constructor.
	 */
	public function __construct()
	{
		// get a default key and total files with size
		$c = 0;
		foreach ($_FILES as $key => $file) {
			if ($c == 0) {
				$this->defaultkey = $key;
			}

			if ($file['size'] != 0) {
				$this->totalfiles ++;
			}
			$c ++;
		}
	}

	/**
	 * Overwrite mode for saving files.
	 *
	 * Overwrite can be one of {@link OVERWRITE_NO}, {@link OVERWRITE_YES} or
	 * {@link OVERWRITE_APPEND}. Default is OVERWRITE_NO. If NO, any existing
	 * file will not be saved over, and {@link save()} will return false if a
	 * file already exists. If YES, any existing file will be overwritten without
	 * notice. If APPEND and a file exists, the overwrite character (see {@link setOverwriteChar()})
	 * and a number is appended to the file. The number will be the next free integer,
	 * starting at 1. To find the final name the file is given, use {@link getNewName()}.
	 *
	 * @param integer $mode
	 *        	OVERWRITE_ Constant as above
	 */
	public function setOverwriteMode($mode)
	{
		$this->overwritemode = $mode;
	}

	/**
	 * Sets a character to use when in Overwrite mode.
	 *
	 * If {@link setOverwriteMode()} is set to {@link OVERWRITE_APPEND}, files
	 * are not overwritten but have this overwrite character and a number
	 * appened to the file name. Default is _ (underscore).<br />
	 * Be wary of restrictions due to operating systems.
	 *
	 * @param string $char
	 */
	public function setOverwriteChar($char)
	{
		$this->overwritechar = $char;
	}

	/**
	 * Does the script expect errors if no file is uploaded?
	 *
	 * @param boolean $bln
	 * @see FileUploader::isErrors()
	 */
	public function setFileRequired($bln)
	{
		$this->requirefile = $bln;
	}

	/**
	 * Set the new name of the file manually.
	 *
	 * The file name is usually preserved if a file by that name is not
	 * already present (@see setOverwriteMode()}. This allows setting of
	 * the name manually before saving. Use the full name, excluding
	 * the extension.
	 *
	 * @param string $name
	 */
	public function setNewName($name)
	{
		$this->newname = $name;
	}

	/**
	 * Set the path to save files.
	 *
	 * Must be from absolute route e,g, /var/www.... or d:\domains\...
	 * <b>WARNING:</b> Do not let users enter the save path. Untested, but if
	 * this is allowable...<br />
	 * "../../../place_my_evil.exe"<br />
	 * then could be very dangerous! Even if tested and it doesn't work, it could
	 * be using Absolute Path mode!<br />
	 * Also, you may need to call clearstatcache()
	 *
	 * @param string $path
	 * @return boolean Success or failure when setting path (i.e. path exists)
	 * @todo Test for relative path breakouts
	 * @todo Add parameter to call clearstatcache()?
	 */
	public function setSavePath($path)
	{
		if (substr($path, strlen($path) - 1, 1) != '/') {
			$path .= '/';
		} // make sure path ends with '/'
		  // echo $path;
		if (! is_dir($path)) {
			return false;
		}
		$this->savepath = $path;
		return true;
	}

	/**
	 * Limit allowable file types.
	 *
	 * Types must be in lower-case for the comparision to work
	 *
	 * @param array $mimes
	 *        	Allowable Mime-Types
	 * @param array $extns
	 *        	Allowable file-extensions
	 */
	public function setAllowableTypes(array $mimes, array $extns)
	{
		$this->mimetypes = $mimes;
		$this->extensions = $extns;
	}

	/**
	 * Customise error messages.
	 *
	 * Replaces {@link $uploaderrors} array. Must
	 * have same format
	 *
	 * @param array $errors
	 * @todo Remove if we can build in PHP's errors instead of copying here
	 */
	public function setUploadErrors($errors)
	{
		$this->uploaderrors = $errors;
	}

	/**
	 * Get the name of the saved file.
	 *
	 * Will be empty if the file has not been saved yet.
	 *
	 * @see setNewName()
	 * @return string
	 */
	public function getNewName()
	{
		return $this->newname;
	}

	/**
	 * Set the character used when file already exists.
	 *
	 * @see setOverwriteChar()
	 * @return string
	 */
	public function getOverwriteChar()
	{
		return $this->overwritechar;
	}

	/**
	 * Get the path of the saved file.
	 * Will be empty if the file has not been saved yet.
	 *
	 * @see setSavePath()
	 * @return string
	 */
	public function getSavePath()
	{
		return $this->savepath;
	}

	/**
	 * Returns the key of the default file.
	 *
	 * The default file will be the first file uploaded or the file manually
	 * set to default. The key is used by other methods of this class.
	 *
	 * @see setDefault()
	 * @return string
	 */
	public function getDefault()
	{
		return $this->defaultkey;
	}

	/**
	 * Get whether errors will be shown if no file is uploaded.
	 *
	 * @see setFileRequired()
	 * @see FileUploader::setFileRequired()
	 * @return boolean
	 */
	public function getFileRequired()
	{
		return $this->requirefile;
	}

	/**
	 * Set the overwrite mode for existing files.
	 *
	 * @see setOverwriteMode()
	 * @return integer
	 */
	public function getOverwriteMode()
	{
		return $this->overwritemode;
	}

	/**
	 * Fetch and errors found during the upload process.
	 *
	 * @see setUploadErrors()
	 * @return array
	 */
	public function getUploadErrors()
	{
		return $this->uploaderrors;
	}

	/**
	 * Set a default uploaded file to be used.
	 *
	 * For example, if your form field is 'userfile', you can set this
	 * to 'userfile' and not need to keep supplying a key.
	 *
	 * @param string $key
	 * @return boolean False if no file by that name exists
	 */
	public function setDefault($key)
	{
		if (! array_key_exists($key, $_FILES)) {
			return false;
		} else {
			$this->defaultkey = $key;
			return true;
		}
	}

	/**
	 * Check for errors in the upload process.
	 *
	 * Return depends on whether an uploaded file is required. See
	 * {@link setFileRequired()}.
	 *
	 * @return boolean
	 * @param string $key
	 *        	{@link setDefault()}
	 */
	public function isError($key = false): bool
	{
		$key = $this->validKey($key);

		if (!$key && $this->requirefile) {
			if (empty($_FILES)) {
				return true;
			}
		}

		// what if more errors added?...
		if ($_FILES[$key]['error'] > UPLOAD_ERR_EXTENSION) {
			return true;
		}
		if ($this->requirefile) {
			return (array_key_exists($_FILES[$key]['error'], $this->uploaderrors)) ? true : false;
		} else {
			return ((array_key_exists($_FILES[$key]['error'], $this->uploaderrors)) && ($_FILES[$key]['error'] != 4)) ? true : false;
		}
	}

	/**
	 * Error string if generated by the upload process.
	 *
	 * @param string $key
	 *        	{@link setDefault()}
	 * @return string
	 */
	public function getErrorMessage($key = false): array|false
	{
		$key = $this->validKey($key);

		var_dump($_FILES);

		if ($key) {
			if ($_FILES[$key]['error'] > UPLOAD_ERR_EXTENSION) {
				return 'An unknown error occurred';
			}

			if ($this->isError($key)) {
				return [$this->uploaderrors[$_FILES[$key]['error']]];
			}
		}

		$errors = [];

		if (empty($_FILES) && $this->requirefile) {
			$errors[] = $this->uploaderrors[UPLOAD_ERR_NO_FILE];
		}

		foreach ($_FILES as $key => $file) {
			$err = $file['error'] ?? false;

			if ($err) {
				$errors[] = $this->uploaderrors[$_FILES[$key]['error']];
			}
		}

		return $errors;
	}

	/**
	 * Error number if generated by the upload process.
	 *
	 * @param string $key
	 *        	{@link setDefault()}
	 * @return integer
	 */
	public function getErrorNumber($key = false)
	{
		$key = $this->validKey($key);
		return $_FILES[$key]['error'];
	}

	/**
	 * Size of the file in ?bytes?
	 *
	 * @param string $key
	 *        	{@link setDefault()}
	 * @return integer
	 * @todo check return is in bytes
	 */
	public function size($key = false)
	{
		$key = $this->validKey($key);
		return $_FILES[$key]['size'];
	}

	/**
	 * Full name of the uploaded file.
	 *
	 * @param string $key
	 *        	{@link setDefault()}
	 * @return string
	 */
	public function fullName($key = false)
	{
		$key = $this->validKey($key);
		return $_FILES[$key]['name'];
	}

	/**
	 * Get the name part of the uploaded file (no extension).
	 *
	 * @param string $key
	 *        	{@link setDefault()}
	 * @return string
	 */
	public function nameOnly($key = false)
	{
		$key = $this->validKey($key);
		return pathinfo($_FILES[$key]['name'], PATHINFO_FILENAME);
	}

	/**
	 * Extension part of the uploaded file's name.
	 *
	 * @param string $key
	 *        	{@link setDefault()}
	 * @return string
	 */
	public function extension($key = false)
	{
		$key = $this->validKey($key);
		return pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION);
	}

	/**
	 * Mime type of the file.
	 *
	 * @param string $key
	 *        	{@link setDefault()}
	 * @return string
	 */
	public function contentType($key = false)
	{
		$key = $this->validKey($key);
		return $_FILES[$key]['type'];
	}

	/**
	 * Does a file by the key exist.
	 *
	 * The key is the internal 'name' of the file - usually
	 * specified by the name attribute of your form's file
	 * element
	 *
	 * @param string $key
	 *        	{@link setDefault()}
	 * @return boolean
	 */
	public function exists($key = false)
	{
		$key = $this->validKey($key);
		return ($key && $_FILES[$key]['size'] > 0) ? true : false;
	}

	/**
	 * Check the file type is allowable.
	 * Checks both extension and mime against any provided
	 * arrays: {@link setAllowableTypes}
	 *
	 * @param string $key
	 *        	{@link setDefault()}
	 * @return boolean
	 */
	public function isAllowableType($key = false)
	{
		$key = $this->validKey($key);
		if (in_array(strtolower($_FILES[$key]['type']), $this->mimetypes) == false) {
			return false;
		}
		if (in_array(strtolower($this->extension($key)), $this->extensions) == false) {
			return false;
		}
		return true;
	}

	/**
	 * Get the name of the file in its temporary folder.
	 *
	 * @param string $key
	 *        	{@link setDefault()}
	 * @return string
	 */
	public function getTempFile($key = false)
	{
		$key = $this->validKey($key);
		return $_FILES[$key]['tmp_name'];
	}

	/**
	 * Count uploaded files.
	 *
	 * Files with 0 size are NOT counted.
	 *
	 * @return integer
	 */
	public function count()
	{
		return $this->totalfiles;
	}

	/**
	 * Saves the file with the previously specified parameters.
	 *
	 * @param string $key
	 *        	{@link setDefault()}
	 * @return boolean True on success
	 * @todo Should throw a descriptive error if can not save
	 */
	public function save($key = false): bool
	{
		$key = $this->validKey($key);
		$savename = '';
		$a = 0;
		if ($this->newname) {
			$savename .= $this->newname . '.' . $this->extension();
		} else {
			$savename .= $this->fullName();
		}
		if (file_exists($this->savepath . $savename)) {
			switch ($this->overwritemode) {
				case self::OVERWRITE_NO:
					return false; // don't overwrite file - just fail
				case self::OVERWRITE_APPEND:
					$c = 1;
					// loop until we find a free file name
					while ($a < 1) {
						$savenametemp = pathinfo($savename, PATHINFO_FILENAME) . $this->overwritechar . $c . '.' . pathinfo($savename, PATHINFO_EXTENSION);
						if (file_exists($this->savepath . $savenametemp) == false) {
							$savename = $savenametemp;
							break;
						}
						$c ++;
					}
			}
		}
		$this->newname = pathinfo($savename, PATHINFO_FILENAME); // save the new name
		if (! move_uploaded_file($_FILES[$key]["tmp_name"], $this->savepath . $savename)) {
			return false;
		}
		return true;
	}

	/**
	 * Returns default, first or specified key.
	 *
	 * @param string $key
	 *        	{@link setDefault()}
	 * @return mixed
	 */
	private function validKey($key): mixed
	{
		if (! $key) {
			$key = $this->defaultkey;
		}

		return $key;
	}
}
