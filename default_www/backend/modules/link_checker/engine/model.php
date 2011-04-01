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
		return (array) BackendModel::getDB()->getRecords('SELECT c.item_title AS title, c.module, c.error_code AS description, c.url, c.item_id, c.date_checked
															FROM link_checker_results AS c
															WHERE c.language = ?', BL::getWorkingLanguage());
	}


	/**
	 * Get 5 most recent links
	 *
	 * @return	array
	 */
	public static function getMostRecent()
	{
		// fetch and return the records
		return (array) BackendModel::getDB()->getRecords('SELECT c.item_title AS title, c.module, c.error_code AS description, c.url, c.item_id, c.date_checked
															FROM link_checker_results AS c
															WHERE c.language = ?
															LIMIT 5', BL::getWorkingLanguage());
	}


	/**
	 * Get all internal urls
	 *
	 * @return	array
	 */
	public static function getInternal()
	{
		// fetch and return the records
		return (array) BackendModel::getDB()->getRecords('SELECT c.item_title AS title, c.module, c.error_code AS description, c.url, c.item_id, c.date_checked
															FROM link_checker_results AS c
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
		return (array) BackendModel::getDB()->getRecords('SELECT c.item_title AS title, c.module, c.error_code AS description, c.url, c.item_id, c.date_checked
															FROM link_checker_results AS c
															WHERE c.external = "Y"
															AND c.language = ?', BL::getWorkingLanguage());
	}


	/**
	 * Get all dead urls
	 *
	 * @return	array
	 */
	public static function getDeadUrls()
	{
		// fetch and return the records
		return (array) BackendModel::getDB()->getColumn('SELECT c.url
															FROM link_checker_results AS c
															WHERE c.language = ?', BL::getWorkingLanguage());
	}


	/**
	 * Get all module entries
	 *
	 * @return	array
	 */
	public static function getModuleEntries($module)
	{
		// contains the query
		$query = '';

		// each module has a different query
		switch ($module)
		{
		    case 'blog':
		        $query = "SELECT p.text, p.title, p.id, p.language FROM blog_posts AS p
						WHERE p.text LIKE '%href=%'
						AND p.status = 'active'
						AND p.hidden = 'N'";
		    break;

		    case 'content_blocks':
		        $query = "SELECT c.text, c.title, c.id, c.language FROM content_blocks AS c
						WHERE c.text LIKE '%href=%'
						AND c.status = 'active'
						AND c.hidden = 'N'";
		    break;

		    case 'pages':
		        $query = "SELECT p.html as text, pa.id, pa.title, pa.language FROM pages_blocks AS p
						INNER JOIN pages AS pa on p.revision_id = pa.revision_id
						WHERE p.html LIKE '%href=%'
						AND pa.status = 'active'
						AND pa.hidden = 'N'";
		    break;

		    case 'faq':
		        $query = "SELECT f.answer as text, f.id, f.question as title, f.language FROM faq_questions AS f
						WHERE f.answer LIKE '%href=%'
						AND f.hidden = 'N'";
		    break;
		}

		// fetch and return the records
		return (array) BackendModel::getDB()->getRecords($query);
	}


	/**
	 * Empty database
	 *
	 * @return	array
	 */
	public static function clear()
	{
		BackendModel::getDB()->truncate('link_checker_results');
	}


	/**
	 * Insert links
	 *
	 * @return	array
	 */
	public static function insertLinks($values)
	{
		if(!empty($values)) BackendModel::getDB()->insert('link_checker_results', $values);
	}


	/**
	 * Delete link
	 *
	 * @return	array
	 */
	public static function deleteLink($url)
	{
		BackendModel::getDB()->delete('link_checker_results', 'url = ?', $url);
	}
}

?>