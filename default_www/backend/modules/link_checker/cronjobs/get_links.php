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
	 * Cleanup database
	 *
	 * @return	void
	 */
	private function cleanupDatabase()
	{
		// cleanup pages
		BackendModel::getDB(true)->truncate('crawler_links');
	}


	/**
	 * Execute the action
	 *
	 * @return	void
	 */
	public function execute()
	{
		// cleanup database
		$this->cleanupDatabase();

		// get data
		$this->getLinks();
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
			// variables we create for each module
			$query = '';
			$editBaseUrl = '';
			$publicBaseUrl = '';

			// @todo	remember to remove debug code later on, cronjobs shouldn't generate output unless they're exceptions (those are auto-mailed).
			echo '---' . PHP_EOL;
			echo $module . PHP_EOL;
			echo '---' . PHP_EOL;

			// @todo	opening space: http://developers.fork-cms.be/index.php?title=Coding_standards#Foreach
			// @todo	switch view: http://developers.fork-cms.be/index.php?title=Coding_standards#Switch

			// each module has a different configuration
			switch ($module)
			{
			    case 'blog':
			        $query = "SELECT p.text, p.title, p.id, p.language FROM blog_posts AS p
							WHERE text LIKE '%href=%'
							AND status = 'active'
							AND hidden = 'N'";
			        $editBaseUrl = '/private/' . BL::getInterfaceLanguage() . '/blog/edit?token=true&id=';
			        $publicBaseUrl = '/blog/detail/';
			        break;
			    case 'content_blocks':
			        $query = "SELECT c.text, c.title, c.id, c.language FROM content_blocks AS c
							WHERE text LIKE '%href=%'
							AND status = 'active'
							AND hidden = 'N'";
			        $editBaseUrl = '/private/' . BL::getInterfaceLanguage() . '/content_blocks/edit?token=true&id=';
			        $publicBaseUrl = '/';
			        break;
			    case 'pages':
			        $query = "SELECT p.html as text, pa.id, pa.title, pa.language FROM pages_blocks AS p
							INNER JOIN pages AS pa on p.revision_id = pa.revision_id
							WHERE p.html LIKE '%href=%'
							AND p.status = 'active'
							AND hidden = 'N'";
			        $editBaseUrl = '/private/' . BL::getInterfaceLanguage() . '/pages/edit?id=';
			        $publicBaseUrl = '/';
			        break;
			    case 'faq':
			        $query = "SELECT f.answer as text, f.id, f.question as title, f.language FROM faq_questions AS f
							WHERE f.answer LIKE '%href=%'
							AND f.hidden = 'N'";
			        $editBaseUrl = '/private/' . BL::getInterfaceLanguage() . '/faq/edit?id=';
			        $publicBaseUrl = '/faq/';
			        break;
			}

			// fetch all entries from a module
			$entries = BackendModel::getDB(true)->getRecords($query);

			// seach every entry for links, if the module is not empty
			if(isset($entries)){

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

							/*
								@todo	remember to remove debug code later on, cronjobs shouldn't generate output unless they're exceptions (those are auto-mailed).
								Unrelated pro tip: use PHP_EOL instead of "\r\n" whenever possible
							*/
							echo $url . PHP_EOL;
						}
					}
				}
			}
		}
	}
}

?>