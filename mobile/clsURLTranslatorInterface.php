<?php
/**
 * URLTranslatorInterface class
 */
/**
 * Interface for mobile translator classes.
 * @package dstruct_mobile
 */
interface URLTranslatorInterface {
/**
 * Class constructor
 * @param string $main Main site
 * @param string $alt Alternative 'mobile' site
 */
public function __construct($main, $alt);

/**
 * On the mobile version of the site?
 * @return boolean
 */
public function onAlternativePage();

/**
 * Get the rewritten path.
 * @return string
 */
public function rewrite();

/**
 * Set the URL of the page of interest.
 * @param string $url
 */
public function setURL($url);
}
?>