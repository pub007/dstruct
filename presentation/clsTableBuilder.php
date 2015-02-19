<?php
/**
 * TableBuilder class.
 */
/**
 * Build tables dynamically.
 *@package dstruct_presentation
 */
abstract class TableBuilder {
	
	/**
	 * Add a row of data.
	 * @param mixed $row
	 */
	public function addRow($row) {}
	
	/**
	 * Clear data and settings.
	 */
	public function clear() {}
	
	/**
	 * Send the output.
	 */
	public function write() {}
}
?>