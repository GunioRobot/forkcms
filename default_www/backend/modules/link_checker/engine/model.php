<?php

/**
 * BackendLinkCheckerModel
 * In this file we store all generic functions that we will be using in the linkchecker module
 *
 * @package		backend
 * @subpackage	link_checker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.0
 */
class BackendLinkCheckerModel
{
	/**
	 * Empty database
	 *
	 * @return	void
	 */
	public static function clear()
	{
		// truncate table
		BackendModel::getDB()->truncate('link_checker_results');
	}


	/**
	 * Empty cache
	 *
	 * @return	void
	 */
	public static function clearCache()
	{
		// truncate table
		BackendModel::getDB()->truncate('link_checker_cache');
	}


	/**
	 * Delete link
	 *
	 * @return	array
	 * @param	string $url		The url to delte.
	 */
	public static function deleteLink($url)
	{
		// remove a dead link
		BackendModel::getDB()->delete('link_checker_results', 'url = ?', $url);
	}


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
	 * Get cache url
	 *
	 * @return	array
	 * @param	string $url		The requested url.
	 */
	public static function getCacheLink($url)
	{
		// fetch and return the records
		return (array) BackendModel::getDB()->getRecord('SELECT l.url, l.error_code, l.date_checked FROM link_checker_cache AS l WHERE l.url = ? ORDER BY l.date_checked DESC', $url);
	}


	/**
	 * Get the requested dead url
	 *
	 * @return	array
	 * @param	string $url		The url we want returned.
	 */
	public static function getDeadUrl($url)
	{
		// fetch and return the records
		return (array) BackendModel::getDB()->getRecord('SELECT c.module, c.item_id, c.item_title, c.url, c.error_code, c.external, c.language, c.date_checked
															FROM link_checker_results AS c
															WHERE c.url = ?', $url);
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
	 * Get all module entries
	 *
	 * @return	array
	 * @param	string $module		The module name.
	 */
	public static function getModuleEntries($module)
	{
		// contains the records
		$records = array();

		// each module has a different query
		switch($module)
		{
		    case 'blog':
		        // build blog text query
		    	$queryText = "SELECT p.text, p.title, p.id, p.language FROM blog_posts AS p
						WHERE p.text LIKE '%href=%'
						AND p.status = 'active'
						AND p.hidden = 'N'";

		    	// fetch text records
		        $recordsText = (array) BackendModel::getDB()->getRecords($queryText);

		         // build blog introduction query
		    	$queryIntro = "SELECT p.introduction AS text, p.title, p.id, p.language FROM blog_posts AS p
						WHERE p.introduction LIKE '%href=%'
						AND p.status = 'active'
						AND p.hidden = 'N'";

		    	// fetch introduction records
		        $recordsIntro = (array) BackendModel::getDB()->getRecords($queryIntro);

		        // merge arrays
		        $records = array_merge($recordsText, $recordsIntro);
		    break;

		    case 'content_blocks':
		        // build query
		    	$query = "SELECT c.text, c.title, c.id, c.language FROM content_blocks AS c
						WHERE c.text LIKE '%href=%'
						AND c.status = 'active'
						AND c.hidden = 'N'";

		        // fetch records
		    	$records = BackendModel::getDB()->getRecords($query);
		    break;

		    case 'pages':
		        // build query
		    	$query = "SELECT p.html as text, pa.id, pa.title, pa.language FROM pages_blocks AS p
						INNER JOIN pages AS pa on p.revision_id = pa.revision_id
						WHERE p.html LIKE '%href=%'
						AND pa.status = 'active'
						AND pa.hidden = 'N'";

		        // fetch records
		    	$records = (array) BackendModel::getDB()->getRecords($query);
		    break;

		    case 'faq':
		        // build query
		    	$query = "SELECT f.answer as text, f.id, f.question as title, f.language FROM faq_questions AS f
						WHERE f.answer LIKE '%href=%'
						AND f.hidden = 'N'";

		        // fetch records
		    	$records = (array) BackendModel::getDB()->getRecords($query);
		    break;
		}

		// return records
		return $records;
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
	 * Insert cache
	 *
	 * @return	array
	 * @param	string $url				The checked url.
	 * @param	string $httpCode		The http code of the checked url.
	 * @param	string $dateChecked		The date the url was checked.
	 */
	public static function insertCache($url, $httpCode, $dateChecked)
	{
		// insert cache
		BackendModel::getDB()->insert('link_checker_cache', array('url' => $url, 'error_code' => $httpCode, 'date_checked' => $dateChecked));
	}


	/**
	 * Insert links
	 *
	 * @return	array
	 * @param	array $values		The values to insert.
	 */
	public static function insertLinks($values)
	{
		// insert freshly found dead links
		if(!empty($values)) BackendModel::getDB()->insert('link_checker_results', $values);
	}


	/**
	 * Is the link a valid cache url?
	 *
	 * @return	bool
	 * @param	string $url		The url to check.
	 */
	public static function isValidCache($url)
	{
		// max cache time
		$maxTime = (int) BackendModel::getModuleSetting('link_checker', 'cache_time');

		// retrieve most recent saved cache url
		$return = BackendModel::getDB()->getRecord("SELECT l.url, l.error_code, l.date_checked FROM link_checker_cache AS l WHERE l.url = ? ORDER BY l.date_checked DESC", array($url));

		// check if most recent is still valid
		if((time() - strtotime($return['date_checked'])) < $maxTime) return true;

		// else
		return false;
	}
}

?>