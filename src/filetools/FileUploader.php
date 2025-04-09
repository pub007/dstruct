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

	const STORAGE_TYPE_FILESYSTEM = 1;
	const STORAGE_TYPE_S3 = 2;

	/**
	 * Allowable mimetypes.
	 *
	 * @var array
	 */
	private $allowedMimetypes = [];

	/**
	 * Allowable file extensions.
	 *
	 * @var array
	 */
	private $allowedExtensions = [];

	private $files = [];
	
	private $imageTransform = [];

	private $errors = [];

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
	private $savePath = '';

	private $storageType = self::STORAGE_TYPE_FILESYSTEM;

	/**
	 * The names of the files after they are saved.
	 *
	 * @var array
	 */
	private $newname = null;

	/**
	 * Whether to throw an error if there is no file uploaded.
	 *
	 * @var bool
	 * @see FileUploader::isError()
	 */
	private $requirefile = false;

	private ?S3FileHandler $s3handler = null;

	/**
	 * PHP's possible upload errors.
	 *
	 * @var array
	 * @todo Can't we use the built in constants???
	 */
	private $uploaderrors = [
		UPLOAD_ERR_INI_SIZE => 'The uploaded file is too large.',
		UPLOAD_ERR_FORM_SIZE => 'The uploaded file is too large.',
		UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded.',
		UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
		UPLOAD_ERR_NO_TMP_DIR => 'Error finding temporary folder.',
		UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
		UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.'
	];

	public function __construct(?S3FileHandler $s3handler = null) {
		if ($s3handler) {
			$this->s3handler = $s3handler;
			$this->storageType = self::STORAGE_TYPE_S3;
		}
	}
	
	/**
	 * Process any uploaded files from the form
	 * 
	 * return [
	 *     'isErrors' => bool,  // any errors in the process
	 *     'errors' => [],  // top level errors.  Each file has its own errors in the files array
	 *     'files' => [],
	 *     'path' => string
	 * ]
	 * 
	 * @param string $inputField Name of the form field
	 * @return array
	 */
	public function process(string $inputField): array
	{
		$rtn = [
			'isErrors' => false,
			'errors' => [],
			'files' => [],
			'path' => ''
		];

		$files = $_FILES[$inputField] ?? false;

		if (! $files) {
			$rtn['errors'][] = 'Unable to find files from the named form';
			return $rtn;
		}
		
		if ($files['name'][0] == '') {
			return $rtn;
		}
		
		if ($this->storageType == self::STORAGE_TYPE_FILESYSTEM && ! $this->checkSavePath()) {
			$rtn['errors'][] = 'Save path is invalid';
			$rtn['isErrors'] = true;
			return $rtn;
		}

		$rtn['path'] = $this->savePath;

		foreach ($files['name'] as $key => $name) {
			$rtn['files'][$key] = [
				'error' => null,
				'originalName' => $name,
				'newName' => null,
				'size' => $files['size'][$key],
				'mimetype' => null
			];
			
			// any errors for the file are returned in the file array
			if ($files['error'][$key]) {
				$rtn['files'][$key]['error'] = $this->uploaderrors[$files['error'][$key]];
				$rtn['isErrors'] = true;
				continue;
			}

			$tmpName = $files['tmp_name'][$key];
			if (!$tmpName) {
				$tmpName = $files['name'][$key];
			}
			$extension = pathinfo($name, PATHINFO_EXTENSION);

			$finfo = new \finfo(FILEINFO_MIME); // return mime type ala mimetype extension
			$mime = $finfo->file($tmpName);
			$rtn['files'][$key]['mimetype'] = $mime;

			if (!$this->isValidFile($extension, $mime)) {
				$rtn['files'][$key]['error'] = "File '$name' has invalid extension or MIME type [$extension | $mime].";
				continue;
			}

			if ($this->newname) {
				$newFileName = $this->newname . '.' . $extension;
			} else {
				$newFileName = pathinfo($name, PATHINFO_FILENAME) . '.' . $extension;
			}
			
			// example image transform ['maxwidth' => 500, 'maxheight' => 500]
			// if we have a transform, we need to do it before we save
			// use SimpleImage to do the transform
			if ($this->imageTransform) {
				$image = new SimpleImage();
				$image->fromFile($tmpName);
				$image->resize($this->imageTransform['maxWidth'], $this->imageTransform['maxHeight']);
				$tmpName = $image->toString();
			}

			if ($this->storageType === self::STORAGE_TYPE_FILESYSTEM) {
				$destination = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $this->savePath . $newFileName;

				$a = 0;

				if (file_exists($destination)) {
					switch ($this->overwritemode) {
						case self::OVERWRITE_NO:
							$rtn[$name]['error'] = "File $name already exists.";
							continue 2;
						case self::OVERWRITE_APPEND:
							$c = 1;
							// loop until we find a free file name
							while ($a < 1) {
								$savenametemp = $tmpName . $this->overwritechar . $c . '.' . $extension;
								if (file_exists($this->savePath . $savenametemp) == false) {
									$newFileName = $savenametemp;
									break;
								}
								$c ++;
							}
					}
				}

				if (!move_uploaded_file($tmpName, $destination)) {
					$rtn['files'][$key]['error'] = "Failed to save file '$name' to the destination.";
					continue;
				}
			} elseif ($this->storageType === self::STORAGE_TYPE_S3) {
				$result = $this->s3handler->uploadFile($newFileName, $tmpName);
				
				if ($result == false) {
					error_log("Upload failed");
				} else {
					//error_log("Upload said" . print_r($result, true));
				}
			}

			$rtn['files'][$key]['newName'] = $newFileName;
		}

		return $rtn;
	}

	private function isValidFile(string $extension, string $mime): bool
	{
		return in_array($extension, $this->allowedExtensions) && in_array($mime, $this->allowedMimetypes);
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
     * Set the image transform to apply to the file.
     *
     * @param array $transform
     *        	Transform array
     * @see FileUploader::getImageTransform()
     */
	public function setImageTransform(array $transform)
	{
		$this->imageTransform = $transform;
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
	public function setSavePath(string $path): bool
	{
		$this->savePath = $path;
		return $this->checkSavePath();
	}

	public function checkSavePath(): bool
	{
		if (! str_ends_with($this->savePath, DIRECTORY_SEPARATOR)) {
			$this->savePath .= DIRECTORY_SEPARATOR;
		}
		//throw new \Exception(dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->savePath);
		return is_dir($this->savePath);
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
		$this->allowedMimetypes = $mimes;
		$this->allowedExtensions = $extns;
	}
	
	/**
	 * Get the settings for the image transform.
	 * 
	 * @return array
	 */
	public function getImageTransform(): array
	{
		return $this->imageTransform;
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
		return $this->savePath;
	}

	/**
	 * Get whether errors will be shown if no file is uploaded.
	 *
	 * @see setFileRequired()
	 * @see FileUploader::setFileRequired()
	 * @return boolean
	 */
	public function getFileRequired(): bool
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
}
