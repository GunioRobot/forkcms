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
		$modules = array('blog', 'content_blocks', 'pages');

		// @todo	comment the foreach, describe what happens inside the looping
		foreach($modules as $module)
		{
			// build the query for each module
			// @todo	a better idea would be to group your queries in the switch below, since you're putting them in $query anyway
			$queryBlog = "
					SELECT p.text, p.title, p.id, p.language FROM blog_posts AS p
					WHERE text LIKE '%href=%'
					AND status = 'active'
					AND hidden = 'N'
					";

			$queryContentBlocks = "
					SELECT c.text, c.title, c.id, c.language FROM content_blocks AS c
					WHERE text LIKE '%href=%'
					AND status = 'active'
					AND hidden = 'N'
					";

			$queryPages = "
					SELECT p.html as text, pa.id, pa.title, pa.language FROM pages_blocks AS p
					INNER JOIN pages AS pa on p.revision_id = pa.revision_id
					WHERE p.html LIKE '%href=%'
					AND p.status = 'active'
					AND hidden = 'N'
					";

			$query = '';
			$editBaseUrl = '';
			$publicBaseUrl = '';

			// @todo	remember to remove debug code later on, cronjobs shouldn't generate output unless they're exceptions (those are auto-mailed).
			echo '---' . "\r\n";
			echo $module . "\r\n";
			echo '---' . "\r\n";

			// loop the modules
			// @todo	opening space: http://developers.fork-cms.be/index.php?title=Coding_standards#Foreach
			// @todo	switch view: http://developers.fork-cms.be/index.php?title=Coding_standards#Switch
			switch ($module)
			{
			    case 'blog':
			        $query = $queryBlog;
			        $editBaseUrl = '/private/' . BL::getInterfaceLanguage() . '/blog/edit?token=true&id=';
			        $publicBaseUrl = '/blog/detail/';
			        break;
			    case 'content_blocks':
			        $query = $queryContentBlocks;
			        $editBaseUrl = '/private/' . BL::getInterfaceLanguage() . '/content_blocks/edit?token=true&id=';
			        $publicBaseUrl = '/';
			        break;
			    case 'pages':
			        $query = $queryPages;
			        $editBaseUrl = '/private/' . BL::getInterfaceLanguage() . '/pages/edit?id=';
			        $publicBaseUrl = '/';
			        break;
			}

			// fetch all entries from a module
			$records = BackendModel::getDB(true)->getRecords($query);

			// seach every entry for links
			if(isset($records)){

				foreach ($records as $record)
				{
					// get all links
					if (preg_match_all("!href=\"(.*?)\"!", $record['text'], $matches))
					{
						//frontend url
						$currentPage = $publicBaseUrl . SpoonFilter::urlise($record['title']);

						// url's per page
						// @todo	naming convention -> $urlList
						$url_list = array();

						// @todo	comment what happens inside the loop, and what you're looping (like an example of the format in case of $matches)
						foreach ($matches[1] as $url)
						{
							// store the url
							$url_list[] = $url;
						}

						// remove duplicates
						$url_list = array_values(array_unique($url_list));

						// store every link inside this entry in the database
						foreach($url_list as $url)
						{
							$values = array();
							$values['title'] = $record['title'];

							$values['module'] = str_replace('_', ' ', ucfirst($module));
							$values['language'] = $record['language'];
							$values['public_url'] = '/' . $record['language'] . $currentPage;
							$values['private_url'] = $editBaseUrl . $record['id'];

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
							}else
							{
								$values['external'] = 'Y';
							}

							$values['url'] = $url;

							BackendModel::getDB(true)->insert('crawler_links', $values);

							/*
								@todo	remember to remove debug code later on, cronjobs shouldn't generate output unless they're exceptions (those are auto-mailed).
								Unrelated pro tip: use PHP_EOL instead of "\r\n" whenever possible
							*/
							echo $url . "\r\n";
						}
					}
				}
			}
		}
	}
}

?>
