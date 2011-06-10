<?php

/**
 * BackendManageEdit
 * This is the index-action (default), it will display a form to edit a module
 *
 * @package		backend
 * @subpackage	manage
 *
 * @author 		Dave Lens <dave@netlash.com>
 * @since		2.0
 */
class BackendManageEdit extends BackendBaseActionEdit
{
	/**
	 * The active module
	 *
	 * @var	string
	 */
	private $activeModule;


	/**
	 * Execute the action
	 *
	 * @return	void
	 */
	public function execute()
	{
		// get parameters
		$this->activeModule = $this->getParameter('module');

		// does the user exists
		if($this->activeModule !== null && BackendManageModel::existsModule($this->activeModule, false))
		{
			// call parent, this will probably add some general CSS/JS or other required files
			parent::execute();

			// get all data for the module we want to edit
			$this->record = BackendManageModel::getModules($this->activeModule);

			// get all module settings
			$settings = BackendModel::getModuleSettings();

			// store the settings for the active module
			$this->record['settings'] = isset($settings[$this->activeModule]) ? $settings[$this->activeModule] : array();

			// load the form
			$this->loadForm();

			// validate the form
			$this->validateForm();

			// parse the datagrid
			$this->parse();

			// display the page
			$this->display();
		}

		// no user found, throw an exceptions, because somebody is fucking with our URL
		else $this->redirect(BackendModel::createURLForAction('index') .'&error=non-existing');
	}


	/**
	 * Load the form
	 *
	 * @return	void
	 */
	private function loadForm()
	{
		// add form
		$this->frm = new BackendForm('edit');

		// create elements
		$this->frm->addText('name', $this->record['name'], 255);
		$this->frm->getField('name')->setAttributes(array('disabled' => 'disabled'));
		$this->frm->addTextarea('description', $this->record['description'], 255);
		$this->frm->addCheckbox('active', $this->record['active']);

		// reserve settings stack
		$settings = array();
		$i = 0;

		// reserve an array with 1 setting
		foreach($this->record['settings'] as $name => $value)
		{
			// inc counter
			$i++;

			// check if the value is an array
			if(is_array($value)) $value = SpoonFilter::htmlspecialchars(serialize($value));

			// add setting to stack
			$settings[] = array('id' => $i, 'name' => $name, 'value' => $value);
		}

		// settings was empty
		if(empty($settings))
		{
			// add an empty placeholder
			$settings[1]['id'] = 1;
			$settings[1]['name'] = '';
			$settings[1]['value'] = '';
		}

		// create array for settings
		$this->tpl->assign('settings', $settings);
	}


	/**
	 * Parse the form
	 *
	 * @return	void
	 */
	protected function parse()
	{
		// call parent
		parent::parse();

		// only allow deletion of other users
		if(BackendAuthentication::getUser()->getUserId() != $this->id) $this->tpl->assign('deleteAllowed', true);

		// assign
		$this->tpl->assign('record', $this->record);
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
			// build settings-array
			$settings = isset($_POST['settings']) ? $_POST['settings'] : array();

			// no errors?
			if($this->frm->isCorrect())
			{
				// build module-array
				$module['name'] = $this->record['name'];
				$module['description'] = $this->frm->getField('description')->getValue(true);
				$module['active'] = $this->frm->getField('active')->getChecked() ? 'Y' : 'N';

				// loop the added settings
				foreach($settings as $key => $value)
				{
					// store the component data
					$settings[$value] = empty($_POST['values'][$key]) ? false : $_POST['values'][$key];

					// check if this is a serialized array
					if(strpos($settings[$value], 'a:') !== false) $settings[$value] = unserialize($settings[$value]);

					// unset the initial key
					unset($settings[$key]);
				}

				// save changes
				BackendManageModel::updateModule($module, $settings);

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('index') .'&report=edited&var='. $module['name'] .'&highlight=module-'. $module['name']);
			}
		}
	}
}

?>