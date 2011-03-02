<?php

/**
 * This cronjob will check every link
 *
 * @package		backend
 * @subpackage	crawler
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.0
 */
class BackendCrawlerCronjobCheckLinks extends BackendBaseCronjob
{
	/**
	 * Cleanup database
	 *
	 * @return	void
	 */
	private function cleanupDatabase()
	{
		// cleanup pages
		BackendModel::getDB(true)->truncate('crawler');
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
		$this->getData();
	}

	/**
	 * Get data from analytics
	 *
	 * @return	void
	 */
	private function getData()
	{
		$modules = array('blog', 'content', 'pages');

		foreach($modules as $module){

			// build our query
			$queryBlog = "
					SELECT p.text, p.title, p.id FROM blog_posts AS p
					WHERE text LIKE '%href=%'
					AND status = 'active'
					AND hidden = 'N'
					";

			$queryContentBlocks = "
					SELECT c.text, c.title, c.id FROM content_blocks AS c
					WHERE text LIKE '%href=%'
					AND status = 'active'
					AND hidden = 'N'
					";

			$queryPages = "
					SELECT p.html as text, p.id, p.revision_id as title FROM pages_blocks AS p
					WHERE html LIKE '%href=%'
					AND status = 'active'
					"; //todo join pages to get page title

			$query = '';
			$editBaseUrl = '';
			$publicBaseUrl = '';

			switch ($module) {
			    case 'blog':
			        $query = $queryBlog;
			        $editBaseUrl = 'http://forkgit.local/private/blog/edit?token=true&id=';
			        $publicBaseUrl = '/blog/detail/';
			        break;
			    case 'content':
			        $query = $queryContentBlocks;
			        $editBaseUrl = 'http://forkgit.local/private/content_blocks/edit?token=true&id=';
			        $publicBaseUrl = '/';
			        break;
			    case 'pages':
			        $query = $queryPages;
			        $editBaseUrl = 'http://forkgit.local/private/pages/edit?id=';
			        $publicBaseUrl = '/';
			        break;
			}

			// fetch the record
			$records = BackendModel::getDB(true)->getRecords($query);

			foreach ($records as $d) {
				// get all links
				if (preg_match_all("!href=\"(.*?)\"!", $d['text'], $matches)) {

					// the current page
					//edit url
					//echo 'Entry: <a href=' . $editBaseUrl . $d['id'] . '>' . $d['title'] . '</a><br/>';
					//frontend url
					$currentPage = $publicBaseUrl . SpoonFilter::urlise($d['title']);

					// url's per page
					$url_list = array();

					foreach ($matches[1] as $url) {
						// store the url
						$url_list[] = $url;
					}

					// remove duplicates
					$url_list = array_values(array_unique($url_list));

					// fetch pages
					foreach($url_list as $url){

						// initialize
						$ch = curl_init();

						$values = array();
						$values['module'] = $module;
						$values['origin'] = $currentPage;

						// check if a link is external or internal
						// fork saves an internal link 'invalid'
						if (!spoonfilter::isURL($url)){
							$url = SITE_URL . $url;
							$values['external'] = 'N';
						}else{
							$values['external'] = 'Y';
						}

						// set the options, including the url
						curl_setopt($ch, CURLOPT_URL, $url);

						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						// follow redirections
						curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
						// do not need the body. this saves bandwidth and time
						curl_setopt($ch, CURLOPT_NOBODY, 1);

						// execute and fetch the resulting HTML output
						curl_exec($ch);

						// get the info on the curl handle
						$chinfo = curl_getinfo($ch);

						// free up the curl handle
						curl_close($ch);

						$values['code'] = $chinfo['http_code'];
						$values['url'] = $chinfo['url'];

						// dead/faulty/non existing link?
						if (!$chinfo['http_code']) {
							BackendModel::getDB(true)->insert('crawler', $values);

						// 4xx, 5xx error
						} else if ($chinfo['http_code'] >= 400 && $chinfo['http_code'] < 600) {
							BackendModel::getDB(true)->insert('crawler', $values);

						// 2xx, 3xx working
						} else if ($chinfo['http_code'] >= 200 && $chinfo['http_code'] < 400) {
							BackendModel::getDB(true)->insert('crawler', $values);
						}
					}
				}
			}
		}
	}
}

?>