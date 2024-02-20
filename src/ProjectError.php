<?php
namespace pub007\dstruct;
/**
 * ProjectError class
 */
/**
 * Aids collation of error messages, and helps test whether errors exist.
 *
 * Simple wrapper around an array.
 * Typically useful for processing user input errors.<br>
 * Example:
 * <code>
 * $errobj = new ProjectError;
 * if ($validuserinput == false) {
 * 	$errobj->addError('Your input is invalid');
 * }
 * if (!$errobj->isErrors()) {
 * 	//do stuff
 * } else {
 *   $errorsoutput = Format::asMessage($errobj->getErrors);
 * }
 * </code>
 * Works well with {@link Format::asMessage()} which  can output a CSS formated 'error list'.
 * @package dstruct_common
 */
class ProjectError implements \Iterator {

	/**
	 * Array containing any errors
	 * @var array
	 */
	private $errors = [];

	/**
	 * Add an error.
	 *
	 * Fails if there is no actual error to add, which avoids empty
	 * error strings being displayed to users (e.g. when using FileUploader
	 * it is easy to introduce a 'non-error'
	 *@param string
	 *@return boolean false if $error evaluates to false and no error is added
	 */
	public function addError($error): bool
	{
		if ($error) {
			$this->errors[] = $error;
			return true;
		}
		return false;
	}

	/**
	 *Are any errors stored in the object?
	 *@return boolean
	 */
	public function isErrors(): bool
	{
		return (sizeof($this->errors) > 0)? true : false;
	}

	/**
	 *Clears any errors stored in the object.
	 */
	public function clear(): void
	{
		$errors = array();
	}

	/**
	 * The current number of errors.
	 * @return integer
	 */
	public function count(): int
	{
		return count($this->errors);
	}

	/**
	 *Get any errors stored in the object.
	 *@return array
	 */
	public function getErrors(): array
	{
		if (count($this->errors) > 0) {
			return $this->errors;
		} else {
			return false;
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see \Iterator::valid()
	 */
	public function valid(): bool
	{
		return (!is_null(key($this->errors)));
	}

	/**
	 * (non-PHPdoc)
	 * @see \Iterator::rewind()
	 */
	public function rewind(): void
	{
		reset($this->errors);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Iterator::current()
	 */
	public function current(): mixed
	{
		return current($this->errors);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Iterator::key()
	 */
	public function key(): mixed
	{
		return key($this->errors);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Iterator::next()
	 */
	public function next(): void
	{
		next($this->errors);
	}
}
