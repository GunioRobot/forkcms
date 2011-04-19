<?php

/**
 * This cronjob will check every link on the website and store dead links in the database
 *
 * @package		backend
 * @subpackage	link_checker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.1
 */
class BackendLinkCheckerCronjobSearchLinks extends BackendBaseCronjob
{
	/**
	 * All links found on the website
	 *
	 * @var bool
	 */
	private $allLinks = array();


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
			// check all links, get there error code and insert into database
			BackendLinkCheckerHelper::checkLinks($this->allLinks);
		}
	}


	/**
	 * Cleanup database
	 *
	 * @return	void
	 */
	private function emptyDatabase()
	{
		// empty database
		BackendLinkCheckerModel::clear();

		// empty cache
		BackendLinkCheckerModel::clearCache();
	}


	/**
	 * Execute the action
	 *
	 * @return	void
	 */
	public function execute()
	{
		// call parent, this will probably add some general CSS/JS or other required files
		parent::execute();

		// empty database
		$this->emptyDatabase();

		// require the helper class
		require_once BACKEND_MODULES_PATH . '/link_checker/engine/helper.php';

		// get data
		$this->getLinks();

		// check data
		$this->checkLinks();
	}


	/**
	 * Get links
	 *
	 * @return	void
	 */
	private function getLinks()
	{
		// get all the links a website contains in a multidimensional array containing all information about the link
		$this->allLinks = BackendLinkCheckerHelper::getAllLinks('multiArray');
	}
}

?>