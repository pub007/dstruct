<?php
namespace pub007\dstruct;

class DocStore
{

	protected static $stores = [];
	protected static $defaultStore;

	public static function gi(string $collection = '', ?string $store = null)
	{
		$store = $store ?? $_ENV['STORE_DEFAULT'] ?? false;

		if (! $store) {
			throw new \ErrorException("Unable to find a Store");
		}

		if (! isset(self::$stores[$store])) {
			$storesSettings = Prefs::gi()->get('stores');
			$options = $storesSettings[$store]['options'] ?? [];
			self::$stores[$store] = new \MongoDB\Client($storesSettings[$store]['connectionString'], [], $options);
		}

		if ($collection) {
			return self::$stores[$store]->$store->$collection;
		}

		return self::$stores[$store];
	}

}