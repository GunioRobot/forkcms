<?php

/**
 * BackendCrawlerModel
 * In this file we store all generic functions that we will be using in the mailmotor module
 *
 * @package		backend
 * @subpackage	crawler
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
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
		$records = (array) BackendModel::getDB()->getRecords('SELECT c.title, c.module, c.code, c.url, c.public_url, c.private_url from crawler_results AS c');

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
		$records = (array) BackendModel::getDB()->getRecords('SELECT c.title, c.module, c.code, c.url from crawler_results AS c WHERE c.external = "N"');

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
		$records = (array) BackendModel::getDB()->getRecords('SELECT c.title, c.module, c.code, c.url from crawler_results AS c WHERE c.external = "Y"');

		return $records;
	}
}

?>