<?php

/**
 * This action is used to perform mass actions on slideshows (delete, ...)
 *
 * @package		backend
 * @subpackage	slideshows
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.5
 */
class BackendSlideshowsMassAction extends BackendBaseAction
{
	/**
	 * Execute the action
	 *
	 * @return	void
	 */
	public function execute()
	{
		// call parent, this will probably add some general CSS/JS or other required files
		parent::execute();

		// action to execute
		$action = SpoonFilter::getGetValue('action', array('delete'), 'delete');

		// no id's provided
		if(!isset($_GET['id'])) $this->redirect(BackendModel::createURLForAction('index') . '&error=no-selection');

		// at least one id
		else
		{
			// redefine id's
			$aIds = (array) $_GET['id'];
			$slideshowID = (int) $_GET['slideshow_id'];

			// delete comment(s)
			if($action == 'delete')
			{
				BackendSlideshowsModel::deleteImage($aIds);
			}
		}

		// redirect
		$this->redirect(BackendModel::createURLForAction('images') . '&slideshow_id=' . $slideshowID . '&report=deleted');
	}
}

?>