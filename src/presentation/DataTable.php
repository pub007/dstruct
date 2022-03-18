<?php
namespace pub007\dstruct\presentation;
/**
 * DataTable class
 */
/**
 * Write tables from arrays or datasets.
 * 
 * Helps building tables by either loading data in one bite via passing
 * an array or CSV file, or by adding the data one row at a time.
 * @package dstruct_presentation
 */
class DataTable extends TableBuilder {

/**
 * Contains the data supplied.
 * @var array
 */
private $arraydata = array();

/**
 * Class which will be added to the table element.
 * @var string
 */
private $class;

/**
 * Character enclosing CSV fields
 * @var string
 */
private $csvenclosedby = '"';

/**
 * CSV escape character.
 * @var string
 */
private $csvescape = '\'';

/**
 * Does the CSV have a header line?
 * @var boolean
 */
private $csvhasheader = true;

/**
 * CSV line ending.
 * @var string
 */
private $csvline = "\r\n";

/**
 * CSV field separator.
 * @var string
 */
private $csvseparator = ',';

/**
 * Database dataset.
 * @var array
 * @todo check datatype
 */
private $dbobjectdata;

/**
 * HTML encode output.
 * @var boolean
 */
private $encodeoutput = false;

/**
 * Headers for the table.
 * @var array
 */
private $headers;

/**
 * Bulk data is CSV.
 * @var boolean
 */
private $iscsv = false;

/**
 * Row counter.
 * @var integer
 */
private $z = 0;

/**
 *Class Constructor
 *<var>$dataset</var> can be used to 'bulk add' data to the table.
 *@param string $class The CSS class to be written to the table
 *@param mixed $dataset Either array or db resultset or CSV string
 *@param boolean $iscsv Data being added is a CSV string.
 */
public function __construct($class = 'datatable', $dataset = false, $iscsv = false) {
	if ($dataset) {
		if (is_object($dataset)) {
			$this->dbobjectdata = $dataset;
		} elseif ($iscsv) {
			$this->iscsv = true;
			$this->addCSV($dataset);
		} else {
			$this->arraydata = $dataset;
		}
		
	}
	$this->class = $class;
}

/**
 * Add CSV data to the table.
 * @param string $data
 */
public function addCSV($data) {
	$this->csvdata = $data;
	$sep = $this->csvenclosedby . $this->csvseparator . $this->csvenclosedby;
	
	// csvs often have a blank line at the end. Remove or get an incorrect row at end
	// we don't know the length of the line separator so we need to calculate e.g. \n is not the same as \r\n (2 chars)
	if (substr($data, (0 - strlen($this->csvline))) == $this->csvline) {
		$data = substr($data, 0, (0 - strlen($this->csvline)));
	}
	
	$lines = explode($this->csvline, $data);
	
	// get the header if exists
	if ($this->csvhasheader) {
		$headers = array_shift($lines);
		if ($this->csvenclosedby) { // remove enclose chars at start and end of line
			$headers = substr($headers, strlen($this->csvenclosedby), (0 - strlen($this->csvenclosedby)));
		}
		$this->headers = explode($sep, $headers);
	}
	
	foreach ($lines as $line) {
		if ($this->csvenclosedby) { // remove enclose chars at start and end of line
			$line = substr($line, strlen($this->csvenclosedby), (0 - strlen($this->csvenclosedby)));
		}
		$this->addRow(explode($sep, $line));
	}
}

/**
 *Add <th> elements to the table.
 *@param $headers array Header titles from left to right
 */
public function addHeaders($headers) {
	if (!is_array($headers)) {throw new DStructGeneralException('DataTable::addHeaders() - $headers parameter must be an array');}
	$this->headers = $headers;
}

/**
 *Add a row to the table.
 *
 *The array will be added to the end of the current
 *data, with the fields added as cells from left to right.<br />
 *There is currently no checking that the array has the correct
 *number of items.
 *@param $arraydata array
 */
public function addRow($arraydata) {
	if (!is_array($arraydata)) {throw new DStructGeneralException('DataTable::addRow() - Row data must be an array');}
	$c = 0;
	foreach ($arraydata as $item) {
		$this->arraydata[$this->z][$c] = $item;
		$c++;
	}
	$this->z++;
}

/**
 * Clear the object.
 * 
 * Removes all data, counters and header info.
 * @param boolean $clearsettings Clear all CSV settings too.
 */
public function clear($clearsettings = false) {
	$this->z = 0;
	$this->dbobjectdata = null;
	$this->arraydata = array();
	$this->headers = null;
	$this->class = 'datatable';
	$this->csvdata = '';
	if ($clearsettings) {
		$this->iscsv = false;
		$this->csvescape = '\'';
		$this->csvhasheader = true;
		$this->csvline = "\r\n";
		$this->csvseparator = ',';
	}
}

/**
 * Set the character CSV fields are enclosed by.
 * 
 * Default is double quote. Set an empty string if the CSV fields are not
 * enclose.
 * @param string $enc
 */
public function setEnclosedBy($enc) {$this->csvenclosedby = $enc;}

/**
 * HTML encode the output.
 * 
 * Encodes headers and data. Default is FALSE.
 * @param boolean $enc
 */
public function setEncodeOutput($enc) {$this->encodeoutput =($enc)? true : false;}

/**
 *Get the completed table.
 *
 *The <var>$tablestart</var> and <var>$tableend</var> params
 *can be used to suppress the <table> and </table> elements.<br />
 *This can be useful in a number of cases, such as buffering the
 *output of very long tables.
 *@param $tablestart boolean Include <table> in output
 *@param $tableend boolean Include </table> in output
 *@return string
 */
public function write($tablestart = true, $tableend = true) {
	$c = 0;
	$output = '';
	
	if ($tablestart) {$output .= "<table class='$this->class'>\n";}
	
	// write any headers sent
	if ($this->headers) {
		$output .= '<thead><tr>';
		foreach ($this->headers as $header) {
			if ($this->encodeoutput) {$header = html_specialchars($header);}
			$output .= "<th>$header</th>\n";
		}
		$output .= "</tr>\n</thead>";
		$c++;
	}
	
	$output .= "<tbody>\n";
	
	// if $result is an object we use PDO::fetchObject() to 'hydrate' our objects etc
	if (is_object($this->dbobjectdata)) {
		while ($row = $this->dbobjectdata->fetchObject()) {
			// if the modulo of c = 0 then even row
			if ($c % 2 == 0) {
				$output .= "<tr class='iseven'>\n";
			} else {
				$output .= "<tr>\n";
			}
			$c++;
			
			foreach ($row as $data) {
				if ($this->encodeoutput) {$data = html_specialchars($data);}
				$output .= "<td>$data</td>\n";
			}
			$output .= '</tr>';
		}
	}
	
	// add any array data
	foreach ($this->arraydata as $row) {
		// if the modulo of c = 0 then even row
		if ($c % 2 == 0) {
			$output .= "<tr class='iseven'>\n";
		} else {
			$output .= "<tr>\n";
		}
		$c++;
		
		if (is_array($row)) {
			foreach($row as $data) {
				if ($this->encodeoutput) {$data = html_specialchars($data);}
				$output .= "<td>$data</td>\n";
			}
			$output .= '</tr>';
		} else {
			$output .= "<td>$row</td>\n";
		}
	}
	
	if ($tableend) {$output .= "</tbody>\n</table>\n";}
	
	return $output;
}

}
?>