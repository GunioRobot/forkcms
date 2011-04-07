<?php

/**
 * This action will refresh the link checker module
 *
 * @package		backend
 * @subpackage	link_checker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.0
 */
class BackendLinkCheckerAjaxRefreshLinks extends BackendBaseAJAXAction
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
			if((bool) BackendModel::getModuleSetting('link_checker', 'cache_dead_links'))
			{
				// all the dead links from the previous run
				$prevDeadLinks = BackendLinkCheckerModel::getDeadUrls();

				// all the dead links we are about to re-insert
				$knownDeadLinks = array();

				// as it is stupid to check links we already know to be dead, we remove the dead ones!
				// loop all previously known dead links
				if(isset($prevDeadLinks) && count($prevDeadLinks) > 0)
				{
					// new array
					$tempAllLinks = array();

					// loop the links we found in the database
					foreach($this->allLinks as $link)
					{
						// if the link is found in the array with dead links
						if(in_array($link['url'], $prevDeadLinks))
						{
							// get the dead link
							$deadLink = BackendLinkCheckerModel::getDeadUrl($link['url']);

							// check if the link isn't too old
							if((time() - strtotime($deadLink['date_checked'])) > (int) BackendModel::getModuleSetting('link_checker', 'cache_time'))
							{
								// time to re-check this one
								$tempAllLinks[] = $link;
							}
							else
							{

								// add it to the list that we are about to re-insert without re-checking the http status
								$knownDeadLinks[] = $deadLink;
							}
						}
						else
						{
							// we don't know if the link is dead, copy it to the array with links we need to check
							$tempAllLinks[] = $link;
						}
					}

					// copy the temp array back to the working array
					$this->allLinks = $tempAllLinks;
				}
			}

			// empty database database
			$this->emptyDatabase();


			if((bool) BackendModel::getModuleSetting('link_checker', 'cache_dead_links'))
			{
				// do we have dead links that we already knew to be deads?
				if(isset($knownDeadLinks) && count($knownDeadLinks) > 0)
				{
					// yes, insert them in the database
					BackendLinkCheckerModel::insertLinks($knownDeadLinks);
				}
			}

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
		$this->output(self::OK, array('status' => 'success', 'allHtml' => $allHtml, 'internalHtml' => $internalHtml, 'externalHtml' => $externalHtml, 'numAll' => count(BackendLinkCheckerModel::getAll()), 'numInternal' => count(BackendLinkCheckerModel::getInternal()), 'numExternal' => count(BackendLinkCheckerModel::getExternal()), 'message' => 'Links have been checked.'));
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
			$datagrid->setSortingColumns(array('title'), 'title');
			$datagrid->setSortParameter('desc');

			// add edit column
			$datagrid->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit', '[module]') . '&amp;id=[item_id]', BL::lbl('Edit'));

			// add module name column
			$datagrid->addColumn('module_name', ucfirst(BL::lbl('Module')), BL::lbl('Module'), '', BL::lbl('Module'), '', 2);

			// hide columns
			$datagrid->setColumnsHidden('item_id');

			// set column URLs
			$datagrid->setColumnURL('title', BackendModel::createURLForAction('edit', '[module]') . '&amp;id=[item_id]');

			// set column functions
			$datagrid->setColumnFunction(array('BackendLinkCheckerHelper', 'getDescription'), array('[description]'), 'description', true);
			$datagrid->setColumnFunction(array('BackendLinkCheckerHelper', 'getModuleLabel'), array('[module]'), 'module_name', true);
			$datagrid->setColumnFunction(array('BackendLinkCheckerHelper', 'getTimeAgo'), array('[date_checked]'), 'date_checked', true);


			// rename the NAMED_APPLICATION from 'backend_ajax' to 'backend',
			// otherwise we have a non working link/action in the backend

			// first rename the edit column
			$namedApplicationOld = (string) $datagrid->getColumn('edit')->getValue();
			$namedApplicationNew = str_replace('backend_ajax', 'backend', $namedApplicationOld);
			$datagrid->getColumn('edit')->setValue($namedApplicationNew, true);

			// then edit the title column
			$namedApplicationOld = (string) $datagrid->getColumn('title')->getURL();
			$namedApplicationNew = str_replace('backend_ajax', 'backend', $namedApplicationOld);
			$datagrid->getColumn('title')->setURL($namedApplicationNew);

			// fix the datagrid navigation
			$namedApplicationOld = (string) $datagrid->getURL();
			$namedApplicationNew = str_replace('backend_ajax', 'backend', $namedApplicationOld);
			$datagrid->setURL($namedApplicationNew, false);
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
			$datagrid->setSortingColumns(array('title'), 'title');
			$datagrid->setSortParameter('desc');

			// add edit column
			$datagrid->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit', '[module]') . '&amp;id=[item_id]', BL::lbl('Edit'));

			// add module name column
			$datagrid->addColumn('module_name', ucfirst(BL::lbl('Module')), BL::lbl('Module'), '', BL::lbl('Module'), '', 2);

			// hide columns
			$datagrid->setColumnsHidden('item_id');

			// set column URLs
			$datagrid->setColumnURL('title', BackendModel::createURLForAction('edit', '[module]') . '&amp;id=[item_id]');

			// set column functions
			$datagrid->setColumnFunction(array('BackendLinkCheckerHelper', 'getDescription'), array('[description]'), 'description', true);
			$datagrid->setColumnFunction(array('BackendLinkCheckerHelper', 'getModuleLabel'), array('[module]'), 'module_name', true);
			$datagrid->setColumnFunction(array('BackendLinkCheckerHelper', 'getTimeAgo'), array('[date_checked]'), 'date_checked', true);


			// rename the NAMED_APPLICATION from 'backend_ajax' to 'backend',
			// otherwise we have a non working link/action in the backend

			// fix the edit column
			$namedApplicationOld = (string) $datagrid->getColumn('edit')->getValue();
			$namedApplicationNew = str_replace('backend_ajax', 'backend', $namedApplicationOld);
			$datagrid->getColumn('edit')->setValue($namedApplicationNew, true);

			// fix the title column
			$namedApplicationOld = (string) $datagrid->getColumn('title')->getURL();
			$namedApplicationNew = str_replace('backend_ajax', 'backend', $namedApplicationOld);
			$datagrid->getColumn('title')->setURL($namedApplicationNew);

			// fix the datagrid navigation
			$namedApplicationOld = (string) $datagrid->getURL();
			$namedApplicationNew = str_replace('backend_ajax', 'backend', $namedApplicationOld);
			$datagrid->setURL($namedApplicationNew, false);
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
			$datagrid->setSortingColumns(array('title'), 'title');
			$datagrid->setSortParameter('desc');

			// add edit column
			$datagrid->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit', '[module]') . '&amp;id=[item_id]', BL::lbl('Edit'));

			// add module name column
			$datagrid->addColumn('module_name', ucfirst(BL::lbl('Module')), BL::lbl('Module'), '', BL::lbl('Module'), '', 2);

			// hide columns
			$datagrid->setColumnsHidden('item_id');

			// set column URLs
			$datagrid->setColumnURL('title', BackendModel::createURLForAction('edit', '[module]') . '&amp;id=[item_id]');

			// set column functions
			$datagrid->setColumnFunction(array('BackendLinkCheckerHelper', 'getDescription'), array('[description]'), 'description', true);
			$datagrid->setColumnFunction(array('BackendLinkCheckerHelper', 'getModuleLabel'), array('[module]'), 'module_name', true);
			$datagrid->setColumnFunction(array('BackendLinkCheckerHelper', 'getTimeAgo'), array('[date_checked]'), 'date_checked', true);


			// rename the NAMED_APPLICATION from 'backend_ajax' to 'backend',
			// otherwise we have a non working link/action in the backend

			// first rename the edit column
			$namedApplicationOld = (string) $datagrid->getColumn('edit')->getValue();
			$namedApplicationNew = str_replace('backend_ajax', 'backend', $namedApplicationOld);
			$datagrid->getColumn('edit')->setValue($namedApplicationNew, true);

			// then edit the title column
			$namedApplicationOld = (string) $datagrid->getColumn('title')->getURL();
			$namedApplicationNew = str_replace('backend_ajax', 'backend', $namedApplicationOld);
			$datagrid->getColumn('title')->setURL($namedApplicationNew);

			// fix the datagrid navigation
			$namedApplicationOld = (string) $datagrid->getURL();
			$namedApplicationNew = str_replace('backend_ajax', 'backend', $namedApplicationOld);
			$datagrid->setURL($namedApplicationNew, false);
		}

		// parse the datagrid
		return (!empty($results) ? '<div class="datagridHolder">' . $datagrid->getContent() . '</div>' : '<p>' . BL::msg('NoLinks') . '</p>');
	}
}

?>