/**
 * Backend{$module.name|camelcase}Install
 * In this file we store all generic functions that we will be using in the {$module.name} module.
 *
 * @package		backend
 * @subpackage	{$module.name}
 *
 * @author		{$user.name} <{$user.email}>
 * @since		{$module.fork_version}
 */
class {$module.name|camelcase}Install extends ModuleInstaller
{
	/**
	 * Install the module
	 *
	 * @return	void
	 */
	protected function execute()
	{
		// load install.sql
		$this->importSQL(PATH_WWW .'/backend/modules/{$module.name}/installer/install.sql');

		// add 'users' as a module
		$this->addModule('{$module.name}', '{$module['description']}');

		// @todo add settings for this module

		// module rights
		$this->setModuleRights(1, '{$module.name}');

		// action rights
		$this->setActionRights(1, '{$module.name}', 'add');
		$this->setActionRights(1, '{$module.name}', 'delete');
		$this->setActionRights(1, '{$module.name}', 'edit');
		$this->setActionRights(1, '{$module.name}', 'index');
	}
}