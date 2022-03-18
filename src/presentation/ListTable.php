<?php
namespace pub007\dstruct\presentation;
/**
 * ListTable class
 */
/**
 * Build a table suitable for lists.
 * 
 * By a 'list' table, we mean a two column table with label:data pairs for example:
 * <code>
 * Product : Thingumy
 * Colour: Blue
 * Size: 25
 * </code>
 * @package dstruct_presentation
 */
class ListTable extends TableBuilder {
	/**
	 * Internal row counter.
	 * @var integer
	 */
	private $z = 0;
	
	/**
	 * Data for table.
	 * @var array
	 */
	private $arraydata = array();
	
	/**
	 * Class to be added to the table element.
	 * @var string
	 */
	private $tableclass;
	
	/**
	 * Class to be added to td elements on the left side of the table.
	 * @var string
	 */
	private $leftclass;
	
	/**
	 * Class to be added to the td elements on the right side of the table.
	 * @var string
	 */
	private $rightclass;
	
	/**
	 * Class constructor.
	 * @param array $dataset Data for table
	 * @param string $tableclass Class to be added to the table element.
	 * @param string $leftclass Class to be added to td elements on the left side of the table.
	 * @param string $rightclass Class to be added to the td elements on the right side of the table.
	 */
	public function __construct($dataset = false, $tableclass = 'listtable', $leftclass = 'listlabel', $rightclass = 'listitem') {
		if ($dataset) {
			if (is_object($dataset)) {
				$this->dbobjectdata = $dataset;
			} else {
				$this->arraydata = $arraydata;
			}
		}
		$this->tableclass = $tableclass;
		$this->leftclass = $leftclass;
		$this->rightclass = $rightclass;
	}
	
	/**
	 * Add a row to the table.
	 * @param $arraydata Array of data to add (should just have two elements!)
	 * @see TableBuilder::addRow()
	 */
	public function addRow($arraydata) {
		if (!is_array($arraydata)) {throw new DStructGeneralException('Row data must be an array');}
		$c = 0;
		foreach ($arraydata as $item) {
			$this->arraydata[$this->z][$c] = $item;
			$c++;
		}
		$this->z++;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see TableBuilder::clear()
	 */
	public function clear() {
		$this->z = 0;
		$this->arraydata = array();
		$this->class = 'datatable';
	}
	
	/**
	 * Write the table.
	 * 
	 * <var>$tablestart</var> and <var>$tableend</var> are useful if you
	 * need to buffer long tables etc.
	 * @param string $tablestart Include <table> in the string.
	 * @param string $tableend Include </table> in the string.
	 * @return string
	 */
	public function write($tablestart = true, $tableend = true) {
		$c = 0;
		$output = '';
		
		if ($tablestart) {$output .= "<table class='$this->tableclass'>\n";}
		
		// add any array data
		foreach ($this->arraydata as $row) {
			// if the modulo of c = 0 then even row
			if ($c % 2 == 0) {
				$output .= "<tr class='iseven'>\n";
			} else {
				$output .= "<tr>\n";
			}
			$c++;
			
			$output .= "<td class='$this->leftclass'>$row[0]</td>\n";
			$output .= "<td class='$this->rightclass'>$row[1]</td>\n";
			$output .= '</tr>';
		}
		
		if ($tableend) {$output .= "</table>\n";}
		
		return $output;
	}
}
?>