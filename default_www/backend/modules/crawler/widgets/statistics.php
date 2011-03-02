<?php

/**
 * This is the classic fork cayenne mailmotor widget
 *
 * @package		backend
 * @subpackage	mailmotor
 *
 * @author		Dave Lens <dave@netlash.com>
 * @author		Tijs Verkoyen <tijs@sumocoders.be>
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