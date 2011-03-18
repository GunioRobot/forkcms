<?php

/**
 * This cronjob will check every link in the database and stores it http code back in the database
 *
 * @package		backend
 * @subpackage	linkchecker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.0
 */
class BackendLinkCheckerCronjobCheckLinks extends BackendBaseCronjob
{
	/**
	 * Insert also working links in the database
	 *
	 * @var bool
	 */
	private $insertWorkingLinks = false;


	/**
	 * Execute the action
	 *
	 * @return	void
	 */
	public function execute()
	{
		// cleanup database
		$this->cleanupDatabase();

		// require the helper class
		require_once BACKEND_MODULES_PATH . '/link_checker/engine/helper.php';

		// get data
		$this->checkLinks();
	}


	/**
	 * Cleanup database
	 *
	 * @return	void
	 */
	private function cleanupDatabase()
	{
		// cleanup pages
		BackendLinkCheckerModel::cleanupResults();
	}


	/**
	 * Check links
	 *
	 * @return	void
	 */
	private function checkLinks()
	{
		// build our query
		$query = 'SELECT * FROM crawler_links AS c';

		// fetch the records
		$links = BackendModel::getDB(true)->getRecords($query);

		// loop every link if there are any
		if(isset($links))
		{

			foreach ($links as $link)
			{
				// built the array to insert
				$values = array ();
				$values['title'] = $link['title'];
				$values['module'] = $link['module'];
				$values['external'] = $link['external'];
				$values['language'] = $link['language'];
				$values['public_url'] = $link['public_url'];
				$values['private_url'] = $link['private_url'];
				$values['url'] = $link['url'];

				// check the link and retrieve the http error code
				$values['code'] = BackendLinkCheckerHelper::checkLink($link['url']);

				// remove the 'http://' before insert
				$values['url'] = str_replace('http://', '', $values['url']);

				// @todo	remember to remove debug code later on, cronjobs shouldn't generate output unless they're exceptions (those are auto-mailed).
				echo $values['url'] .' => '. $values['code'] . "\r\n";

				// only insert dead or non working links
				if(!$values['code'] || $values['code'] >= 400 && $values['code'] < 600 || $this->insertWorkingLinks)
				{
					// insert into database
					BackendModel::getDB(true)->insert('crawler_results', $values);
				}
			}
		}
	}
}

?>