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
class BackendLinkCheckerCronjobGetLinks extends BackendBaseCronjob
{
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
		$this->getLinks();
	}


	/**
	 * Cleanup database
	 *
	 * @return	void
	 */
	private function cleanupDatabase()
	{
		// cleanup pages
		BackendLinkCheckerModel::cleanupLinks();
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
			// each module has a specific edit/public url
			$editBaseUrl = BackendLinkCheckerHelper::getModuleEditUrl($module);
			$publicBaseUrl = BackendLinkCheckerHelper::getModulePublicUrl($module);

			// fetch all entries from a module
			$entries = BackendLinkCheckerModel::getModuleEntries($module);

			// @todo	remember to remove debug code later on, cronjobs shouldn't generate output unless they're exceptions (those are auto-mailed).
			echo $module . PHP_EOL;

			// @todo	opening space: http://developers.fork-cms.be/index.php?title=Coding_standards#Foreach
			// @todo	switch view: http://developers.fork-cms.be/index.php?title=Coding_standards#Switch

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
							// frontend url
							$currentPage = $publicBaseUrl . SpoonFilter::urlise($entry['title']);

							// build the array to insert
							$values = array();
							$values['title'] = $entry['title'];
							$values['module'] = str_replace('_', ' ', ucfirst($module));
							$values['language'] = $entry['language'];
							$values['public_url'] = '/' . $entry['language'] . $currentPage;
							$values['private_url'] = $editBaseUrl . $entry['id'];

							/*
								@todo	remember to respect case-sensitivity naming convention
								@todo	i wouldn't assume that when your URL is not valid, you have an external URL.
										I'd do a check if SITE_URL exists in $url instead

										$values['external'] = (strpos($url, SITE_URL) !== false) ? 'N' : 'Y';
										$values['url'] = ($values['external'] === 'Y') ? SITE_URL . $url : $url;
							*/

							// check if a link is external or internal
							// fork saves an internal link 'invalid'
							if (!spoonfilter::isURL($url))
							{
								$url = SITE_URL . $url;
								$values['external'] = 'N';
							}
							else
							{
								$values['external'] = 'Y';
							}

							// add the (edited) url
							$values['url'] = $url;

							// insert in database
							BackendModel::getDB(true)->insert('crawler_links', $values);

							// @todo	remember to remove debug code later on, cronjobs shouldn't generate output unless they're exceptions (those are auto-mailed).
							echo $url . PHP_EOL;
						}
					}
				}
			}
		}
	}
}

?>