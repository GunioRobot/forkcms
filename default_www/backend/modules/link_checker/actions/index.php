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

		// add refresh javascript
		$this->header->addJavascript('module.js', 'link_checker');

		// delete non used dead links
		BackendLinkCheckerModel::cleanup();

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

		// hide columns
		$this->dgAll->setColumnsHidden('item_id');

		// set column functions
		$this->dgAll->setColumnFunction(array('BackendLinkCheckerIndex', 'getDescription'), array('[description]'), 'description', true);
		$this->dgAll->setColumnFunction(array('BackendLinkCheckerIndex', 'getEditUrl'), array('[module]', '[item_id]', '[title]'), 'title', true);
		$this->dgAll->setColumnFunction(array('BackendLinkCheckerIndex', 'getModuleLabel'), array('[module]'), 'module', true);
		$this->dgAll->setColumnFunction(array('BackendLinkCheckerIndex', 'getTimeAgo'), array('[date_checked]'), 'date_checked', true);

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

		// hide columns
		$this->dgInternal->setColumnsHidden('item_id');

		// set column functions
		$this->dgInternal->setColumnFunction(array('BackendLinkCheckerIndex', 'getDescription'), array('[description]'), 'description', true);
		$this->dgInternal->setColumnFunction(array('BackendLinkCheckerIndex', 'getEditUrl'), array('[module]', '[item_id]', '[title]'), 'title', true);
		$this->dgInternal->setColumnFunction(array('BackendLinkCheckerIndex', 'getModuleLabel'), array('[module]'), 'module', true);
		$this->dgInternal->setColumnFunction(array('BackendLinkCheckerIndex', 'getTimeAgo'), array('[date_checked]'), 'date_checked', true);

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

		// hide columns
		$this->dgExternal->setColumnsHidden('item_id');

		// set column functions
		$this->dgExternal->setColumnFunction(array('BackendLinkCheckerIndex', 'getDescription'), array('[description]'), 'description', true);
		$this->dgExternal->setColumnFunction(array('BackendLinkCheckerIndex', 'getEditUrl'), array('[module]', '[item_id]', '[title]'), 'title', true);
		$this->dgExternal->setColumnFunction(array('BackendLinkCheckerIndex', 'getModuleLabel'), array('[module]'), 'module', true);
		$this->dgExternal->setColumnFunction(array('BackendLinkCheckerIndex', 'getTimeAgo'), array('[date_checked]'), 'date_checked', true);
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


	/**
	 * Column function to convert the http error code into a human readable message.
	 *
	 * @return	string
	 * @param $errorCode		The error code.
	 */
	public static function getDescription($errorCode)
	{
		// return the label for the error code
		return BL::msg('ErrorCode' . $errorCode);
	}


	/**
	 * Column function to convert the module into a label.
	 *
	 * @return	string
	 * @param $errorCode		The error code.
	 */
	public static function getModuleLabel($module)
	{
		// return the label for the module
		return ucfirst(BL::lbl(str_replace(' ', '', ucwords(str_replace('_', ' ', $module)))));
	}


	/**
	 * Column function to convert the item id into an edit url.
	 *
	 * @return	string
	 * @param $errorCode		The error code.
	 */
	public static function getEditUrl($module, $item_id, $title)
	{
		// each module has a specific edit/public url
		return '<a href="' . BackendLinkCheckerHelper::getModuleEditUrl($module) . $item_id . '">' . $title . '<a/>';
	}


	/**
	 * Column function to get the time ago since the link was checked.
	 *
	 * @return	string
	 * @param $date		The date the link was checked.
	 */
	public static function getTimeAgo($date)
	{
		return SpoonDate::getTimeAgo(strtotime($date), BL::getWorkingLanguage());
	}
}

?>