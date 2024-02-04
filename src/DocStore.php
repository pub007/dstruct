<?php
namespace pub007\dstruct;

class DocStore
{

	protected static $stores = [];

	public static function gi(string $collection = '', string $store = "cache")
	{
		if (! isset(self::$stores[$store])) {
			$storesSettings = Prefs::gi()->get('stores');
			self::$stores[$store] = new \MongoDB\Client($storesSettings[$store]['connectionString'], [], $storesSettings[$store]['options']);
		}

		if ($collection) {
			return self::$stores[$store]->$store->$collection;
		}

		return self::$stores[$store];
	}

}