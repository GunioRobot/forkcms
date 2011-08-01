<?php

/**
 * This is the edit action, it will display a form to edit an existing slideshow image.
 *
 * @package		backend
 * @subpackage	slideshows
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.5
 */
class BackendSlideshowsEditImage extends BackendBaseActionEdit
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
		if($this->id !== null && BackendSlideshowsModel::existsImage($this->id))
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
		$this->record = BackendSlideshowsModel::getImage($this->id);
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
		$this->frm->addText('title', $this->record['title']);
		$this->frm->addEditor('caption', $this->record['caption']);
		$this->frm->addImage('image');
		$this->frm->addCheckbox('delete_image');
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

			// validate fields
			$image = $this->frm->getField('image');
			$caption = $this->frm->getField('caption');

			$this->frm->getField('title')->isFilled(BL::err('NameIsRequired'));
			if($image->isFilled())
			{
				if($image->isAllowedExtension(array('jpg', 'jpeg', 'gif', 'png'), BL::err('JPGGIFAndPNGOnly')))
				{
					$image->isAllowedMimeType(array('image/gif', 'image/jpg', 'image/jpeg', 'image/png'), BL::err('JPGGIFAndPNGOnly'));
				}
			}

			elseif($this->record['image'] === null) $image->isFilled(BL::err('FieldIsRequired'));

			// no errors?
			if($this->frm->isCorrect())
			{
				// build image record to insert
				$item['id'] = $this->id;
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['caption'] = (!$caption->isFilled()) ? null : $caption->getValue();
				$item['filename'] = $this->record['image'];

				// set events files path for this record
				$path = FRONTEND_FILES_PATH . '/slideshows/' . $this->record['slideshow_id'];
				$format = $this->record['width'] . 'x' . $this->record['height'];

				// delete_image checkbox was checked
				if($this->frm->getField('delete_image')->getChecked())
				{
					SpoonFile::delete($path . '/source/' . $this->record['image']);
					SpoonFile::delete($path . '/64x64/' . $this->record['image']);
					SpoonFile::delete($path . '/' . $format . '/' . $this->record['image']);

					$item['filename'] = null;
				}

				if($image->isFilled())
				{
					// set formats
					$formats = array();
					$formats[] = array('size' => '64x64', 'force_aspect_ratio' => false);
					$formats[] = array('size' => $format, 'force_aspect_ratio' => false);

					// overwrite the filename
					if($item['filename'] === null)
					{
						$item['filename'] = time() . '.' . $image->getExtension();
					}

					// add images
					BackendSlideshowsHelper::addImages($image, $path, $item['filename'], $formats);
				}

				// save the item
				$id = BackendSlideshowsModel::saveImage($item);

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('images') . '&slideshow_id=' . $this->record['slideshow_id'] . '&report=edited&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']);
			}
		}
	}
}

?>