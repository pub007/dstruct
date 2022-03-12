<?php
/**
 * HMenu class.
 */
/**
 * Dynamically create horizantal CSS menus.
 * 
 * Based on ?superfish? type CSS menus (see link), this class
 * allows dynamic generation of CSS and HTML to produce horizantal
 * menus at runtime.<br />
 * This does not actually product Superfish functionality, but the code
 * should be compatible with adding Superfish.<br />
 * It is advisable to cache the menu if possible, as there is
 * considerable overhead to running this every time the script is
 * accessed and on a busy site with many buttons and layers could
 * have a significant detrimental effect on performance.
 * Simple example adding a three button menu where second button has a sub-button:
 * <code>
 * $hmenu = new HMenu;
 * $hmenu->addButton('Home', '/');
 * $hmenu->addButton('Products' '');
 * $parentid = $hmenu->getLastID();
 * $hmenu->addButton('Product One', '/prod1/', 'Product One', 2, $parentid);
 * $hmenu->addButton('Product Two', '/prod2/', 'Product Two', 2, $parentid);
 * $hmenu->addButton('Contact Us', '/contact/');
 * 
 * // then use...
 * // $hmenu->generateCSS() at the end of your <head> section
 * // and $hmenu->generateHTML() where the menu would go
 * </code>
 * @link http://users.tpg.com.au/j_birch/plugins/superfish/
 * @see VMenu
 * @package dstruct_presentation
 * @todo This was written some years ago and probably needs updating to fit more recent menu technologies
 */
