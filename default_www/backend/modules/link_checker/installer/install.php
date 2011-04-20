<?php

/**
 * Installer for the linkchecker module
 *
 * @package		installer
 * @subpackage	link_checker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.1
 */
class LinkCheckerInstall extends ModuleInstaller
{
	/**
	 * Install the module
	 *
	 * @return	void
	 */
	protected function execute()
	{
		// load install.sql
		$this->importSQL(dirname(__FILE__) . '/data/install.sql');

		// add 'link_checker' as a module
		$this->addModule('link_checker', 'The link checker module.');

		// module rights
		$this->setModuleRights(1, 'link_checker');

		// action rights
		$this->setActionRights(1, 'link_checker', 'index');

		// import locale
		$this->importLocale(dirname(__FILE__) . '/data/locale.xml');
	}
}

?>