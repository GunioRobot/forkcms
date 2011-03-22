<?php

/**
 * This is the index-action, it will display the overview of all links checked by the linkchecker
 *
 * @package		backend
 * @subpackage	linkchecker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.0
 */
class BackendLinkCheckerIndex extends BackendBaseActionIndex
{
	/**
	 * Datagrids
	 *
	 * @var	BackendDataGridDB
	 */
	private $dgAll, $dgInternal, $dgExternal;

	/**
	 * Execute the action
	 *
	 * @return	void
	 */
	public function execute()
	{
		// call parent, this will probably add some general CSS/JS or other required files
		parent::execute();

		// load datagrids
		$this->loadDataGrids();

		// parse page
		$this->parse();

		// display the page
		$this->display();
	}


	/**
	 * Loads the datagrids
	 *
	 * @return	void
	 */
	private function loadDataGrids()
	{
		/*
		 * Datagrid for all links.
		 */

		$this->dgAll = new BackendDataGridArray(BackendLinkCheckerModel::getAll());

		// active tab
		$this->dgAll->setActiveTab('tabAll');

		// num items per page
		$this->dgAll->setPagingLimit(10);

		// sorting
		$this->dgAll->setSortingColumns(array('title', 'module', 'description'), 'title');
		$this->dgAll->setSortParameter('desc');

		// set colum URLs
		$this->dgAll->setColumnURL('title', '[public_url]');

		// add column
		$this->dgAll->addColumn('edit', null, BL::lbl('Edit'), '[private_url]', BL::lbl('Edit'));

		// hide columns
		$this->dgAll->setColumnsHidden('public_url', 'private_url');

		/*
		 * Datagrid for internal links only.
		 */

		$this->dgInternal = new BackendDataGridArray(BackendLinkCheckerModel::getInternal());

		// active tab
		$this->dgInternal->setActiveTab('tabInternal');

		// num items per page
		$this->dgInternal->setPagingLimit(10);

		// sorting
		$this->dgInternal->setSortingColumns(array('title', 'module', 'description'), 'title');
		$this->dgInternal->setSortParameter('desc');

		// set colum URLs
		$this->dgInternal->setColumnURL('title', '[public_url]');

		// add column
		$this->dgInternal->addColumn('edit', null, BL::lbl('Edit'), '[private_url]', BL::lbl('Edit'));

		// hide columns
		$this->dgInternal->setColumnsHidden('public_url', 'private_url');

		/*
		 * Datagrid for external links only.
		 */

		$this->dgExternal = new BackendDataGridArray(BackendLinkCheckerModel::getExternal());

		// active tab
		$this->dgExternal->setActiveTab('tabExternal');

		// num items per page
		$this->dgExternal->setPagingLimit(10);

		// sorting
		$this->dgExternal->setSortingColumns(array('title', 'module', 'description'), 'title');
		$this->dgExternal->setSortParameter('desc');

		// set colum URLs
		$this->dgExternal->setColumnURL('title', '[public_url]');

		// add column
		$this->dgExternal->addColumn('edit', null, BL::lbl('Edit'), '[private_url]', BL::lbl('Edit'));

		// hide columns
		$this->dgExternal->setColumnsHidden('public_url', 'private_url');
	}


	/**
	 * Parse & display the page
	 *
	 * @return	void
	 */
	private function parse()
	{
		// all datagrid and num results
		$this->tpl->assign('dgAll', ($this->dgAll->getNumResults() != 0) ? $this->dgAll->getContent() : false);
		$this->tpl->assign('numAll', $this->dgAll->getNumResults());

		// internal datagrid and num results
		$this->tpl->assign('dgInternal', ($this->dgInternal->getNumResults() != 0) ? $this->dgInternal->getContent() : false);
		$this->tpl->assign('numInternal', $this->dgInternal->getNumResults());

		// external datagrid and num results
		$this->tpl->assign('dgExternal', ($this->dgExternal->getNumResults() != 0) ? $this->dgExternal->getContent() : false);
		$this->tpl->assign('numExternal', $this->dgExternal->getNumResults());
	}
}

?>