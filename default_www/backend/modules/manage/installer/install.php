<?php

/**
 * ManageInstall
 * Installer for the modules-management module
 *
 * @package		installer
 * @subpackage	manage
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.0
 */
class ManageInstall extends ModuleInstaller
{
	/**
	 * Install the module
	 *
	 * @return	void
	 */
	protected function execute()
	{
		// add 'users' as a module
		$this->addModule('manage', 'The module to manage ... modules.');

		// general settings
		$this->setSetting('manage', 'default_modules', array('authentication', 'content_blocks', 'core', 'dashboard', 'error', 'example', 'locale', 'pages', 'settings', 'tags', 'users'));

		// module rights
		$this->setModuleRights(1, 'manage');

		// action rights
		$this->setActionRights(1, 'manage', 'add');
		$this->setActionRights(1, 'manage', 'edit');
		$this->setActionRights(1, 'manage', 'index');

		// install locale
		$this->importLocale(dirname(__FILE__) . '/data/locale.xml');
	}
}

?>