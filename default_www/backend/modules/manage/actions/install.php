<?php

/**
 * BackendManageInstall
 * This is the index-action (default), it will install the module
 *
 * @package		backend
 * @subpackage	manage
 *
 * @author 		Dave Lens <dave@netlash.com>
 * @since		2.0
 */
class BackendManageInstall extends BackendBaseActionEdit
{
	/**
	 * The active selectedModule
	 *
	 * @var	string
	 */
	private $selectedModule;


	/**
	 * Execute the action
	 *
	 * @return	void
	 */
	public function execute()
	{
		// get parameters
		$this->selectedModule = $this->getParameter('module');

		// does the user exists
		if($this->selectedModule !== null)
		{
			// call parent, this will probably add some general CSS/JS or other required files
			parent::execute();

			// get all data for the module we want to edit
			$this->record = BackendManageModel::getModules($this->selectedModule);

			// get all module settings
			$settings = BackendModel::getModuleSettings();

			// install the module
			$this->install();

			// redirect
			$this->redirect(BackendModel::createURLForAction('index'));
		}

		// no user found, throw an exceptions, because somebody is fucking with our URL
		else $this->redirect(BackendModel::createURLForAction('index') .'&error=non-existing');
	}


	/**
	 * Install the module
	 *
	 * @return	void
	 */
	private function install()
	{
		// check if there are labels already present
		if(BackendManageModel::hasLabels($this->selectedModule))
		{
			// activate the module
			BackendManageModel::activateModules(array($this->selectedModule));

			// redirect to reload the locale
			$this->redirect(BackendModel::createURLForAction('reload') .'&amp;module='. $this->selectedModule);
		}

		// install exists
		if(SpoonFile::exists(PATH_WWW .'/backend/modules/'. $this->selectedModule .'/installer/install.php'))
		{
			// require the module installer class
			require_once PATH_WWW .'/backend/core/installer/install.php';

			// init var
			$variables = array();

			// users module needs custom variables
			if($this->selectedModule == 'users')
			{
				$variables['email'] = SpoonSession::get('email');
				$variables['password'] = SpoonSession::get('password');
			}

			// load file
			require_once PATH_WWW .'/backend/modules/'. $this->selectedModule .'/installer/install.php';

			// class name
			$class = SpoonFilter::toCamelCase($this->selectedModule) .'Install';

			// execute installer
			$install = new $class(BackendModel::getDB(), BL::getActiveLanguages(), BL::getInterfaceLanguages(), true, $variables);
		}
	}
}

?>