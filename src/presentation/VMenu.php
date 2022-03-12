<?php
/**
 * VMenu class
 */
/**
 * Dynamically create vertical CSS menus.
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
 * $vmenu = new VMenu;
 * $vmenu->addButton('Home', '/');
 * $parentid = $vmenu->getNextButton(); // next button will be a parent
 * $vmenu->addButton('Products' '');
 * $vmenu->addButton('Product One', '/prod1/', 'Product One', 2, $parentid);
 * $vmenu->addButton('Product Two', '/prod2/', 'Product Two', 2, $parentid);
 * $vmenu->addButton('Contact Us', '/contact/');
 * 
 * // then use...
 * // $vmenu->generateCSS() at the end of your <head> section
 * // optionally use $vmenu->generateConditionalStatements() 
 * // and $vmenu->generateHTML() where the menu would go
 * </code>
 * @link http://users.tpg.com.au/j_birch/plugins/superfish/
 * @see HMenu
 * @package dstruct_presentation
 * @todo This was written some years ago and probably needs updating to fit more recent menu technologies
 * @todo Add method to get last button id rather than next button as it is counter-intuitive.
 */
class VMenu {
	
/**
 * Level 1 buttons.
 *@var array
 */
private $level1 = array();

/**
 * Level 2 buttons.
 *@var array
 */
private $level2 = array();

/**
 * Level 3 buttons.
 *@var array
 */
private $level3 = array();

/**
 * Button border widths.
 *@var array
 */
private $borderwidths = array(1,1,1,1);

/**
 * Border colours.
 *@var array
 */
private $bordercols = array('CCCCCC','888888','555555','BBBBBB');

/**
 * Button padding.
 *@var array
 */
private $padding = array(2,3,2,3);

/**
 * Wrap in div with id=menu?
 *@var boolean
 */
private $includediv = true;

/**
 * Include style tags with CSS?
 *@var boolean
 */
private $includestyletags = true;

/**
 * Background colour.
 *@var string
 */
private $bgcol = 'efefef';

/**
 * Background :hover colour.
 *@var string
 */
private $bghovercol = 'fff';

/**
 * Top border of menu.
 *@var array
 */
private $topborder;

/**
 * Font colour.
 *@var string
 */
private $fontcol = '000';

/**
 * Font :hover colour
 *@var string
 */
private $fonthovercol = 'a00';

/**
 * Font size.
 *@var float
 */
private $fontsize = 12;

/**
 * Font weight.
 *@var string
 */
private $fontweight = 'normal';

/**
 * Is font italic?
 *@var boolean
 */
private $fontitalic = false;

/**
 * Menu width.
 *@var integer
 */
private $menuwidth = 12;

/**
 * ID of the next button to be added.
 *@var integer
 */
private $nextbutton = 1;

/**
 * Wrap HTML output in a div with id of 'menu'>?
 * @param boolean $includediv
 */
public function setIncludeDiv($includediv) {$this->includediv = $includediv;}

/**
 * Include style tags around the CSS output?
 * 
 * If you already have a script with CSS in your page, you may want to exclude the
 * script tags and just include the CSS in the existing block.
 * @param boolean $includestyletags
 */
public function setIncludeStyleTags($includestyletags) {$this->includestyletags = $includestyletags;}

/**
 * Set background colour.
 * @param string $bgcol Hex colour code e.g. ec4a8f
 */
public function setBGCol($bgcol) {$this->bgcol = $bgcol;}

/**
 * Set background :hover colour.
 * @param string $bghovercol Hex colour code e.g. ec4a8f
 */
public function setBGHoverCol($bghovercol) {$this->bghovercol = $bghovercol;}

/**
 * Font colour.
 * @param string $fontcol Hex colour code e.g. ec4a8f
 */
public function setFontCol($fontcol) {$this->fontcol = $fontcol;}

/**
 * Font :hover colour.
 * @param string $fonthovercol Hex colour code e.g. ec4a8f
 */
public function setFontHoverCol($fonthovercol) {$this->fonthovercol = $fonthovercol;}

/**
 * Font is italic?
 * @param boolean $fontitalic
 */
public function setFontItalic($fontitalic) {$this->fontitalic = $fontitalic;}

/**
 * Border colours.
 * 
 * Array with 4 elements for top, right, bottom and left. Elements should be
 * hex colour codes e.g. ec4a8f
 * @param array $bordercols
 */
public function setBorderCols($bordercols) {$this->bordercols = explode(',', $bordercols);}

/**
 * Border widths.
 *
 * Array with 4 integer elements for top, right, bottom and left.
 * @param array $borderwidths
 */
public function setBorderWidths($borderwidths) {$this->borderwidths = explode(',',$borderwidths);}

/**
 * Set button padding
 * @param integer $padding In pixels
 */
public function setPadding($padding) {$this->padding = explode(',',$padding);}

/**
 * Set top border on buttons added as a 'topbutton'.
 * 
 * Array with two elements to set width in px and colour in hex.
 * @param array $topborder
 * @todo confirm usage of this method.
 * @see addButton()
 */
public function setTopBorder($topborder) {$this->topborder = $topborder;}

/**
 * Set menu with in px.
 * @param integer $menuwidth
 */
public function setMenuWidth($menuwidth) {$this->menuwidth = $menuwidth;}

/**
 * Set font size.
 * @param float $fontsize
 */
public function setFontSize($fontsize) {$this->fontsize = $fontsize;}

/**
 * Set font weight.
 * 
 * e.g. 'bolder'
 * @param strong $fontweight
 */
public function setFontWeight($fontweight) {$this->fontweight = $fontweight;}

/**
 * Will the HTML output include a div with id='menu'?
 * @return boolean
 */
public function getIncludeDiv() {return $this->includediv;}

/**
 *Will the CSS output be wrapped with the <style> tags.
 *@return boolean
 */
public function getIncludeStyleTags() {return $this->includestyletags;}

/**
 *Button background colour.
 *@return string
 */
public function getBGCol() {return $this->bgcol;}

/**
 *Button :hover colour.
 *@return string
 */
public function getBGHover() {return $this->bghover;}

/**
 *Font colour
 *@return string
 */
public function getFontCol() {return $this->fontcol;}

/**
 *Font :hover colour
 *@return string
 */
public function getFontHoverCol() {return $this->fonthovercol;}

/**
 *Set font as italic
 *@return boolean
 */
public function getFontItalic() {return $this->fontitalic;}

/**
 *Button border colors
 *@see VMenu::setBorderCols()
 *@return array
 */
public function getBorderCols() {return $this->bordercols;}

/**
 *Border widths in pixels.
 *@see setBorderWidths()
 *@return array
 */
public function getBorderWidths() {return $this->borderwidths;}

/**
 * Get button padding.
 * @return array
 */
public function getPadding() {return $this->padding;}

/**
 * Get top border of first button.
 * @return array
 * @see setTopBorder()
 */
public function getTopBorder() {return $this->topborder;}

/**
 * Get menu width
 * @return integer
 */
public function getMenuWidth() {return $this->menuwidth;}

/**
 * Get font size.
 * @return integer
 */
public function getFontSize() {return $this->fontsize;}

/**
 * Get font weight.
 * @return string
 */
public function getFontWeight() {return $this->fontweight;}

/**
 * Get next button.
 * @return integer
 */
public function getNextButton() {return $this->nextbutton;}

/**
 *Add a button to the menu
 *If using javascript, you may need to use # for <var>$url</var>.
 *If the button is <var>$level</var> 2 or 3, you will need to set the
 *<var>$parentid</var>.
 *Example:
 *<code>
 *<?php
 *$menu = new VMenu;
 *$menu->addButton('Button 1'); // add a button
 *$parentid = $menu->getNextButton(); // get the ID of the next button. This will be the parent button.
 *$menu->addButton('Button 2');
 *$menu->addButton('Button 2-2','','',2,$parentid); // add a sub-button
 *?>
 *</code>
 *
 *@param string $text Text for the button
 *@param string $url
 *@param string $title Set the title attribute of the anchor element
 *@param integer $level Level of the button in the structure - 1, 2 or 3
 *@param integer $parentid See above
 *@param boolean $topbutton If the button is the first of its group
 *@param string $onclick Insert into the onclick attribute of the anchor element
 */
public function addButton($text, $url = '', $title = '', $level = 1, $parentid = 0, $topbutton = false, $onclick = '') {
	$button = '';
	static $c = 0;
	static $d = 0;
	static $e = 0;
	// if no id provided, we just give it the next id
	$id = $this->nextbutton;
	
	// create the button
	// add topborder to first button
	// other topbuttons are added in VMenu::generateHTML()
	if ($id == 1 && $this->topborder) {
		$button .= "<li><a class='topnavbut' href='";
	} else {
		$button .= "<li><a href='";
	}
	
	$button .= ($url)? "$url'" : "#'";
	if ($onclick) {$button .= " onclick='$onclick'";}
	$button .= ($title)? " title='$title'>" : ">";
	$button .= "$text</a>";
	if ($level == 3) {$button .= '</li>';}
	$button .= "\n";
	
	// we need to record which button is next
	$this->nextbutton++;
	
	// add the stuff to an array for each level of button
	// change 0 based indexes to string indexes?
	switch ($level) {
		case 1:
			$this->level1[$c][0] = $id;
			$this->level1[$c][1] = $level;
			$this->level1[$c][2] = $parentid;
			$this->level1[$c][3] = $button;
			$c++;
			break;
		case 2:
			$this->level2[$d][0] = $id;
			$this->level2[$d][1] = $level;
			$this->level2[$d][2] = $parentid;
			$this->level2[$d][3] = $button;
			$d++;
			break;
		case 3:
			$this->level3[$e][0] = $id;
			$this->level3[$e][1] = $level;
			$this->level3[$e][2] = $parentid;
			$this->level3[$e][3] = $button;
			$e++;
			break;
	}
}

/**
 *Outputs the HTML required for the menu.
 *
 *echo this where your menu should be in the HTML
 *@return string
 */
public function generateHTML() {
	$c = $d = $e = 0;
	$output = '';
	$level1haschildren = $level2haschildren = false;
	$level1firstchild = $level2firstchild = true;
	$level1count = count($this->level1);
	$level2count = count($this->level2);
	$level3count = count($this->level3);
	
	if ($this->includediv) {$output .= "<div id='menu'>\n";}
	$output .= "<ul><li><ul>\n";
	
	for ($c =0 ; $c < $level1count; $c++) {
		// add the string for the button
		//echo "C:$c Output:$this->level1[$c][3] <br /><br />\n";
		$output .= $this->level1[$c][3];
		
		// check if any children for the button
		$level1haschildren = ($this->multiarray_search($this->level2, 2, $this->level1[$c][0]) !== false)? true : false;
		
		// add any level 1 children
		if ($level1haschildren) {
			$output .= "<ul>\n";
			// go through array looking for sub-buttons
			for ($d = 0; $d < $level2count; $d++) {
				// if we find a child...
				if ($this->level2[$d][2] == $this->level1[$c][0]) {
					// if it is a first child
					if ($level1firstchild) {
						// insert CSS class
						$this->level2[$d][3] = iconv_substr($this->level2[$d][3], 0, 6) . " class='topnavbut'" . iconv_substr($this->level2[$d][3], 6);
						$level1firstchild = false;
					}
					// add string for buttonn
					$output .= $this->level2[$d][3];
					// check if this level 2 button has children
					$level2haschildren = ($this->multiarray_search($this->level3, 2, $this->level2[$d][0]) !== false)? true : false;
					
					// if we find a child for this level2 button
					if ($level2haschildren) {
						$output .= "<ul>\n";
						// search all level3 buttons for the level2 children
						for ($e = 0; $e < $level3count; $e++) {
							// if it is a child
							if ($this->level3[$e][2] == $this->level2[$d][0]) {
								// if it is the first child of the level2 button
								if ($level2firstchild) {
									// insert the CSS class and set all following to NOT be first child
									$this->level3[$e][3] = iconv_substr($this->level3[$e][3], 0, 6) . " class='topnavbut'" . iconv_substr($this->level3[$e][3], 6);
									$level2firstchild = false;
								}
								
								$output .= $this->level3[$e][3];
							}
						}
						
						// close level 3 buttons for this level 2 button
						$output .= "</ul>\n";
						$level2haschildren = false;
						$level2firstchild = true;
						
					}
					
					// close level 2 button
					$output .= "</li>\n";
					
				}
			}
			
			// close level2 for this level1 button
			$output .= "</ul>\n";
			$level1firstchild = true;
			
		}
		
		// close level1
		$output .= "</li>\n";
		$level1haschildren = false;
	}
	
	// finalise
	$output .= "</ul></li></ul>\n";
	if ($this->includediv) {$output .= "</div>\n";}
	return $output;
	
}
/**
 *Outputs the CSS required for the menu.
 *
 *Echo this where you want the CSS on the page.
 *@return string
 *@see setIncludeStyleTags()
 */
public function generateCSS() {
	$output = '';
	if ($this->includestyletags) {$output .= "<style type='text/css'>";}
	$output .= "\n/********** MENU ***********/
				#menu {width:".$this->menuwidth."em;background:#EEEEEE;}
				#menu ul {list-style:none;margin:0;padding:0;}
				#menu a {font: $this->fontweight ".$this->fontsize."px arial, helvetical, sans-serif;display: block;";
	if ($this->fontitalic) {$output .= "font-style:italic;";}
	$output .= "border-width: ".$this->borderwidths[0]."px ".$this->borderwidths[1]."px ".$this->borderwidths[2]."px ".$this->borderwidths[3]."px;
				border-style: solid;
				border-color: #".$this->bordercols[0]." #".$this->bordercols[1]." #".$this->bordercols[2]." #".$this->bordercols[3].";
				margin: 0;padding: ".$this->padding[0]."px ".$this->padding[1]."px ".$this->padding[2]."px ".$this->padding[3]."px;}";
	if ($this->topborder) {
		$tb = explode(',', $this->topborder);
		$output .= "\n#menu a.topnavbut {border-top-width:".$tb[0]."px;border-top-color:#".$tb[1].";}";
	}
	$output .= "\n#menu a {color: #$this->fontcol;background: #$this->bgcol;text-decoration: none;}
				#menu a:hover {color: #$this->fonthovercol;background: #$this->bghovercol;}
				#menu li {position:relative;}
				#menu ul ul ul {position:absolute;top:0;left:100%;width:100%;}
				#menu ul ul ul, #menu ul ul li:hover ul ul	{display: none;z-index:1000;}
				#menu ul ul li:hover ul, #menu ul ul ul li:hover ul {display: block;}
				/***** Menu End ******/";
	if ($this->includestyletags) {$output .= "\n</style>";}
	return $output;
}

/**
 *Outputs conditional statements required to make IE browsers work.
 *@return string
 */
public function generateConditionalStatements() {
	return "<!--[if IE]>
			<style type='text/css' media='screen'>
			#menu ul li {float: left; width: 100%;}
			</style>
			<![endif]-->
			<!--[if lt IE 7]>
			<style type='text/css' media='screen'>
			body {behavior: url(csshover.htc);font-size: 100%;}
			#menu ul li {float: left; width: 100%;}
			#menu ul li a {height: 1%;}
			#menu a {font: $this->fontweight ".$this->fontsize."px arial, helvetica, sans-serif;}
			</style>
			<![endif]-->";
}

/**
 *Searches a multi-dimensional array.
 *
 *@param array $arr Multi-dimensional array to search
 *@param mixed $dimension
 *@param mixed $needle
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