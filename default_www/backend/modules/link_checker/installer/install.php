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

		// add 'link_checker' as a module
		$this->addModule('link_checker', 'The link checker module.');

		// general settings
		$this->setSetting('location', 'multi_call', false);
		$this->setSetting('location', 'multi_call', 10);

		// module rights
		$this->setModuleRights(1, 'link_checker');

		// action rights
		$this->setActionRights(1, 'link_checker', 'index');
		$this->setActionRights(1, 'link_checker', 'settings');

		// insert locale (nl)


		// insert locale (en)
		$this->insertLocale('en', 'backend', 'dashboard', 'lbl', 'Url', 'destination');
		$this->insertLocale('en', 'backend', 'dashboard', 'lbl', 'All', 'all');
		$this->insertLocale('en', 'backend', 'dashboard', 'lbl', 'Internal', 'internal');
		$this->insertLocale('en', 'backend', 'dashboard', 'lbl', 'External', 'external');
		$this->insertLocale('en', 'backend', 'dashboard', 'msg', 'NoLinks', 'No broken links.');
		$this->insertLocale('en', 'backend', 'dashboard', 'msg', 'AllLinks', 'Edit links');

		$this->insertLocale('en', 'backend', 'link_checker', 'lbl', 'All', 'all');
		$this->insertLocale('en', 'backend', 'link_checker', 'lbl', 'Internal', 'internal');
		$this->insertLocale('en', 'backend', 'link_checker', 'lbl', 'External', 'external');
		$this->insertLocale('en', 'backend', 'link_checker', 'lbl', 'Code', 'code');
		$this->insertLocale('en', 'backend', 'link_checker', 'lbl', 'Url', 'destination');
		$this->insertLocale('en', 'backend', 'link_checker', 'msg', 'ErrorCode404', 'Page not found.');
		$this->insertLocale('en', 'backend', 'link_checker', 'msg', 'ErrorCode0', 'Website is dead.');

		$this->insertLocale('en', 'backend', 'core', 'msg', 'NoLinks', 'No broken links.');
		$this->insertLocale('en', 'backend', 'core', 'lbl', 'LinkChecker', 'link checker');

		$this->insertLocale('en', 'backend', 'core', 'lbl', 'MultiCall', 'use multicall');
		$this->insertLocale('en', 'backend', 'core', 'lbl', 'NumConnections', 'connections');

	}
}

?>