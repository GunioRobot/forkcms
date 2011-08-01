<?php

/**
 * This is the index-action, it will display the overview of slideshows
 *
 * @package		backend
 * @subpackage	slideshows
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.5
 */
class BackendSlideshowsIndex extends BackendBaseActionIndex
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		// call parent, this will probably add some general CSS/JS or other required files
		parent::execute();

		// load datagrid
		$this->loadDataGrid();

		// parse page
		$this->parse();

		// display the page
		$this->display();
	}


	/**
	 * Loads the datagrids
	 */
	private function loadDataGrid()
	{
		// create datagrid
		$this->dataGrid = new BackendDataGridDB(BackendSlideshowsModel::QRY_DATAGRID_BROWSE, BL::getWorkingLanguage());

		// sorting columns
		$this->dataGrid->setSortingColumns(array('name'), 'name');
		$this->dataGrid->setSortParameter('desc');

		// column functions
		$this->dataGrid->addColumn('images', null, BL::lbl('Images'));
		$this->dataGrid->setColumnFunction(array(__CLASS__, 'setImagesLink'), array('[module]', '[id]'), 'images');
		$this->dataGrid->setColumnAttributes('images', array('style' => 'width: 1%;'));

		// add column
		$this->dataGrid->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit') . '&amp;id=[id]', BL::lbl('Edit'));

		// add attributes, so the inline editing has all the needed data
		$this->dataGrid->setColumnAttributes('name', array('data-id' => '{id:[id]}'));
	}


	/**
	 * Parse & display the page
	 */
	private function parse()
	{
		$this->tpl->assign('dataGrid', ($this->dataGrid->getNumResults() != 0) ? $this->dataGrid->getContent() : false);
	}


	/**
	 * Datagrid method, sets a link to the images overview for the slideshow if
	 * a module was not specified
	 *
	 * @return	string
	 * @param	string $module		The module string (which shouldn't be an empty one).
	 * @param	int $slideshowID	The slideshow ID used in the URL parameters.
	 */
	public static function setImagesLink($module, $slideshowID)
	{
		if($module == '')
		{
			$imagesLink = BackendModel::createURLForAction('images') . '&slideshow_id=' . $slideshowID;

			return '<a class="button icon iconEdit linkButton" href="' . $imagesLink . '">
						<span>' . BL::lbl('ManageImages') . '</span>
					</a>';
		}
	}
}

?>