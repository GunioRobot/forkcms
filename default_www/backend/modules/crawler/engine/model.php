<?php

/**
 * BackendCrawlerModel
 * In this file we store all generic functions that we will be using in the mailmotor module
 *
 * @package		backend
 * @subpackage	mailmotor
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.0
 */
class BackendCrawlerModel
{
	/**
	 * Get all urls
	 *
	 * @return	array
	 */
	public static function getAll()
	{
		// fetch the records
		$records = (array) BackendModel::getDB()->getRecords('SELECT c.origin, c.code, c.url from crawler AS c');

		return $records;
	}

	/**
	 * Get all internal urls
	 *
	 * @return	array
	 */
	public static function getInternal()
	{
		// fetch the records
		$records = (array) BackendModel::getDB()->getRecords('SELECT c.origin, c.code, c.url from crawler AS c WHERE c.external = "N"');

		return $records;
	}

	/**
	 * Get all external urls
	 *
	 * @return	array
	 */
	public static function getExternal()
	{
		// fetch the records
		$records = (array) BackendModel::getDB()->getRecords('SELECT c.origin, c.code, c.url from crawler AS c WHERE c.external = "Y"');

		return $records;
	}
}

?>