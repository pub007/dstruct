<?php
/**
 * CSVOutput class.
 */
/**
 * Create a CSV file.
 * 
 * Takes arrays and turns them into CSV files.
 * @package dstruct_presentation
 * @author David
 * @todo Accept recordset objects like DataTable?
 */
class CSVOutput {

/**
 * Export to string
 * @var string
 */
const EXPORT_STRING = 'S';

/**
 * Export to browser
 * @var string
 */
const EXPORT_BROWSER = 'I';

/**
 * Export to download
 * @var string
 */
const EXPORT_DOWNLOAD = 'D';

/**
 * Export to file
 * @var string
 */
const EXPORT_FILE = 'F';
	
/**
 * Buffer of output data.
 * @var string
 */
private $buffer = '';

/**
 * Enclose fields with.
 * @var string
 */
private $enclosedby = '';

/**
 * Escape data with.
 * @var string
 */
private $escape = '\'';

/**
 * Number of headers for the csv.
 * @var integer
 */
private $headercount = 0; // the number of header fields added to the csv

/**
 * Default new line setting.
 * @var string
 */
private $line = "\r\n";

/**
 * Field separator
 * @var string
 */
private $separator = ',';


/**
 * Class constructor.
 * 
 * Accepts multi-dimensional array suitable for {@link addRows()} and
 * will return a csv output. Quick way of generating a csv, but not
 * suitable if you want to change default field enclosure character,
 * add headers etc.
 * @param array $dataset
 * @return string CSV formatted string.
 */
public function __construct($dataset = false) {
	if ($dataset) {
		$this->addRows($dataset);
		return $this->write();
	}
}

/**
 * Add an element to the array.
 * @param string $element
 */
private function addElement($element) {
	// do escaping of escape char, enclosedchar and separator
	// would regex be quicker???
	if ($this->escape) {
		str_replace($this->escape, $this->escape . $this->escape, $element);
	}
	if ($this->enclosedby) {
		str_replace($this->enclosedby, $this->escape . $this->enclosedby, $element);
	}
	if ($this->separator) {
		str_replace($this->separator, $this->escape . $this->separator, $element);
	}
	// last separator is removed by endline if needed
	$this->buffer .= $this->enclosedby . $element . $this->enclosedby . $this->separator;
}

/**
 * Add headers to the CSV.
 * @param $headers array Header titles from left to right
 * @throws DStructGeneralException
 */
public function addHeaders($headers) {
	if (!is_array($headers)) {throw new DStructGeneralException('CSVOutput::addHeaders() - $headers parameter must be an array');}
	if ($this->buffer) {throw new DStructGeneralException('CSVOutput::addHeaders() - There is already data in the buffer. Headers must be added first');}
	$this->headercount = count($headers);
	foreach ($headers as $header) {
		$this->addElement($header);
	}
	$this->endLine();
}

/**
 * Add a row to the table.
 * 
 * The array will be added as a row to the end of the current data.
 * Method does not check that the array has the correct
 * number of items. This is expected behaviour.
 * @param array $arraydata Flat array of data.
 * @throws DStructGeneralException
 */
public function addRow($arraydata) {
	if (!is_array($arraydata)) {throw new DStructGeneralException('CSVOutput::addRow() - Row data must be an array');}
	foreach ($arraydata as $element) {
		$this->addElement($element);
	}
	$this->endLine();
}


/**
 * Add multiple rows of data.
 * 
 * Expects a multi-dimensional array of data to output as a csv.
 * @param array $data Multi-dimensional array of data.
 * @throws DStructGeneralException
 */
public function addRows($data) {
	if (!is_array($data)) {throw new DStructGeneralException('CSVOutput::addRows() - Rows data must be an array');}
	foreach ($data as $row) {
		if (!is_array($row)) {throw new DStructGeneralException('CSVOutput::addRows() - Row data must be an array');}
		$this->addRow($row);
	}
}

/**
 *Clear the object.
 *
 *Removes all data, counters and header info.
 */
public function clear() {
	$this->headers = null;
	$this->buffer = '';
}

/**
 * End a line of the CSV.
 */
private function endLine() {
	if (substr($this->buffer, strlen($this->buffer) - 1) == $this->separator) {
		$this->buffer = substr($this->buffer, 0, strlen($this->buffer) - 1);
	}
	$this->buffer .= $this->line;
}

/**
 * Get the number of headers set.
 * @return integer
 */
public function getHeaderCount() {
	return $this->headercount;
}

/**
 * Set the character to enclose fields with.
 * 
 * Default is nothing - fields will not be enclosed. Common setting would
 * be double quotes.
 * Must be set before any data or headers are added.
 * @param string $by
 * @throws DStructGeneralException
 */
public function setEnclosedBy($by) {
	if ($this->buffer) {throw new DStructGeneralException('CSVOutput::setEnclosedBy() - Must be set before sending any data');}
	$this->enclosedby = $by;
}

/**
 * Set the character to escape fields with.
 * 
 * Default is \ (backslash).
 * Must be set before any data or headers are added.
 * @param string $char
 * @throws DStructGeneralException
 */
public function setEscapeCharacter($char) {
	if ($this->buffer) {throw new DStructGeneralException('CSVOutput::setEnclosedBy() - Must be set before sending any data');}
	$this->escape = $char;
}

/**
 * Set the line ending.
 * 
 * Default is windows style \r\n. You must set the string using
 * double quotes! e.g.
 * <code>
 * $csvout->setLineEnding("\n"); // works!
 * $csvout->setLineEnding('\n'); // doesn't work!
 * </code>
 * @param string $ending
 * @throws DStructGeneralException
 */
public function setLineEnding($ending) {
	if ($this->buffer) {throw new DStructGeneralException('CSVOutput::setLineEnding() - Must be set before sending any data');}
	$this->line = $ending;
}

/**
 * Set the field separator.
 * 
 * Default is a comma.
 * Must be set before any headers or data.
 * @param string $sep
 * @throws DStructGeneralException
 */
public function setSeparator($sep) {
	if ($this->buffer) {throw new DStructGeneralException('CSVOutput::setSeparator() - Must be set before sending any data');}
	$this->separator = $sep;
}

/**
 * Output the CSV string.
 * 
 * Can either stream to browser as csv, as a download, output a string
 * for use in the script, or save as a local file.
 * Note that the browser and download options can only be used if their
 * has been no output from the script yet as they send headers.
 * @param string $name If streaming, downloading or saving can set name. Will default to export.csv if not given.
 * @param string $dest Output destination. See notes and class constants.
 * @throws DStructGeneralException
 * @return string
 */
public function write($name='', $dest= CSVOutput::EXPORT_STRING) {
	$dest=strtoupper($dest);
	if($dest=='')
	{
		if($name=='')
		{
			$name='export.csv';
			$dest='I';
		}
		else
			$dest='F';
	}
	
	switch($dest)
	{
		case 'I':
			//Send to standard output
			if(ob_get_length())
				throw new DStructGeneralException('Some data has already been output, can\'t send CSV file');
			if(php_sapi_name()!='cli')
			{
				//We send to a browser
				header('Content-Type: text/csv');
				if(headers_sent())
					throw new DStructGeneralException('Some data has already been output, can\'t send CSV file');
				header('Content-Length: '.strlen($this->buffer));
				header('Content-Disposition: inline; filename="'.$name.'"');
				header('Cache-Control: private, max-age=0, must-revalidate');
				header('Pragma: public');
				ini_set('zlib.output_compression','0');
			}
			echo $this->buffer;
			break;
		case 'D':
			//Download file
			if(ob_get_length())
				throw new DStructGeneralException('Some data has already been output, can\'t send CSV file');
			header('Content-Type: application/x-download');
			if(headers_sent())
				throw new DStructGeneralException('Some data has already been output, can\'t send CSV file');
			header('Content-Length: '.strlen($this->buffer));
			header('Content-Disposition: attachment; filename="'.$name.'"');
			header('Cache-Control: private, max-age=0, must-revalidate');
			header('Pragma: public');
			ini_set('zlib.output_compression','0');
			echo $this->buffer;
			break;
		case 'F':
			//Save to local file
			$f=fopen($name,'wb');
			if(!$f)
				throw new DStructGeneralException('Unable to create output file: '.$name);
			fwrite($f,$this->buffer,strlen($this->buffer));
			fclose($f);
			break;
		case 'S':
			//Return as a string
			return $this->buffer;
		default:
			throw new DStructGeneralException('Incorrect output destination: '.$dest);
	}
	return '';
}

}
?>