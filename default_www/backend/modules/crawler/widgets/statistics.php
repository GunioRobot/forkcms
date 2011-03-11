<?php

/**
 * This is the crawler widget
 *
 * @package		backend
 * @subpackage	crawler
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.0
 */
class BackendCrawlerWidgetStatistics extends BackendBaseWidget
{

	/**
	 * the default group ID
	 *
	 * @var	int
	 */
	private $groupId;


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

		// display
		$this->display();
	}


	/**
	 * Load the datagrid for statistics
	 *
	 * @return	void
	 */
	private function loadAll()
	{
		// fetch the latest mailing
		$all = BackendCrawlerModel::getAll();

		// there are some results
		if(!empty($all))
		{
			// get the datagrid
			$datagrid = new BackendDataGridArray($all);

			$datagrid->setSortingColumns(array('title', 'module'));

			$datagrid->setColumnURL('title', '[public_url]');
			$datagrid->addColumn('edit', null, BL::lbl('Edit'), '[private_url]', BL::lbl('Edit'));

			$datagrid->setColumnsHidden(array('code', 'public_url', 'private_url'));

			// no pagination
			$datagrid->setPaging(false);

			// parse the datagrid
			$this->tpl->assign('dgCrawlerAll', $datagrid->getContent());
		}
	}


	/**
	 * Load the datagrid for subscriptions
	 *
	 * @return	void
	 */
	private function loadInternal()
	{
		// fetch the latest mailing
		$all = BackendCrawlerModel::getInternal();

		// there are some results
		if(!empty($all))
		{
			// get the datagrid
			$datagrid = new BackendDataGridArray($all);

			$datagrid->setSortingColumns(array('title'));

			$datagrid->setColumnURL('title', '#');
			$datagrid->setColumnsHidden(array('code', 'module'));

			$datagrid->addColumn('edit', null, BL::lbl('Edit'), '#', BL::lbl('Edit'));

			// no pagination
			$datagrid->setPaging(false);

			// parse the datagrid
			$this->tpl->assign('dgCrawlerInternal', $datagrid->getContent());
		}
	}


	/**
	 * Load the datagrid for unsubscriptions
	 *
	 * @return	void
	 */
	private function loadExternal()
	{
		// fetch the latest mailing
		$all = BackendCrawlerModel::getExternal();

		// there are some results
		if(!empty($all))
		{
			// get the datagrid
			$datagrid = new BackendDataGridArray($all);

			$datagrid->setSortingColumns(array('title'));

			$datagrid->setColumnURL('title', '#');
			$datagrid->setColumnsHidden(array('code', 'module'));

			$datagrid->addColumn('edit', null, BL::lbl('Edit'), '#', BL::lbl('Edit'));

			// no pagination
			$datagrid->setPaging(false);

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