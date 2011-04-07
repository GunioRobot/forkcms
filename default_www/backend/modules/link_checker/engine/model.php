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
	 * Get the requested dead url
	 *
	 * @return	array
	 */
	public static function getDeadUrl($url)
	{
		// fetch and return the records
		return (array) BackendModel::getDB()->getRecord('SELECT c.module, c.item_id, c.item_title, c.url, c.error_code, c.external, c.language, c.date_checked
															FROM link_checker_results AS c
															WHERE c.url = ?', $url);
	}


	/**
	 * Get all module entries
	 *
	 * @return	array
	 */
	public static function getModuleEntries($module)
	{
		// contains the records
		$records = array();

		// each module has a different query
		switch ($module)
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
	 * Empty database
	 *
	 * @return	array
	 */
	public static function clear()
	{
		// truncate table
		BackendModel::getDB()->truncate('link_checker_results');
	}


	/**
	 * Insert links
	 *
	 * @return	array
	 */
	public static function insertLinks($values)
	{
		// insert freshly found dead links
		if(!empty($values)) BackendModel::getDB()->insert('link_checker_results', $values);
	}


	/**
	 * Delete link
	 *
	 * @return	array
	 */
	public static function deleteLink($url)
	{
		// remove a dead link
		BackendModel::getDB()->delete('link_checker_results', 'url = ?', $url);
	}
}

?>