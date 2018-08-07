<?php
/**
 * Generate class
 */
/**
 * Static methods which 'generate'.
 * @package dstruct_common
 */
class Generate {
    
    /**
     * Hexadecimal characters
     * @var integer
     */
    const CHARACTERS_HEX = 1;
    
    /**
     * Alpha-numeric characters
     * @var integer
     */
    const CHARACTERS_ALPHA_NUMERIC = 2;
    
    /**
     * Lower case.
     * @var integer
     */
    const CASE_LOWER = 1;
    
    /**
     * Upper case.
     * @var integer
     */
    const CASE_UPPER = 2;
    
    /**
     * Mixed case.
     * @var integer
     */
    const CASE_MIXED = 3;
    
    /**
     * Exclude from passwords generated.
     * @var string
     * @see Generate::password()
     */
    const PASSWORD_EXCLUDE = '0 o O 1 l | 5 S';
    
    const LOG_FILE = 0x1; // can only use binary easily here after PHP5.4
    const LOG_ERROR_LOG = 0x2;
    const LOG_ECHO = 0x4;
    
    /**
     * Class constructor.
     * @throws DStructGeneralException
     */
    private function __construct() {
        throw new DStructGeneralException('Generate::__construct() - Trying to instantiate a class which has only static methods');
    }
    
    /**
     * Encrypt text - DISABLED
     *
     * Suitable for encryption. Only text which needs to be decrypted should be
     * passed through this method.
     * Passwords should be one-way hashed if possible, not encrypted.
     * @param string $text
     * @return string
     * @link http://blog.justin.kelly.org.au/simple-mcrypt-encrypt-decrypt-functions-for-p/
     * @see Generate::decrypt()
     * @todo Check return / errors with non-text input
     */
    public static function encrypt($text) {
        throw new Exception("MCRYPT functions not available");
    }
    
    /**
     * Decrypt text - DISABLED
     *
     * @param string $text
     * @return string
     * @link http://blog.justin.kelly.org.au/simple-mcrypt-encrypt-decrypt-functions-for-p/
     * @see Generate::encrypt()
     * @todo Check return / errors with non-text input
     */
    public static function decrypt($text) {
        throw new Exception("MCRYPT functions disabled");
    }
    
    /**
     * Generate array of ASCII from string.
     * @param string $str
     * @return array
     */
    public static function toASCIIArray($str) {
        $output = array();
        $strlen = strlen($str);
        for ($c = 0; $c < $strlen; $c++) {
            $output[] = ord($str[$c]);
        }
        return $output;
    }
    
    /**
     * Generate a string of ASCII HTML entities.
     * @param string $str
     * @return string
     */
    public static function toASCIIEntities($str) {
        $output = '';
        if ($str) {
            $output = self::toASCIIArray($str);
            foreach ($output as $element) {
                $element .= ';';
            }
            $output = implode('&#', $output);
            $output = '&#' . $output;
        }
        return $output;
    }
    
