<?php

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
	 * Load the datagrid for all links
	 *
	 * @return	void
	 */
	private function loadAll()
	{
		// fetch the latest mailing
		$all = BackendLinkCheckerModel::getAll();

		// there are some results
		if(!empty($all))
		{
			// get the datagrid
			$datagrid = new BackendDataGridArray($all);

			$datagrid->setSortingColumns(array('module'));

			$datagrid->setColumnsHidden(array('title', 'code', 'public_url', 'private_url'));

			// no pagination
			$datagrid->setPaging(false);

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
		// fetch the latest mailing
		$all = BackendLinkCheckerModel::getInternal();

		// there are some results
		if(!empty($all))
		{
			// get the datagrid
			$datagrid = new BackendDataGridArray($all);

			$datagrid->setSortingColumns(array('module'));

			$datagrid->setColumnsHidden(array('title', 'code', 'public_url', 'private_url'));

			// no pagination
			$datagrid->setPaging(false);

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
		// fetch the latest mailing
		$all = BackendLinkCheckerModel::getExternal();

		// there are some results
		if(!empty($all))
		{
			// get the datagrid
			$datagrid = new BackendDataGridArray($all);

			$datagrid->setSortingColumns(array('module'));


			$datagrid->setColumnsHidden(array('title', 'code', 'public_url', 'private_url'));

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