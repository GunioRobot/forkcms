<?php

/**
 * This is the edit action, it will display a form to edit an existing slideshow.
 *
 * @package		backend
 * @subpackage	slideshows
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.5
 */
class BackendSlideshowsEdit extends BackendBaseActionEdit
{
	/**
	 * Execute the action
	 *
	 * @return	void
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');

		// does the item exist
		if($this->id !== null && BackendSlideshowsModel::exists($this->id))
		{
			parent::execute();

			$this->getData();
			$this->loadForm();
			$this->validateForm();
			$this->parse();
			$this->display();
		}

		// no item found, throw an exceptions, because somebody is fucking with our URL
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}


	/**
	 * Get the data
	 *
	 * @return	void
	 */
	private function getData()
	{
		$this->record = BackendSlideshowsModel::get($this->id);
	}


	/**
	 * Load the form
	 *
	 * @return	void
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new BackendForm('edit');

		// create elements
		$this->frm->addText('name', $this->record['name']);
		$this->frm->addDropdown('type', BackendSlideshowsModel::getTypesAsPairs(), $this->record['type_id']);
		$this->frm->addDropdown('module', BackendSlideshowsHelper::getSupportedModules(), $this->record['module']);
		$this->frm->addDropdown('methods', BackendSlideshowsHelper::getSupportedMethodsByModuleAsPairs($this->record['module']), $this->record['data_callback_method']);

		$this->frm->addText('width', $this->record['width']);
		$this->frm->addText('height', $this->record['height']);
	}


	/**
	 * Parse the form
	 *
	 * @return	void
	 */
	protected function parse()
	{
		parent::parse();

		$this->tpl->assign('id', $this->id);
		$this->tpl->assign('item', $this->record);

		if(BackendSlideshowsHelper::getModules())
		{
			$this->tpl->assign('modules', true);
		}
	}


	/**
	 * Validate the form
	 *
	 * @return	void
	 */
	private function validateForm()
	{
		// is the form submitted?
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
			if($width->isFilled())
			{
				$width->isNumeric(BL::err('NumericCharactersOnly'));
			}
			if($width->isFilled())
			{
				$width->isNumeric(BL::err('NumericCharactersOnly'));
			}

			// the method is filled by javascript, so we have to fetch it from POST
			$method = isset($_POST['methods']) ? $_POST['methods'] : null;

			// no errors?
			if($this->frm->isCorrect())
			{
				// build slideshow record to insert
				$item['id'] = $this->id;
				$item['extra_id'] = $this->record['extra_id'];
				$item['language'] = BL::getWorkingLanguage();
				$item['name'] = $this->frm->getField('name')->getValue();
				$item['type_id'] = $this->frm->getField('type')->getValue();
				$item['module'] = ($module->getValue() == '0') ? null : $module->getValue();
				if($item['module'] !== null)
				{
					$item['data_callback_method'] = $method->getValue();
				}
				else
				{
					$item['width'] = $width->getValue();
					$item['height'] = $height->getValue();

					/*
						if the width/height differ from the one in $this->record, we resize
						all images in this slideshow to the new measurements.
					*/
					if($item['width'] != $this->record['width'] || $item['height'] != $this->record['height'])
					{
						$images = BackendSlideshowsModel::getImages($this->id);

						if(!empty($images))
						{
							// define the old and the new format folders (ie. '600x280')
							$newFormat = $item['width'] . 'x' . $item['height'];
							$oldFormat = $this->record['width'] . 'x' . $this->record['height'];

							// define the path to the slideshow
							$slideshowPath = FRONTEND_FILES_PATH . '/slideshows/' . $this->id;

							// set formats
							$formats = array();
							$formats[] = array('size' => $newFormat, 'force_aspect_ratio' => false);

							foreach($images as $image)
							{
								BackendSlideshowsHelper::generateImages($slideshowPath, $image['filename'], $formats);
							}

							// delete the old format
							SpoonDirectory::delete($slideshowPath . '/' . $oldFormat);
						}
					}
				}

				// save the item
				$id = BackendSlideshowsModel::save($item);

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('index') . '&report=edited' . '&var=' . urlencode($item['name']) . '&highlight=row-' . $item['id']);
			}
		}
	}
}

?>