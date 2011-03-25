<?php

/**
 * This edit-action will refresh the link checker widget using Ajax
 *
 * @package		backend
 * @subpackage	linkchecker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.0
 */
class BackendLinkCheckerAjaxRefreshLinks extends BackendBaseAJAXAction
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
		// call parent, this will probably add some general CSS/JS or other required files
		parent::execute();

		// cleanup database
		$this->cleanupDatabase();

		// require the helper class
		require_once BACKEND_MODULES_PATH . '/link_checker/engine/helper.php';

		// get data
		$this->getLinks();

		// check data
		$this->checkLinks();

		// get html
		$allHtml = $this->parseAll();
		$internalHtml = $this->parseInternal();
		$externalHtml = $this->parseExternal();

		// return status and data
		$this->output(self::OK, array('status' => 'success', 'allHtml' => $allHtml, 'internalHtml' => $internalHtml, 'externalHtml' => $externalHtml, 'message' => 'Data has been retrieved.'));
	}


	/**
	 * Cleanup database
	 *
	 * @return	void
	 */
	private function cleanupDatabase()
	{
		// cleanup pages
		BackendLinkCheckerModel::cleanup();
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


	/**
	 * Parse into template
	 *
	 * @return	void
	 */
	private function parseAll()
	{
		// get results
		$results = BackendLinkCheckerModel::getAll();

		// there are some results
		if(!empty($results))
		{
			// get the datagrid
			$datagrid = new BackendDataGridArray($results);

			// set paging
			$datagrid->setPaging(true);
			$datagrid->setPagingLimit(10);

			// set sorting column
			$datagrid->setSortingColumns(array('module'));

			// set columns hidden
			$datagrid->setColumnsHidden(array('title', 'description', 'private_url'));
		}

		// parse the datagrid
		return (!empty($results) ? '<div class="datagridHolder">' . $datagrid->getContent() . '</div>' : '<p>' . BL::msg('NoLinks') . '</p>');
	}


	/**
	 * Parse into template
	 *
	 * @return	void
	 */
	private function parseInternal()
	{
		// get results
		$results = BackendLinkCheckerModel::getInternal();

		// there are some results
		if(!empty($results))
		{
			// get the datagrid
			$datagrid = new BackendDataGridArray($results);

			// set paging
			$datagrid->setPaging(true);
			$datagrid->setPagingLimit(10);

			// set sorting column
			$datagrid->setSortingColumns(array('module'));

			// set columns hidden
			$datagrid->setColumnsHidden(array('title', 'description', 'private_url'));
		}

		// parse the datagrid
		return (!empty($results) ? '<div class="datagridHolder">' . $datagrid->getContent() . '</div>' : '<p>' . BL::msg('NoLinks') . '</p>');
	}


	/**
	 * Parse into template
	 *
	 * @return	void
	 */
	private function parseExternal()
	{
		// get results
		$results = BackendLinkCheckerModel::getExternal();

		// there are some results
		if(!empty($results))
		{
			// get the datagrid
			$datagrid = new BackendDataGridArray($results);

			// set paging
			$datagrid->setPaging(true);
			$datagrid->setPagingLimit(10);

			// set sorting column
			$datagrid->setSortingColumns(array('module'));

			// set columns hidden
			$datagrid->setColumnsHidden(array('title', 'description', 'private_url'));
		}

		// parse the datagrid
		return (!empty($results) ? '<div class="datagridHolder">' . $datagrid->getContent() . '</div>' : '<p>' . BL::msg('NoLinks') . '</p>');
	}
}

?>