class HMenu {

/**
 * Level 1 buttons
 * @var array
 */
private $level1 = array();

/**
 * Level 2 buttons.
 * @var array
 */
private $level2 = array();

/**
 * Level 3 array.
 * @var array
 */
private $level3 = array();

/**
 * ID of last button
 * @var integer
 */
private $id;

/**
 * Directory where javascript is stored.
 * @var string
 */
private $js_dir;

/**
 * Directory where CSS is stored.
 * @var unknown
 */
private $css_dir;

/**
 * Directory where images are stored.
 * @var unknown
 */
private $img_dir;

/**
 * Menu fload
 * @var string
 */
private $menufloat = 'right';

/**
 * Menu width.
 * @var integer
 */
private $menuwidth = '600';

/**
 * Drop of menu
 * @var integer
 */
private $menudrop = '30';

/**
 * Background colour.
 * @var string
 */
private $bgcol = 'ffffff';

/**
 * Button width.
 * @var array
 */
private $buttonwidth = array('80', '80', '80');

/**
 * Button height.
 * @var array
 */
private $buttonheight = array('30', '30', '30');

/**
 * Menu margins.
 * @var array
 */
private $margin = array(20, 20, 20, 20);

/**
 * Font colour.
 * @var string
 */
private $fontcol = '000000';

/**
 * Font size.
 * @var float
 */
private $fontsize = 12;

/**
 * Font weight.
 * @var string
 */
private $fontweight = 'normal';

/**
 * Font is italic?
 * @var boolean
 */
private $fontitalic = false;

/**
 * Border widths.
 * @var array
 */
private $borderwidths = array();

/**
 * Border colours.
 * @var array
 */
private $bordercols = array('000000','000000','000000','000000');

/**
 * Background :hover colour.
 * @var string
 */
private $bghovercol = 'ffffff';

/**
 * Font :hover colour.
 * @var string
 */
private $fonthovercol = 'aa0000';

/**
 * Next button ID
 * @var integer
 */
private $nextbutton = 1;

/**
 * Set the javascript file directory.
 * @param string $js_dir
 */
public function setJSFileDirectory($js_dir){$this->js_dir = $js_dir;}

/**
 * Set CSS file directory.
 * @param string $css_dir
 */
public function setCSSFileDirectory($css_dir){$this->css_dir = $css_dir;}

/**
 * Set the image directory.
 * 
 * Expects to find a shadow.png
 * @param string $img_dir
 */
public function setImageFileDirectory($img_dir){$this->img_dir = $img_dir;}

/**
 * Set the float (left or right) of the menu.
 * @param string $menufloat
 */
public function setMenuFloat($menufloat){$this->menufloat = $menufloat;}

/**
 * Set the width of the menu.
 * @param integer $menuwidth In pixels
 */
public function setMenuWidth($menuwidth){$this->menuwidth = $menuwidth;}

/**
 * Menu drop in pixels
 * @param integer $menudrop
 */
public function setMenuDrop($menudrop){$this->menudrop = $menudrop;}

/**
 * Background colour.
 * @param string $bgcol Hex colour code e.g. ec4a8f
 */
public function setBGCol($bgcol) {$this->bgcol = $bgcol;}

/**
 * Button width.
 * @param integer $buttonwidth In pixels
 */
public function setButtonWidth($buttonwidth) {$this->buttonwidth = explode(', ', $buttonwidth);}

/**
 * Button height.
 * @param integer $buttonheight
 */
public function setButtonHeight($buttonheight){$this->buttonheight = explode(', ', $buttonheight);}

/**
 * Menu margin
 * @param integer $margin In pixels
 */
public function setMargin($margin){$this->margin = explode(',', $margin);}

/**
 * Default font colour.
 * @param string $fontcol Hex colour code e.g. ec4a8f
 */
public function setFontCol($fontcol) {$this->fontcol = $fontcol;}

/**
 * Font size.
 * @param float $fontsize
 */
public function setFontSize($fontsize) {$this->fontsize = $fontsize;}

/**
 * Font weight.
 * @param string $fontweight e.g. bolder
 */
public function setFontWeight($fontweight) {$this->fontweight = $fontweight;}

/**
 * Font style is italic?
 * @param boolean $fontitalic
 */
public function setFontItalic($fontitalic) {$this->fontitalic = $fontitalic;}

/**
 * Border widths.
 * 
 * Array with 4 integer elements for top, right, bottom and left.
 * @param array $borderwidths
 */
public function setBorderWidths($borderwidths) {$this->borderwidths = explode(', ',$borderwidths);}

/**
 * Border colours.
 * 
 * Array with 4 elements for top, right, bottom and left. Elements should be
 * hex colour codes e.g. ec4a8f
 * @param array $bordercols
 */
public function setBorderCols($bordercols) {$this->bordercols = explode(', ', $bordercols);}

/**
 * Background :hover colour.
 * @param string. $bghovercol Hex colour code e.g. ec4a8f
 */
public function setBGHoverCol($bghovercol) {$this->bghovercol = $bghovercol;}

/**
 * Font :hover colour.
 * @param string $fonthovercol Hex colour code e.g. ec4a8f
 */
public function setFontHoverCol($fonthovercol) {$this->fonthovercol = $fonthovercol;}

/**
 * Get the next button ID.
 * 
 * Useful when using {@link addButton()}
 * @return integer.
 */
public function getNextButton() {return $this->nextbutton;}

/**
 * Add a button to the menu.
 * 
 * See the class description for a simple example which includes adding
 * buttons.
 * @param string $text Button text
 * @param string $url Link for anchor
 * @param string $title Text for title of anchor
 * @param number $level Level of the button (1, 2 or 3)
 * @param number $parentid ID of the parent button.
 * @param string $onclick Any JS for the onlick of the anchor
 */
public function addButton($text, $url = '', $title = '', $level = 1, $parentid = 0, $onclick = '') {
	$button = '';
	static $c = 0;
	static $d = 0;
	static $e = 0;
	$id = $this->nextbutton;
	
	$button .= "<li><a href='";
	
	$button .= ($url)? "$url'" : "#'";
	if($onclick){
		$button .= " onclick='$onclick'";
	}
	if($title){
		$button .= " title='$title'";
	}
	$button .= ">$text</a>";
	if($level == 3){
		$button .= '</li>';
	}
	$button .= "\n";

	$this->nextbutton++;
	
	switch($level){
		case 1:
			$this->level1[$c][0] = $id;
			$this->level1[$c][1] = $level;
			$this->level1[$c][2] = $parentid;
			$this->level1[$c][3] = $button;
			$this->id = $id;
			$c++;
			break;
		case 2:
			$this->level2[$d][0] = $id;
			$this->level2[$d][1] = $level;
			$this->level2[$d][2] = $parentid;
			$this->level2[$d][3] = $button;
			$this->id = $id;
			$d++;
			break;
		case 3:
			$this->level3[$e][0] = $id;
			$this->level3[$e][1] = $level;
			$this->level3[$e][2] = $parentid;
			$this->level3[$e][3] = $button;
			$this->id = $id;
			$e++;
			break;
	}
}

/**
 * Get ID of last button
 * @return integer
 */
public function getLastID(){
	return $this->id;
}

/**
 * Generate the HTML portion of the menu.
 * @return string
 */
public function generateHTML() {
	$c = $d = $e = 0;
	$output = '';
	$level1haschildren = $level2haschildren = false;
	$level1firstchild = $level2firstchild = true;
	$level1count = count($this->level1);
	$level2count = count($this->level2);
	$level3count = count($this->level3);
	
	$output .= "<div id='menu'><ul class='sf-menu'>\n";
	
	for($c =0 ; $c < $level1count; $c++){
		$output .= $this->level1[$c][3];
		
		if($this->multiarray_search($this->level2, 2, $this->level1[$c][0]) !== false){
			$output .= "<ul>\n";
			for($d = 0; $d < $level2count; $d++){
				if($this->level2[$d][2] == $this->level1[$c][0]){
					if($level1firstchild){
						$this->level2[$d][3] = iconv_substr($this->level2[$d][3], 0, 6) . iconv_substr($this->level2[$d][3], 6);
						$level1firstchild = false;
					}
					$output .= $this->level2[$d][3];
					if($this->multiarray_search($this->level3, 2, $this->level2[$d][0]) !== false){
						$output .= "<ul>\n";
						for($e = 0; $e < $level3count; $e++){
							if($this->level3[$e][2] == $this->level2[$d][0]){
								if($level2firstchild) {
									$this->level3[$e][3] = iconv_substr($this->level3[$e][3], 0, 6) . iconv_substr($this->level3[$e][3], 6);
									$level2firstchild = false;
								}
								$output .= $this->level3[$e][3];
							}
						}
						$output .= "</ul>\n";
						$level2haschildren = false;
						$level2firstchild = true;
					}
					$output .= "</li>\n";
				}
			}
			$output .= "</ul>\n";
			$level1firstchild = true;
		}
		$output .= "</li>\n";
		$level1haschildren = false;
	}
	$output .= "</ul></li></ul></div>\n";
	return $output;
}

/**
 * Generate the CSS portion of the menu.
 * 
 * Creates a style tag with CSS for the menu.<br />
 * If you have put in CSS and JS directories, then script tags will also be output with links to
 * <code>
 * /[jsdir]/menu.js
 * /[jsdir]/supersubs.js
 * </code>
 * If you have set and image directory then it will try to use a dropshadow with the file
 * <code>
 * /[imgdir]/shadow.png
 * </code>
 * @return string
 */
public function generateCSS() {
	$output = '';
	if($this->js_dir){
		$output .= "
			<script language='javascript' type='text/javascript' src='/" . $this->js_dir . "/menu.js'></script>
			<script language='javascript' type='text/javascript' src='/" . $this->js_dir . "/supersubs.js'></script>";
		}
	if($this->css_dir){
		$output .= "
		<link type='text/css' href='" . $this->css_dir . "/menu.css' rel='stylesheet' />\n";
	}
	$output .= "<style type='text/css'>
				\n/********** MENU ***********/
				#menu {width:" . $this->menuwidth . "px;";
					if($this->menufloat){
						$output .= "float:" . $this->menufloat . ";";}
	$output .= "}
				.sf-menu li:hover ul,
				.sf-menu li.sfHover ul {
					top:			" . $this->menudrop . "px; 
				
				}
				.sf-menu a{ 
					color:			#" . $this->fontcol . ";
					text-decoration:none;
				}
				ul.sf-menu li li:hover ul,
				ul.sf-menu li li.sfHover ul {
					border-top:		" . $this->borderwidths[0] . "px solid #" . $this->bordercols[0] . ";
					left:			" . $this->buttonwidth[1] . "px;
				}
				.sf-menu li {
					background:		#" . $this->bgcol . ";
					width:			" . $this->buttonwidth[0] . "px;
					height:			" . $this->buttonheight[0] . "px;
					font-size:      " . $this->fontsize . "px;
					font-weight:    " . $this->fontweight . ";";
					if($this->fontitalic){$output .= "font-style:italic;";}
	$output .= "	margin-top:			" . $this->margin[0] . "px;
					margin-right:		" . $this->margin[1] . "px;
					margin-bottom:		" . $this->margin[2] . "px;
					margin-left:		" . $this->margin[3] . "px;
					border-top:		" . $this->borderwidths[0] . "px solid #" . $this->bordercols[0] . ";
					border-right:	" . $this->borderwidths[1] . "px solid #" . $this->bordercols[1] . ";
					border-left:	" . $this->borderwidths[2] . "px solid #" . $this->bordercols[2] . ";
					border-bottom:	" . $this->borderwidths[3] . "px solid #" . $this->bordercols[3] . ";	
				}
				.sf-menu li li {
					background:		#" . $this->bgcol . ";
					width:			" . $this->buttonwidth[1] . "px;
					height:			" . $this->buttonheight[1] . "px;
					font-size:      " . $this->fontsize . "px;
					font-weight:    " . $this->fontweight . ";
					margin:			0px 0px 0px 0px;
					border-top:none;";
					if($this->fontitalic){$output .= "font-style:italic;";}
	$output .= "}
				.sf-menu li li li {
					background:		#" . $this->bgcol . ";
					width:			" . $this->buttonwidth[2] . "px;
					height:			" . $this->buttonheight[2] . "px;
					font-size:      " . $this->fontsize . "px;
					font-weight:    " . $this->fontweight . ";
					margin:			0px 0px 0px 0px;";
					if($this->fontitalic){$output .= "font-style:italic;";}
	$output .= "}
				.sf-menu a:hover{
					color:			#" . $this->fonthovercol . ";
				}	
				.sf-menu li:hover, .sf-menu li.sfHover,
				.sf-menu a:focus, .sf-menu a:active {
					background:		#" . $this->bghovercol . ";
				}";
				if($this->img_dir){
					$output .= ".sf-shadow ul {
									background:	   url('" . $this->img_dir . "/shadow.png') no-repeat bottom right;
								}
								.sf-shadow ul {
									padding: 0 8px 9px 0;
									-moz-border-radius-bottomleft: 17px;
									-moz-border-radius-topright: 17px;
									-webkit-border-top-right-radius: 17px;
									-webkit-border-bottom-left-radius: 17px;
								}
								.sf-shadow ul.sf-shadow-off {
									background: transparent;
								}";
				}
				$output .= "
				/***** Menu End ******/
				\n</style>";
	return $output;
}

/**
 * Search a multi-dimensional array.
 * @param array $arr Array to search
 * @param string $dimension Dimension to search
 * @param string $needle String to search for
 * @return string|false Key if found or false
 * @author ?
 */
private function multiarray_search($arr, $dimension, $needle){
	while(isset($arr[key($arr)])){
		if($arr[key($arr)][$dimension] == $needle){
			return key($arr);
		}
		next($arr);
	}
	return false;
}
}
?>