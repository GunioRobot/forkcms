<?php

/**
 * This is the add action, it will display a form to add a slideshow.
 *
 * @package		backend
 * @subpackage	slideshows
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.5
 */
class BackendSlideshowsAdd extends BackendBaseActionEdit
{
	/**
	 * Execute the action	a:1:{i:0;s:4:"blog";}
	 */
	public function execute()
	{
		parent::execute();

		$this->loadForm();
		$this->validateForm();
		$this->parse();
		$this->display();
	}


	/**
	 * Load the form
	 */
	private function loadForm()
	{
		$this->frm = new BackendForm('add');

		$this->frm->addTexts('name', 'width', 'height');
		$this->frm->addDropdown('type', BackendSlideshowsModel::getTypesAsPairs());
		$this->frm->addDropdown('module', BackendSlideshowsHelper::getSupportedModules());
		$this->frm->addDropdown('methods');
	}


	/**
	 * Parse the form
	 */
	protected function parse()
	{
		parent::parse();

		if(BackendSlideshowsHelper::getModules())
		{
			$this->tpl->assign('modules', true);
		}
	}


	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// shorten fields
			$module = $this->frm->getField('module');
			$width = $this->frm->getField('width');
			$height = $this->frm->getField('height');

			// validate fields
			$this->frm->getField('name')->isFilled(BL::err('NameIsRequired'));
			if($module->getValue() === null || $module->getValue() == '0')
			{
				if($width->isFilled(BL::err('FieldIsRequired')))
				{
					$width->isNumeric(BL::err('NumericCharactersOnly'));
				}
				if($height->isFilled(BL::err('FieldIsRequired')))
				{
					$height->isNumeric(BL::err('NumericCharactersOnly'));
				}
			}

			// the method is filled by javascript, so we have to fetch it from POST
			$method = isset($_POST['methods']) ? $_POST['methods'] : null;

			// form validated with no errors
			if($this->frm->isCorrect())
			{
				// build slideshow record to insert
				$item['language'] = BL::getWorkingLanguage();
				$item['name'] = $this->frm->getField('name')->getValue();
				$item['type_id'] = $this->frm->getField('type')->getValue();
				$item['module'] = ($module->getValue() == '0') ? null : $module->getValue();
				if($item['module'] !== null)
				{
					$item['dataset_id'] = $method;
				}
				else
				{
					$item['width'] = $width->getValue();
					$item['height'] = $height->getValue();
				}

				// save the item
				$id = BackendSlideshowsModel::save($item);

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('index') . '&report=edited&var=' . urlencode($item['name']) . '&highlight=row-' . $id);
			}
		}
	}
}

?>