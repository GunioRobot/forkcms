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

		foreach($modules as $module)
		{
			// build the query for each module
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

			$queryFaq = "
					SELECT f.answer as text, f.id, f.question as title, f.language FROM faq_questions AS f
					WHERE f.answer LIKE '%href=%'
					AND f.hidden = 'N'
					";

			$query = '';
			$editBaseUrl = '';
			$publicBaseUrl = '';

			//echo '---' . "\r\n";
			//echo $module . "\r\n";
			//echo '---' . "\r\n";

			// loop the modules
			switch ($module)
			{
			    case 'blog':
			        $query = $queryBlog;
			        $editBaseUrl = '/private/' . BL::getInterfaceLanguage() . '/blog/edit?token=true&id=';
			        $publicBaseUrl = '/blog/detail/';
			        break;
			    case 'content_blocks': //incomplete !!!
			        $query = $queryContentBlocks;
			        $editBaseUrl = '/private/' . BL::getInterfaceLanguage() . '/content_blocks/edit?token=true&id=';
			        $publicBaseUrl = '/';
			        break;
			    case 'pages':
			        $query = $queryPages;
			        $editBaseUrl = '/private/' . BL::getInterfaceLanguage() . '/pages/edit?id=';
			        $publicBaseUrl = '/';
			        break;
			    case 'faq':
			        $query = $queryFaq;
			        $editBaseUrl = '/private/' . BL::getInterfaceLanguage() . '/faq/edit?id=';
			        $publicBaseUrl = '/faq/';
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
						$url_list = array();

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

							//echo $url . "\r\n";
						}
					}
				}
			}
		}
	}
}

?>
