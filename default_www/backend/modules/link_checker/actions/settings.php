<?php

/**
 * This is the settings-action, it will display a form to set general link checker settings
 *
 * @package		backend
 * @subpackage	link_checker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.0
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

		// add fields for cache
		$this->frm->addCheckbox('cache_links', BackendModel::getModuleSetting($this->URL->getModule(), 'cache_links', false));

		// add fields for cache time
		$this->frm->addText('cache_time', BackendModel::getModuleSetting($this->URL->getModule(), 'cache_time', 1800));
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
				// @todo This is a bit vague - elaborate a bit on their function. What do these settings do and where are they used?
				BackendModel::setModuleSetting($this->URL->getModule(), 'multi_call', (bool) $this->frm->getField('multi_call')->getValue());

				$numConnections = $this->frm->getField('num_connections')->getValue() == "" ? BackendModel::getModuleSetting($this->URL->getModule(), 'num_connections', 10) : $this->frm->getField('num_connections')->getValue();
				BackendModel::setModuleSetting($this->URL->getModule(), 'num_connections', (int) $numConnections);

				BackendModel::setModuleSetting($this->URL->getModule(), 'cache_links', (bool) $this->frm->getField('cache_links')->getValue());

				$numConnections = $this->frm->getField('cache_time')->getValue() == "" ? BackendModel::getModuleSetting($this->URL->getModule(), 'cache_time', 1800) : $this->frm->getField('cache_time')->getValue();
				BackendModel::setModuleSetting($this->URL->getModule(), 'cache_time', (int) $numConnections);

				// redirect to the settings page
				$this->redirect(BackendModel::createURLForAction('settings') . '&report=saved');
			}
		}
	}
}

?>