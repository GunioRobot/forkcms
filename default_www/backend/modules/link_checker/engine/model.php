<?php

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
		// fetch and return the records
		return (array) BackendModel::getDB()->getRecords('SELECT c.title, c.module, c.code AS description, c.url, c.public_url, c.private_url
															FROM crawler_results AS c
															WHERE c.language = ?', BL::getWorkingLanguage());
	}


	/**
	 * Get all internal urls
	 *
	 * @return	array
	 */
	public static function getInternal()
	{
		// fetch and return the records
		return (array) BackendModel::getDB()->getRecords('SELECT c.title, c.module, c.code AS description, c.url, c.public_url, c.private_url
															FROM crawler_results AS c
															WHERE c.external = "N"
															AND c.language = ?', BL::getWorkingLanguage());
	}


	/**
	 * Get all external urls
	 *
	 * @return	array
	 */
	public static function getExternal()
	{
		// fetch and return the records
		return (array) BackendModel::getDB()->getRecords('SELECT c.title, c.module, c.code AS description, c.url, c.public_url, c.private_url
															FROM crawler_results AS c
															WHERE c.external = "Y"
															AND c.language = ?', BL::getWorkingLanguage());
	}
}

?>