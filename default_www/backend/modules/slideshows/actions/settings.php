<?php

/**
 * This is the settings-action, it will display a form to set general slideshows settings
 *
 * @package		backend
 * @subpackage	slideshows
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.5
 */
class BackendSlideshowsSettings extends BackendBaseActionEdit
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

		// load form
		$this->loadForm();

		// validates the form
		$this->validateForm();

		// parse
		$this->parse();

		// display the page
		$this->display();
	}


	/**
	 * Loads the settings form
	 *
	 * @return	void
	 */
	private function loadForm()
	{
		// init settings form
		$this->frm = new BackendForm('settings');

		// get all active modules and rewrite them so key = value
		$modules = BackendModel::getModules();

		// loop the modules
		foreach($modules as $key => &$module)
		{
			if($module === 'slideshows') unset($modules[$key]);

			$label = BL::lbl(SpoonFilter::toCamelCase($module));
			$module = array('label' => $label, 'value' => $module);
		}

		// get all the currently stored modules
		$storedModules = BackendModel::getModuleSetting('slideshows', 'modules');

		// add fields for pagination
		$this->frm->addMultiCheckbox('modules', $modules, $storedModules);
	}


	/**
	 * Validates the settings form
	 *
	 * @return	void
	 */
	private function validateForm()
	{
		// form is submitted
		if($this->frm->isSubmitted())
		{
			// shorten fields
			$modules = $this->frm->getField('modules')->getValue();

			// form is validated
			if($this->frm->isCorrect())
			{
				// set our settings
				BackendModel::setModuleSetting('slideshows', 'modules', $modules);

				if(!empty($modules))
				{
					foreach($modules as $module)
					{
						BackendSlideshowsHelper::writeHelperFile($module);
					}
				}

				// redirect to the settings page
				$this->redirect(BackendModel::createURLForAction('settings') . '&report=saved');
			}
		}
	}
}

?>