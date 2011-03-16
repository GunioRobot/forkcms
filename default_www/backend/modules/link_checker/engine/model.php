<?php

// @todo	2 spaces inbetween methods here
// @todo	No need to store $records in a separate array here, since you do nothing with them anyway. You can return them straight away.

/**
 * BackendLinkCheckerModel
 * In this file we store all generic functions that we will be using in the linkchecker module
 *
 * @package		backend
 * @subpackage	linkchecker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.0
 */
class BackendLinkCheckerModel
{
	/**
	 * Get all urls
	 *
	 * @return	array
	 */
	public static function getAll()
	{
		// fetch the records
		$records = (array) BackendModel::getDB()->getRecords('SELECT c.title, c.module, c.code, c.url, c.public_url, c.private_url, cc.description
															FROM crawler_results AS c
															INNER JOIN crawler_codes AS cc ON cc.code = c.code
															WHERE c.language = ?', BL::getWorkingLanguage());

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
		$records = (array) BackendModel::getDB()->getRecords('SELECT c.title, c.module, c.code, c.url, c.public_url, c.private_url, cc.description
															FROM crawler_results AS c
															INNER JOIN crawler_codes AS cc ON cc.code = c.code
															WHERE c.external = "N"
															AND c.language = ?', BL::getWorkingLanguage());

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
		$records = (array) BackendModel::getDB()->getRecords('SELECT c.title, c.module, c.code, c.url, c.public_url, c.private_url, cc.description
															FROM crawler_results AS c
															INNER JOIN crawler_codes AS cc ON cc.code = c.code
															WHERE c.external = "Y"
															AND c.language = ?', BL::getWorkingLanguage());

		return $records;
	}
}

?>