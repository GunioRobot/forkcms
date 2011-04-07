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
		$this->setSetting('link_checker', 'multi_call', false);
		$this->setSetting('link_checker', 'multi_call', 10);

		$this->setSetting('link_checker', 'cache_dead_links', false);
		$this->setSetting('link_checker', 'cache_time', 1800);

		// module rights
		$this->setModuleRights(1, 'link_checker');

		// action rights
		$this->setActionRights(1, 'link_checker', 'index');
		$this->setActionRights(1, 'link_checker', 'settings');

		// insert locale (en)
		$this->insertLocale('en', 'backend', 'dashboard', 'lbl', 'Url', 'destination');
		$this->insertLocale('en', 'backend', 'dashboard', 'msg', 'NoLinks', 'No broken links.');
		$this->insertLocale('en', 'backend', 'dashboard', 'msg', 'AllLinks', 'Edit links');

		$this->insertLocale('en', 'backend', 'link_checker', 'lbl', 'All', 'all');
		$this->insertLocale('en', 'backend', 'link_checker', 'lbl', 'Internal', 'internal');
		$this->insertLocale('en', 'backend', 'link_checker', 'lbl', 'External', 'external');
		$this->insertLocale('en', 'backend', 'link_checker', 'lbl', 'Code', 'code');
		$this->insertLocale('en', 'backend', 'link_checker', 'lbl', 'Url', 'destination');
		$this->insertLocale('en', 'backend', 'link_checker', 'lbl', 'DateChecked', 'last checked');
		$this->insertLocale('en', 'backend', 'link_checker', 'msg', 'ErrorCode404', 'Page not found.');
		$this->insertLocale('en', 'backend', 'link_checker', 'msg', 'ErrorCode0', 'Website is dead.');

		$this->insertLocale('en', 'backend', 'core', 'msg', 'DeadLinksToModerate', '%1$s link(s) to correct.');
		$this->insertLocale('en', 'backend', 'core', 'msg', 'NoLinks', 'No broken links.');
		$this->insertLocale('en', 'backend', 'core', 'lbl', 'LinkChecker', 'link checker');
		$this->insertLocale('en', 'backend', 'core', 'lbl', 'DeadLinks', 'dead links');
		$this->insertLocale('en', 'backend', 'core', 'lbl', 'Refresh', 'refresh');

		$this->insertLocale('en', 'backend', 'core', 'lbl', 'Curl', 'cURL');
		$this->insertLocale('en', 'backend', 'core', 'lbl', 'MultiCall', 'use Multi-Threading');
		$this->insertLocale('en', 'backend', 'core', 'lbl', 'NumConnections', 'connections');

		$this->insertLocale('en', 'backend', 'core', 'msg', 'EditorDeadLinks', 'There are dead/broken links.');

		$this->insertLocale('en', 'backend', 'core', 'lbl', 'Caching', 'caching');
		$this->insertLocale('en', 'backend', 'core', 'lbl', 'CacheTime', 'cache time');
		$this->insertLocale('en', 'backend', 'core', 'lbl', 'CacheDeadLinks', 'cache dead links');
	}
}

?>