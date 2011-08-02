<?php

/**
 * This is the add action, it will display a form to add an image to a slideshow.
 *
 * @package		backend
 * @subpackage	slideshows
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.5
 */
class BackendSlideshowsAddImage extends BackendBaseActionEdit
{
	/**
	 * The slideshow record
	 *
	 * @var	array
	 */
	private $slideshow;


	/**
	 * Execute the action
	 */
	public function execute()
	{
		$id = $this->getParameter('slideshow_id', 'int');

		// does the item exist
		if($id !== null && BackendSlideshowsModel::exists($id))
		{
			parent::execute();

			$this->getData();
			$this->loadForm();
			$this->validateForm();
			$this->parse();
			$this->display();
		}
	}


	/**
	 * Get the necessary data
	 */
	private function getData()
	{
		$this->slideshow = BackendSlideshowsModel::get($this->getParameter('slideshow_id', 'int'));
	}


	/**
	 * Load the form
	 */
	private function loadForm()
	{
		$this->frm = new BackendForm('add');

		$this->frm->addText('title');
		$this->frm->addEditor('caption');
		$this->frm->addImage('image');
	}


	/**
	 * Parses stuff into the template
	 */
	protected function parse()
	{
		parent::parse();

		$this->tpl->assign('slideshow', $this->slideshow);
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

			// validate fields
			$image = $this->frm->getField('image');
			$caption = $this->frm->getField('caption');

			$this->frm->getField('title')->isFilled(BL::err('NameIsRequired'));

			if($image->isFilled(BL::err('FieldIsRequired')))
			{
				if($image->isAllowedExtension(array('jpg', 'jpeg', 'gif', 'png'), BL::err('JPGGIFAndPNGOnly')))
				{
					$image->isAllowedMimeType(array('image/gif', 'image/jpg', 'image/jpeg', 'image/png'), BL::err('JPGGIFAndPNGOnly'));
				}
			}

			// no errors?
			if($this->frm->isCorrect())
			{
				// build image record to insert
				$item['slideshow_id'] = $this->slideshow['id'];
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['caption'] = (!$caption->isFilled()) ? null : $caption->getValue();

				// set files path for this record
				$path = FRONTEND_FILES_PATH . '/slideshows/' . $item['slideshow_id'];
				$format = $this->slideshow['width'] . 'x' . $this->slideshow['height'];

				// set formats
				$formats = array();
				$formats[] = array('size' => '64x64', 'force_aspect_ratio' => false);
				$formats[] = array('size' => $format, 'force_aspect_ratio' => false);

				// set the filename
				$item['filename'] = time() . '.' . $image->getExtension();

				// add images
				BackendSlideshowsHelper::addImages($image, $path, $item['filename'], $formats);

				// save the item
				$id = BackendSlideshowsModel::saveImage($item);

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('images') . '&slideshow_id=' . $item['slideshow_id'] . '&report=added&var=' . urlencode($item['title']) . '&highlight=row-' . $id);
			}
		}
	}
}

?>