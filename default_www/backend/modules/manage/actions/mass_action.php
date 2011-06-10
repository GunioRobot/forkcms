<?php

/**
 * BackendManageMassAction
 * This action is used to activate/deactivate one or more modules.
 *
 * @package		backend
 * @subpackage	manage
 *
 * @author		Fork CMS <dave.lens@telenet.be>
 * @since		2.0.1
 */
class BackendManageMassAction extends BackendBaseAction
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
		$action = SpoonFilter::getGetValue('action', array('activate', 'deactivate'), 'activate');

		// no id's provided
		if(!isset($_GET['id'])) $this->redirect(BackendModel::createURLForAction('index') .'&error=no-selection');

		// at least one id
		else
		{
			// redefine id's
			$aIds = (array) $_GET['id'];

			// execute the correct action
			switch($action)
			{
				case 'activate':
					BackendManageModel::activateModules($aIds);
				break;

				case 'deactivate':
					BackendManageModel::deactivateModules($aIds);
				break;
			}
		}

		// redirect
		$this->redirect(BackendModel::createURLForAction('index') .'&report=deleted');
	}
}

?>