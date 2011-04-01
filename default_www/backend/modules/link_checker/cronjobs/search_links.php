<?php

/**
 * This cronjob will get every link in the given modules and store it in the database
 *
 * @package		backend
 * @subpackage	linkchecker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.0
 */
class BackendLinkCheckerCronjobSearchLinks extends BackendBaseCronjob
{
	/**
	 * All links found
	 *
	 * @var bool
	 */
	private $allLinks = array();


	/**
	 * Execute the action
	 *
	 * @return	void
	 */
	public function execute()
	{
		// cleanup database
		$this->emptyDatabase();

		// require the helper class
		require_once BACKEND_MODULES_PATH . '/link_checker/engine/helper.php';

		// get data
		$this->getLinks();

		// check data
		$this->checkLinks();
	}


	/**
	 * Cleanup database
	 *
	 * @return	void
	 */
	private function emptyDatabase()
	{
		// cleanup pages
		BackendLinkCheckerModel::clear();
	}


	/**
	 * Get links from modules
	 *
	 * @return	void
	 */
	private function getLinks()
	{
		$this->allLinks = BackendLinkCheckerHelper::getAllLinks('multiArray');
	}


	/**
	 * Check links
	 *
	 * @return	void
	 */
	private function checkLinks()
	{
		// loop every link if there are any
		if(isset($this->allLinks))
		{
			// check all urls, get there error code and insert into database
			BackendLinkCheckerHelper::checkLink($this->allLinks);
		}
	}
}

?>