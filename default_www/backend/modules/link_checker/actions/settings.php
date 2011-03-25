<?php

/**
 * This is the settings-action, it will display a form to set general location settings
 *
 * @package		backend
 * @subpackage	location
 *
 * @author		Matthias Mullie <matthias@netlash.com>
 * @since		2.1
 */
class BackendLinkCheckerSettings extends BackendBaseActionEdit
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

		// add javascript
		$this->header->addJavascript('settings.js', 'link_checker');
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

		// add fields for multi call
		$this->frm->addCheckbox('multi_call', BackendModel::getModuleSetting($this->URL->getModule(), 'multi_call', false));

		// add fields for num connections
		$this->frm->addText('num_connections', BackendModel::getModuleSetting($this->URL->getModule(), 'num_connections', 10));
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
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// form is validated
			if($this->frm->isCorrect())
			{
				// set our settings
				BackendModel::setModuleSetting($this->URL->getModule(), 'multi_call', (bool) $this->frm->getField('multi_call')->getValue());

				$numConnections = $this->frm->getField('num_connections')->getValue() == "" ? BackendModel::getModuleSetting($this->URL->getModule(), 'num_connections', 10) : $this->frm->getField('num_connections')->getValue();
				BackendModel::setModuleSetting($this->URL->getModule(), 'num_connections', (int) $numConnections);

				// redirect to the settings page
				$this->redirect(BackendModel::createURLForAction('settings') . '&report=saved');
			}
		}
	}
}

?>