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
	private $insertWorkingLinks = true;

	/**
	 * Cleanup database
	 *
	 * @return	void
	 */
	private function cleanupDatabase()
	{
		// cleanup pages
		BackendModel::getDB(true)->truncate('crawler_results');
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
		$this->checkLinks();
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
		if(isset($links)){

			foreach ($links as $link)
			{
				// @todo	remember to remove debug code later on, cronjobs shouldn't generate output unless they're exceptions (those are auto-mailed).
				echo '---' . "\r\n";
				echo $link['module'] . "\r\n";
				echo '---' . "\r\n";

				// initialize
				$ch = curl_init();

				// built the array to insert
				$values = array();
				$values['title'] = $link['title'];
				$values['module'] = $link['module'];
				$values['external'] = $link['external'];
				$values['language'] = $link['language'];
				$values['public_url'] = $link['public_url'];
				$values['private_url'] = $link['private_url'];
				$values['url'] = $link['url'];

				// set the options, including the url
				curl_setopt($ch, CURLOPT_URL, $values['url']);
	            curl_setopt($ch, CURLOPT_HEADER, 1);
	            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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
				$values['url'] = str_replace('http://', '', $values['url']);

				// @todo	remember to remove debug code later on, cronjobs shouldn't generate output unless they're exceptions (those are auto-mailed).
				echo $values['url'] .' => '. $values['code'] . "\r\n";

				if (!$chinfo['http_code'])
				{
					// dead/faulty/non existing link?
					BackendModel::getDB(true)->insert('crawler_results', $values);
				}
				else if ($chinfo['http_code'] >= 400 && $chinfo['http_code'] < 600)
				{
					// 4xx, 5xx error
					BackendModel::getDB(true)->insert('crawler_results', $values);
				}
				if($this->insertWorkingLinks)
				{
					if ($chinfo['http_code'] >= 200 && $chinfo['http_code'] < 400)
					{
						// 2xx, 3xx working
						BackendModel::getDB(true)->insert('crawler_results', $values);
					}
				}
			}
		}
	}
}

?>