    /**
     * Generate an anchor mailto: which is obscured from robots and requires JS to display properly.
     *
     * If JS is not available,, you can encode the email address with {@link toASCIIEntities()}. The
     * document.write() line from the out put MUST NOT be split over multiple lines when output to the
     * browser.
     * @param string $email Is encoded.
     * @param string $linktext The text which will show in the browser. Is encoded.
     * @param string $anchortitle Text for the HTML 'a' element title. Is encoded.
     * @param string $class Any class(es) you want on the HTML 'a' element
     */
    public static function mailTo($email, $linktext='Send Email', $anchortitle='Send Email', $class='') {
        $email = self::ASCIIArray($email);
        $email = implode(',', $email);
        
        $linktext = self::ASCIIArray($linktext);
        $linktext = implode(',', $linktext);
        
        $anchortitle = self::ASCIIArray($anchortitle);
        $anchortitle = implode(',', $anchortitle);
        
        // note that the document.write has to be kept on one line
        return
        "<script type='text/javascript'>
		<!--
		document.write(\"<a title='\" + String.fromCharCode($anchortitle) + \"' class='$class' href='mailto:\" + String.fromCharCode($email) + \"'>\" + String.fromCharCode($linktext) + \"<\/a>\");
		-->
		</script>";
    }
    
    /**
     *Generate a random string suitable for passwords.
     *@param integer $charcount Number of characters required in output
     *@param integer $characters Hex characters or Alpha-Numeric output
     *@param integer $case Upper, Lower or Mixed case
     *@param string $exclude Characters not allowed in password
     *@param string $include Characters allowed in password which are not otherwise included
     *@return string
     */
    public static function password($charcount = 8, $characters = Generate::CHARACTERS_ALPHA_NUMERIC, $case = Generate::CASE_MIXED, $exclude = Generate::PASSWORD_EXCLUDE, $include = false) {
        $exclude = str_replace(' ', '', $exclude);
        $include = str_replace(' ', '', $include);
        // add just hex chars to array
        if ($characters == Generate::CHARACTERS_HEX) {
            $chars = array(1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f'); // could use a counter, but hopefully this is faster!?
        } else { // characters are numeric and some alpha
            $chars = array(1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
            
            // if we have a mixed case, we want to add capitals.
            // if not mixed then we don't because that will double the frequency of the alphas vs numerics
            // we'll use a loop this time as painful to type it all out! $c is the ASCII number
            if ($case == Generate::CASE_MIXED) {
                for ($c = 65; $c <= 90; $c++) {
                    $chars[] = chr($c);
                }
            }
        }
        
        // we do includes before excludes so that the excludes take priority
        if ($include) {
            $includechars = str_split($include);
            
            foreach ($includechars as $includechar) {
                if (!in_array($includechar, $chars)) {
                    $chars[] = $includechar;
                }
            }
        }
        
        // we remove any existing excluded characters from the array
        if ($exclude) {
            $excludechars = str_split($exclude);
            
            foreach ($excludechars as $excludechar) {
                if ($existingchar = array_search($excludechar, $chars)) {
                    unset($chars[$existingchar]);
                }
            }
        }
        
        if ($exclude || $include) {$chars = array_values($chars);} // need to re-index the array
        
        $arraytop = count($chars) - 1;
        $output = '';
        
        // generate the string
        for ($c = 1; $c <= $charcount; $c++) {
            $output .= $chars[rand(0, $arraytop)];
        }
        
        if ($case == Generate::CASE_UPPER) {$output = strtoupper($output);}
        return $output;
    }
    
    /**
     * Make a Post to a URL without CURL.
     * @param string $url The target page
     * @param array $data e.g. 'id' => 3, 'name' => 'myname'
     * @param string $optional_headers Additional HTTP headers that you would like to send in your request.
     * @link http://wezfurlong.org/blog/2006/nov/http-post-from-php-without-curl/
     */
    public static function postRequest($url, $data, $optional_headers = null) {
        $params = array('http' => array(
            'method' => 'POST',
            'content' => $data
        ));
        if ($optional_headers !== null) {
            $params['http']['header'] = $optional_headers;
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            throw new DStructGeneralException("Problem with $url, $php_errormsg");
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            throw new DStructGeneralException("Generate::postRequest() - Problem reading data from $url, $php_errormsg");
        }
        return $response;
    }
    
    /**
     * POST data and grab response.
     *
     * Origin of this method?
     * @param string $host Host
     * @param string $usepath Path of URL
     * @param string $postdata POST data
     * @return string
     */
    public static function pullPage( $host, $usepath, $postdata = "" ) {
        $fp = fsockopen( $host, 80, $errno, $errstr, 60 );
        
        #check that the socket has been opened successfully
        if( !$fp ) {
            print "$errstr ($errno)<br>\n";
        } else {
            #write the data to the encryption cgi
            fputs( $fp, "POST $usepath HTTP/1.0\n");
            $strlength = strlen( $postdata );
            fputs( $fp, "Content-type: application/x-www-form-urlencoded\n" );
            fputs( $fp, "Content-length: ".$strlength."\n\n" );
            fputs( $fp, $postdata."\n\n" );
            
            #clear the response data
            $output = "";
            
            #read the response from the remote cgi
            #while content exists, keep retrieving document in 1K chunks
            while( !feof( $fp ) ) {
                $output .= fgets( $fp, 1024);
            }
            
            fclose( $fp);
        }
        return $output;
    }
    
    /**
     * Truncates text by looking for \n followed by space.
     *
     * Useful for when inserting a summary of a longer bit of text somewhere.
     * <var>$forcelinebreak</var> makes a break at the first \n (newline) regardless of whether line is short.
     * <var>$oversuffix</var> is appended if the text is too long. Typically, it might be used to append '...'
     * to the end of the output.
     * If the text is shorter than <var>$maxchars</var> then the original text is returned.
     * Should be UTF-8 safe.
     * @param string $text Text to truncate
     * @param integer $maxchars Maximum characters allowed in output
     * @param boolean $forcelinebreak Break at first \n
     * @param string $oversuffix Suffixed to the output if it was truncated.
     * @return string
     */
    public static function truncatedText($text, $maxchars, $forcelinebreak = true, $oversuffix = false) {
        if ($forcelinebreak) { // get any part BEFORE a \n
            //$text = $title = stristr($text, "\n", true);
            if (iconv_strpos($text, "\n")) {$text = iconv_substr($text, 0, iconv_strpos($text, "\n") -1);}
        }
        //echo 'here'.$text;
        if (iconv_strlen($text) <= $maxchars) {return $text;}
        $text = iconv_substr($text, 0, $maxchars); // trim to the max length
        while (iconv_strlen($text . $oversuffix) >= $maxchars) {
            $text = iconv_substr($text, 0, iconv_strrpos($text, ' '));  // then trim last word (or part of)
        }
        return $text . $oversuffix;
    }
    
    /**
     * Log to file / error_log / echo
     *
     * @param mixed $s String or array to log
     * @param int $flags as hex. See class constants. Defines logging options
     * @param string $target Name of log. Default is the same as passing __FILE__. Use false to
     * @return boolean True on success
     */
    public static function log($s, $flags = self::LOG_ECHO, $target = false) {
        static $tested = array();
        if (is_array($s)) {
            $s = print_r($s,true);
        }
        if (is_object($s) && method_exists($s, '__toString')) {
            $s = $s->__toString();
        }
        $dir = Prefs::gi()->get('logging_path');
        $date = DB::dateStr(time());
        if ($flags & self::LOG_ERROR_LOG) {
            error_log($s);
        }
        if ($flags & self::LOG_ECHO) {
            echo "\n".$s;
        }
        $s = "\n$date | $s";
        if ($flags & self::LOG_FILE) {
            $scriptName = Prefs::APP_NAME;
            if ($target) {
                $scriptName = basename($target, '.php');
            }
            if (!isset($tested['$target'])) {
                if (!file_exists($dir)) {
                    $s =  "\n$date | $scriptName | Unable to find directory to write to. Message: $s";
                    echo $s;
                    error_log($s);
                    return false;
                }
                $tested = true;
            }
            $rtn = file_put_contents("$dir/$scriptName.log", $s, FILE_APPEND);
            if ($rtn === false) {
                return false;
            }
        }
        return true;
    }
    
}
?>