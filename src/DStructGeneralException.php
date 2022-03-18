<?php
namespace pub007\dstruct;
/**
 * DStructGeneralException class
 */
/**
 * General exception thrown by DStruct.
 * @package dstruct_common
 */
class DStructGeneralException extends Exception {

/**
 * Formats exception output in a way which is easier to read in a browser.
 * @return string
 * @see Exception::__toString()
 */
public function __toString() {
	echo "<pre class='DStructGeneralException'>";
	return "exception '".__CLASS__ ."' with message: \n\n".$this->getMessage()."\n\nin: ".$this->getFile().":".$this->getLine()."\n\nStack trace:\n".$this->getTraceAsString();
}

}
?>