<?php

/**
 * Installer for the linkchecker module
 *
 * @package		installer
 * @subpackage	linkchecker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.0
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
		$this->importSQL(dirname(__FILE__) . '/install.sql');

		// add 'analytics' as a module
		$this->addModule('link_checker', 'The link checker module.');

		// module rights
		$this->setModuleRights(1, 'link_checker');

		// insert locale (en)
		$this->insertLocale('en', 'backend', 'dashboard', 'lbl', 'Crawler', 'broken links');
		$this->insertLocale('en', 'backend', 'dashboard', 'lbl', 'All', 'all');
		$this->insertLocale('en', 'backend', 'dashboard', 'lbl', 'Internal', 'internal');
		$this->insertLocale('en', 'backend', 'dashboard', 'lbl', 'External', 'external');

		$this->insertLocale('en', 'backend', 'dashboard', 'msg', 'NoLinks', 'No broken links');

	}
}

?>