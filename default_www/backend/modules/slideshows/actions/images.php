<?php

/**
 * This is the images-action, it will display the overview of images for a specific slideshow
 *
 * @package		backend
 * @subpackage	slideshows
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.5
 */
class BackendSlideshowsImages extends BackendBaseActionIndex
{
	/**
	 * The slideshow record
	 *
	 * @var	array
	 */
	private $slideshow = array();


	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('slideshow_id', 'int');

		parent::execute();

		if($this->id !== null && BackendSlideshowsModel::exists($this->id))
		{
			$this->getData();
			$this->loadDataGrid();
			$this->parse();
			$this->display();
		}

		else
		{
			// @todo nice redirect
			$this->redirect(BackendModel::createURLForAction('index') . '&error=exists');
		}
	}


	/**
	 * Gets all necessary data
	 */
	private function getData()
	{
		$this->slideshow = BackendSlideshowsModel::get($this->id);
	}


	/**
	 * Loads the datagrids
	 */
	private function loadDataGrid()
	{
		// create datagrid
		$this->dataGrid = new BackendDataGridDB(BackendSlideshowsModel::QRY_DATAGRID_BROWSE_IMAGES, $this->id);
		$this->dataGrid->setColumnHidden('slideshow_id');

		// sorting columns
		$this->dataGrid->setSortingColumns(array('title', 'sequence'), 'sequence');
		$this->dataGrid->setSortParameter('asc');

		// add column
		$this->dataGrid->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit_image') . '&amp;id=[id]&amp;slideshow_id=[slideshow_id]', BL::lbl('Edit'));

		// enable drag and drop
		$this->dataGrid->enableSequenceByDragAndDrop();

		// show image thumbnail
		$imageLink = FRONTEND_FILES_URL . '/slideshows/[slideshow_id]/64x64';
		$this->dataGrid->setColumnFunction(array('BackendDataGridFunctions', 'showImage'), array($imageLink, '[filename]'), 'filename' );
		$this->dataGrid->setColumnAttributes('filename', array('class' => 'thumbnail'));

		// add the multicheckbox column
		$this->dataGrid->addColumn('checkbox', '<span class="checkboxHolder block"><input type="checkbox" name="toggleChecks" value="toggleChecks" />', '<input type="checkbox" name="id[]" value="[id]" class="inputCheckbox" /></span>');
		$this->dataGrid->setColumnsSequence('checkbox');

		// add mass action dropdown
		$ddmMassAction = new SpoonFormDropdown('action', array('delete' => BL::lbl('Delete')), 'delete');
		$this->dataGrid->setMassAction($ddmMassAction);

		// add attributes, so the inline editing has all the needed data
		$this->dataGrid->setColumnAttributes('title', array('data-id' => '{id:[id]}'));
	}


	/**
	 * Parse & display the page
	 */
	private function parse()
	{
		$this->tpl->assign('dataGrid', ($this->dataGrid->getNumResults() != 0) ? $this->dataGrid->getContent() : false);
		$this->tpl->assign('slideshow', $this->slideshow);
	}
}

?>