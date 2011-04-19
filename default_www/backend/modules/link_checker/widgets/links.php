<?php

/**
 * This is the linkchecker widget
 *
 * @package		backend
 * @subpackage	link_checker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.1
 */
class BackendLinkCheckerWidgetLinks extends BackendBaseWidget
{
	/**
	 * Datagrid
	 *
	 * @var	BackendDataGridDB
	 */
	private $dgRecent;


	/**
	 * Execute the widget
	 *
	 * @return	void
	 */
	public function execute()
	{
		// set column
		$this->setColumn('left');

		// delete non used dead links
		BackendLinkCheckerHelper::cleanup();

		// load datagrid
		$this->loadDataGrid();

		// parse page
		$this->parse();

		// display the page
		$this->display();
	}


	/**
	 * Load the datagrid
	 *
	 * @return	void
	 */
	private function loadDataGrid()
	{
		// new data grid (only show the 5 most recent links)
		$this->dgRecent = new BackendDataGridArray(BackendLinkCheckerModel::getMostRecent(5));

		// set columns hidden
		$this->dgRecent->setColumnsHidden(array('title', 'description', 'item_id', 'date_checked'));

		// set column functions
		$this->dgRecent->setColumnFunction(array('BackendLinkCheckerHelper', 'getModuleLabel'), array('[module]'), 'module', true);
	}


	/**
	 * Parse stuff into the template
	 *
	 * @return	void
	 */
	private function parse()
	{
		// all datagrid and num results
		$this->tpl->assign('dgRecent', ($this->dgRecent->getNumResults() != 0) ? $this->dgRecent->getContent() : false);

		// set moderation highlight message (count all dead links)
		$this->tpl->assign('numDeadLinksFound', count(BackendLinkCheckerModel::getAll()));
	}
}

?>