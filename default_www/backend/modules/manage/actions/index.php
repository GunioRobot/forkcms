<?php

/**
 * BackendManageIndex
 * This is the index-action (default), it will display the overview of all available modules
 *
 * @package		backend
 * @subpackage	manage
 *
 * @author 		Dave Lens <dave@netlash.com>
 * @since		2.0
 */
class BackendManageIndex extends BackendBaseActionIndex
{
	/**
	 * Excluded modules
	 *
	 * @var	array
	 */
	private $excludedModules = array('core', 'authentication', 'dashboard', 'error', 'locale', 'users', 'example',
										'settings', 'manage', 'pages', 'search', 'contact', 'content_blocks', 'tags');


	/**
	 * Execute the action
	 *
	 * @return	void
	 */
	public function execute()
	{
		// call parent, this will probably add some general CSS/JS or other required files
		parent::execute();

		// load the datagrid
		$this->loadDatagrid();

		// parse the datagrid
		$this->parse();

		// display the page
		$this->display();
	}


	/**
	 * Load the datagrid.
	 *
	 * @return	void
	 */
	private function loadDatagrid()
	{
		// fetch modules from the directory and the database in 2 separate stacks
		$dirModules = SpoonDirectory::getList(PATH_WWW .'/backend/modules', false, null, '/^[a-z0-9_]+$/i');
		$activeModules = BackendModel::getModules();

		// start with the core, it won't be in $dirModules
		$modules = array();
		$modules[count($dirModules)]['name'] = 'core';
		$modules[count($dirModules)]['active'] = 'Y';

		// loop all modules in the directory
		foreach($dirModules as $key => &$module)
		{
			// build module record
			$modules[$key]['name'] = $module;
			$modules[$key]['active'] = in_array($module, $activeModules) ? 'Y' : 'N';
		}

		// create datagrid with an overview of all active and undeleted users
		$this->datagrid = new BackendDataGridArray($modules);

		// set sorting
		$this->datagrid->setSortingColumns(array('name'), 'name');

		// set name column URL
		$this->datagrid->setColumnURL('name', BackendModel::createURLForAction('edit') .'&amp;module=[name]');

		// add the multicheckbox column
		$this->datagrid->setMassActionCheckboxes('checkbox', '[name]', $this->excludedModules);

		// add mass action dropdown
		$ddmMassAction = new SpoonFormDropdown('action', array('activate' => BL::getLabel('Activate'), 'deactivate' => BL::getLabel('Deactivate')), 'activate');
		$this->datagrid->setMassAction($ddmMassAction);

		// set install/uninstall column
		$this->datagrid->setColumnFunction(array(__CLASS__, 'setInstall'), array('[name]', '[active]'), 'active');
		$this->datagrid->setColumnAttributes('active', array('class' => 'action'));

		// set header labels
		$this->datagrid->setHeaderLabels(array('active' => ''));

		// add edit column
		$this->datagrid->addColumn('edit', null, BL::getLabel('Edit'), BackendModel::createURLForAction('edit') .'&amp;module=[name]');
	}


	/**
	 * Parse the datagrid
	 *
	 * @return	void
	 */
	private function parse()
	{
		$this->tpl->assign('datagrid', ($this->datagrid->getNumResults() != 0) ? $this->datagrid->getContent() : false);
	}


	/**
	 * Datagrid method: shows an available action for a module; install/uninstall
	 *
	 * @return	string
	 * @param	string $module
	 * @param	string $active
	 */
	public static function setInstall($module, $active)
	{
		// base value for string
		$string = '';

		// module is not active, so we need to show the install link
		if($active === 'N')
		{
			// if the module is the core, stop here
			if($module === 'core') return '';

			$url = BackendModel::createURLForAction('install', 'manage').'&amp;module='. $module;
			$classes = 'button icon iconAdd linkButton';
			$string = '<a class="'. $classes .'" href="'. $url .'"><span>'. ucfirst(BL::getLabel('Install')) .'</span></a>';
		}

		// return the string
		return $string;
	}
}

?>