<?php

/*
	@todo	I'd put all methods starting with "load" into execute(), and everything involving $this->tpl->assign() into the parse() method,
			because $this->tpl->assign() essentially parses content into the template.
*/

/**
 * This is the linkchecker widget
 *
 * @package		backend
 * @subpackage	linkchecker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.0
 */
class BackendLinkCheckerWidgetLinks extends BackendBaseWidget
{
	/**
	 * Execute the widget
	 *
	 * @return	void
	 */
	public function execute()
	{
		// set column
		$this->setColumn('left');

		// parse
		$this->parse();


		// add refresh javascript
		$this->header->addJavascript('dashboard.js', 'link_checker');


		// display
		$this->display();
	}


	/**
	 * Load the datagrid for all links
	 *
	 * @return	void
	 */
	private function loadAll()
	{
		// fetch all links
		$all = BackendLinkCheckerModel::getAll();

		// there are some results
		if(!empty($all))
		{
			// get the datagrid
			$datagrid = new BackendDataGridArray($all);

			// set tab active
			$datagrid->setActiveTab('tabCrawlerAll');

			// set paging
			$datagrid->setPaging(true);
			$datagrid->setPagingLimit(10);

			// set sorting column
			$datagrid->setSortingColumns(array('module'));

			// set columns hidden
			$datagrid->setColumnsHidden(array('title', 'description', 'public_url', 'private_url'));

			// parse the datagrid
			$this->tpl->assign('dgCrawlerAll', $datagrid->getContent());
		}
	}


	/**
	 * Load the datagrid for internal links
	 *
	 * @return	void
	 */
	private function loadInternal()
	{
		// fetch internal links
		$all = BackendLinkCheckerModel::getInternal();

		// there are some results
		if(!empty($all))
		{
			// get the datagrid
			$datagrid = new BackendDataGridArray($all);

			// set tab active
			$datagrid->setActiveTab('tabCrawlerInternal');

			// set paging
			$datagrid->setPaging(true);
			$datagrid->setPagingLimit(10);

			// set sorting column
			$datagrid->setSortingColumns(array('module'));

			// set columns hidden
			$datagrid->setColumnsHidden(array('title', 'description', 'public_url', 'private_url'));

			// parse the datagrid
			$this->tpl->assign('dgCrawlerInternal', $datagrid->getContent());
		}
	}


	/**
	 * Load the datagrid for external links
	 *
	 * @return	void
	 */
	private function loadExternal()
	{
		// fetch external links
		$all = BackendLinkCheckerModel::getExternal();

		// there are some results
		if(!empty($all))
		{
			// get the datagrid
			$datagrid = new BackendDataGridArray($all);

			// set tab active
			$datagrid->setActiveTab('tabCrawlerExternal');

			// set paging
			$datagrid->setPaging(true);
			$datagrid->setPagingLimit(10);

			// set sorting column
			$datagrid->setSortingColumns(array('module'));

			// set columns hidden
			$datagrid->setColumnsHidden(array('title', 'description', 'public_url', 'private_url'));

			// parse the datagrid
			$this->tpl->assign('dgCrawlerExternal', $datagrid->getContent());
		}
	}


	/**
	 * Parse stuff into the template
	 *
	 * @return	void
	 */
	private function parse()
	{
		$this->loadAll();
		$this->loadInternal();
		$this->loadExternal();
	}
}

?>