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
		// modules to check
		$modules = array('blog', 'content_blocks', 'pages', 'faq');

		// loop all modules
		foreach($modules as $module)
		{
			// fetch all entries from a module
			$entries = BackendLinkCheckerModel::getModuleEntries($module);

			// seach every entry for links, if the module is not empty
			if(isset($entries))
			{
				// we check everye entry in this module for links
				foreach ($entries as $entry)
				{
					// get all links in this entry
					if (preg_match_all("!href=\"(.*?)\"!", $entry['text'], $matches))
					{
						// all urls we find in this entry
						$urlList = array();

						// @todo	comment what happens inside the loop, and what you're looping (like an example of the format in case of $matches)
						foreach ($matches[1] as $url)
						{
							// add the url to the list
							$urlList[] = $url;
						}

						// remove duplicates
						$urlList = array_values(array_unique($urlList));

						// store every link inside this entry in the database
						foreach($urlList as $url)
						{
							// build the array to insert
							$values = array();
							$values['item_title'] = $entry['title'];
							$values['module'] = $module;
							$values['language'] = $entry['language'];
							$values['item_id'] = $entry['id'];

							// check if a link is external or internal
							// fork saves an internal link 'invalid'
							$values['external'] = (spoonfilter::isURL($url)) ? 'Y' : 'N';
							$values['url'] = ($values['external'] === 'Y') ? $url : SITE_URL . $url;

							// add to allLinks array
							$this->allLinks[] = $values;
						}
					}
				}
			}
		}
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