<?php

/**
 * This is the index-action, it will display the overview of all links checked by the module
 *
 * @package		backend
 * @subpackage	link_checker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.1
 */
class BackendLinkCheckerIndex extends BackendBaseActionIndex
{
	/**
	 * Datagrids
	 *
	 * @var	BackendDataGridArray
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

		// add refresh javascript
		$this->header->addJS('module.js');

		// delete non used dead links
		BackendLinkCheckerHelper::cleanup();

		// load datagrids
		$this->loadDataGrids();

		// parse page
		$this->parse();

		// display the page
		$this->display();
	}


	/**
	 * Load All
	 *
	 * @return	void
	 */
	private function loadDataGridAll()
	{
		// fill datagrid with all the links
		$this->dgAll = new BackendDataGridArray(BackendLinkCheckerModel::getAll());

		// active tab
		$this->dgAll->setActiveTab('tabAll');

		// num items per page
		$this->dgAll->setPagingLimit(10);

		// sorting
		$this->dgAll->setSortingColumns(array('title', 'date_checked'), 'title');
		$this->dgAll->setSortParameter('desc');

		// add edit column
		$this->dgAll->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit', '[module]') . '&amp;id=[item_id]', BL::lbl('Edit'));

		// add module name column
		$this->dgAll->addColumn('module_name', ucfirst(BL::lbl('Module')), BL::lbl('Module'), '', BL::lbl('Module'), '', 2);

		// hide columns
		$this->dgAll->setColumnsHidden('item_id');

		// set column URLs
		$this->dgAll->setColumnURL('title', BackendModel::createURLForAction('edit', '[module]') . '&amp;id=[item_id]');

		// set column functions
		$this->dgAll->setColumnFunction(array('BackendLinkCheckerHelper', 'getDescription'), array('[description]'), 'description', true);
		$this->dgAll->setColumnFunction(array('BackendLinkCheckerHelper', 'getModuleLabel'), array('[module]'), 'module_name', true);
		$this->dgAll->setColumnFunction(array('BackendLinkCheckerHelper', 'getTimeAgo'), array('[date_checked]'), 'date_checked', true);
	}


	/**
	 * Load External
	 *
	 * @return	void
	 */
	private function loadDataGridExternal()
	{
		// fill datagrid with only the external links
		$this->dgExternal = new BackendDataGridArray(BackendLinkCheckerModel::getExternal());

		// num items per page
		$this->dgExternal->setPagingLimit(10);

		// sorting
		$this->dgExternal->setSortingColumns(array('title', 'date_checked'), 'title');

		$this->dgExternal->setSortParameter('desc');

		// hide columns
		$this->dgExternal->setColumnsHidden('item_id', 'module');

		// add edit column
		$this->dgExternal->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit', '[module]') . '&amp;id=[item_id]', BL::lbl('Edit'));

		// add module name column
		$this->dgExternal->addColumn('module_name', ucfirst(BL::lbl('Module')), BL::lbl('Module'), '', BL::lbl('Module'), '', 2);

		// set column URLs
		$this->dgExternal->setColumnURL('title', BackendModel::createURLForAction('edit', '[module]') . '&amp;id=[item_id]');

		// set column functions
		$this->dgExternal->setColumnFunction(array('BackendLinkCheckerHelper', 'getDescription'), array('[description]'), 'description', true);
		$this->dgExternal->setColumnFunction(array('BackendLinkCheckerHelper', 'getModuleLabel'), array('[module]'), 'module_name', true);
		$this->dgExternal->setColumnFunction(array('BackendLinkCheckerHelper', 'getTimeAgo'), array('[date_checked]'), 'date_checked', true);
	}


	/**
	 * Load Internal
	 *
	 * @return	void
	 */
	private function loadDataGridInternal()
	{
		// fill datagrid with only the internal links
		$this->dgInternal = new BackendDataGridArray(BackendLinkCheckerModel::getInternal());

		// num items per page
		$this->dgInternal->setPagingLimit(10);

		// sorting
		$this->dgInternal->setSortingColumns(array('title', 'date_checked'), 'title');
		$this->dgInternal->setSortParameter('desc');

		// add edit column
		$this->dgInternal->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit', '[module]') . '&amp;id=[item_id]', BL::lbl('Edit'));

		// add module name column
		$this->dgInternal->addColumn('module_name', ucfirst(BL::lbl('Module')), BL::lbl('Module'), '', BL::lbl('Module'), '', 2);

		// hide columns
		$this->dgInternal->setColumnsHidden('item_id');

		// set column URLs
		$this->dgInternal->setColumnURL('title', BackendModel::createURLForAction('edit', '[module]') . '&amp;id=[item_id]');

		// set column functions
		$this->dgInternal->setColumnFunction(array('BackendLinkCheckerHelper', 'getDescription'), array('[description]'), 'description', true);
		$this->dgInternal->setColumnFunction(array('BackendLinkCheckerHelper', 'getModuleLabel'), array('[module]'), 'module_name', true);
		$this->dgInternal->setColumnFunction(array('BackendLinkCheckerHelper', 'getTimeAgo'), array('[date_checked]'), 'date_checked', true);
	}


	/**
	 * Load the datagrids
	 *
	 * @return	void
	 */
	private function loadDataGrids()
	{
		// load all links
		$this->loadDataGridAll();

		// load external links
		$this->loadDataGridExternal();

		// load internal links
		$this->loadDataGridInternal();
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