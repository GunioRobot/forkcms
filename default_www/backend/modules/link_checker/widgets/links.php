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
	 * Datagrids
	 *
	 * @var	BackendDataGridDB
	 */
	private $dgAll;


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

		// load datagrids
		$this->loadDataGrid();

		// parse page
		$this->parse();

		// display the page
		$this->display();
	}


	/**
	 * Load the datagrids
	 *
	 * @return	void
	 */
	private function loadDataGrid()
	{
		$this->dgAll = new BackendDataGridArray(BackendLinkCheckerModel::getAll());

		// set columns hidden
		$this->dgAll->setColumnsHidden(array('title', 'description', 'item_id', 'date_checked'));

		// set column functions
		$this->dgAll->setColumnFunction(array('BackendLinkCheckerWidgetLinks', 'getModuleLabel'), array('[module]'), 'module', true);
	}


	/**
	 * Parse stuff into the template
	 *
	 * @return	void
	 */
	private function parse()
	{
		// all datagrid and num results
		$this->tpl->assign('dgAll', ($this->dgAll->getNumResults() != 0) ? $this->dgAll->getContent() : false);

		// set moderation highlight message
		$this->tpl->assign('numDeadLinksFound', $this->dgAll->getNumResults());
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
}

?>