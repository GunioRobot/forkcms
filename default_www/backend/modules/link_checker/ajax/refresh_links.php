<?php

/**
 * This action will refresh the link checker module
 *
 * @package		backend
 * @subpackage	link_checker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.1
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

		// empty database
		$this->emptyDatabase();

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
			$datagrid->setSortingColumns(array('title', 'date_checked'), 'title');
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
			$datagrid->setSortingColumns(array('title', 'date_checked'), 'title');
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
			$datagrid->setSortingColumns(array('title', 'date_checked'), 'title');
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