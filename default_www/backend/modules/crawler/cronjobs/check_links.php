<?php

/**
 * This cronjob will check every link in the database and stores it http code back in the database
 *
 * @package		backend
 * @subpackage	crawler
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.0
 */
class BackendCrawlerCronjobCheckLinks extends BackendBaseCronjob
{
	private $insertWorkingLinks = false;

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
		$this->getData();
	}


	/**
	 * Get data from analytics
	 *
	 * @return	void
	 */
	private function getData()
	{
		// build our query
		$query = 'SELECT * FROM crawler_links AS c';

		// fetch the record
		$records = BackendModel::getDB(true)->getRecords($query);

		foreach ($records as $link) {

			// initialize
			$ch = curl_init();

			$values = array();
			$values['title'] = $link['title'];
			$values['module'] = $link['module'];
			$values['origin'] = $link['origin'];
			$values['external'] = $link['external'];

			$values['public_url'] = $link['public_url'];
			$values['private_url'] = $link['private_url'];

			if($link['external'] == 'N'){
				$values['url'] = SITE_URL . $link['url'];
			}else{
				$values['url'] = $link['url'];
			}

			// set the options, including the url
			curl_setopt($ch, CURLOPT_URL, $values['url']);

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

			// dead/faulty/non existing link?
			if (!$chinfo['http_code']) {
				BackendModel::getDB(true)->insert('crawler_results', $values);

			// 4xx, 5xx error
			} else if ($chinfo['http_code'] >= 400 && $chinfo['http_code'] < 600) {
				BackendModel::getDB(true)->insert('crawler_results', $values);

			if($this->insertWorkingLinks)
			{
				// 2xx, 3xx working
				} else if ($chinfo['http_code'] >= 200 && $chinfo['http_code'] < 400) {
					BackendModel::getDB(true)->insert('crawler_results', $values);
				}
			}
		}
	}
}

?>