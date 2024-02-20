<?php
/**
 * Bootstrap file for DStruct
 */
namespace pub007\dstruct;

class Bootstrap {

	public function init()
	{
		$prefs = Prefs::gi();
		$prefs->set('DSTRUCT_TIMER_START', microtime(true));
	}
}
