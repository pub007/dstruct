<?php
/**
 * URLTranslatorFactory class
 */
/**
 * Factory to create translator classes.
 * @package dstruct_mobile
 */
class URLTranslatorFactory {

/**
 * Create a translator class.
 * 
 * Currently available classes: main, path or subdomain.
 * WARNING: Only path is currently implimented.
 * @param string $class Class to create
 * @param string $main URL of main site
 * @param string $alt URL of alternative 'mobile' site
 * @throws DStructGeneralException
 * @return URLTranslatorSubDomain|URLTranslatorPath|URLTranslatorExtension
 */
public static function createTranslator($class, $main, $alt) {
	switch($class) {
		case 'subdomain':
			return new URLTranslatorSubDomain($main, $alt);
			break;
		case 'path':
			return new URLTranslatorPath($main, $alt);
			break;
		case 'extension':
			return new URLTranslatorExtension($main, $alt);
			break;
		default:
			throw new DStructGeneralException('URLTranslator::createTranslator() - Unknown Translator class');
			break;
	}
}

}
?>