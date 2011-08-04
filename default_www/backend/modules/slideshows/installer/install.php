<?php

/**
 * Installer for the slideshows module
 *
 * @package		installer
 * @subpackage	slideshows
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.5
 */
class SlideshowsInstall extends ModuleInstaller
{
	/**
	 * Install the module
	 */
	protected function execute()
	{
		// load install.sql
		$this->importSQL(dirname(__FILE__) . '/data/install.sql');

		// add 'blog' as a module
		$this->addModule('slideshows', 'The slideshows module.');

		// import locale
		$this->importLocale(dirname(__FILE__) . '/data/locale.xml');

		// module rights
		$this->setModuleRights(1, 'slideshows');

		// action rights
		$this->setActionRights(1, 'slideshows', 'add');
		$this->setActionRights(1, 'slideshows', 'edit');
		$this->setActionRights(1, 'slideshows', 'index');
		$this->setActionRights(1, 'slideshows', 'add_image');
		$this->setActionRights(1, 'slideshows', 'edit_image');
		$this->setActionRights(1, 'slideshows', 'images');
		$this->setActionRights(1, 'slideshows', 'mass_action');
		$this->setActionRights(1, 'slideshows', 'delete');
		$this->setActionRights(1, 'slideshows', 'delete_image');
		$this->setActionRights(1, 'slideshows', 'settings');
		$this->setActionRights(1, 'slideshows', 'sequence');
		$this->setActionRights(1, 'slideshows', 'get_dataset_methods_as_pairs');
		$this->setActionRights(1, 'slideshows', 'get_dataset_methods');
		$this->setActionRights(1, 'slideshows', 'get_dataset_preview');
		$this->setActionRights(1, 'slideshows', 'get_supported_methods');
		$this->setActionRights(1, 'slideshows', 'insert_dataset_method');
		$this->setActionRights(1, 'slideshows', 'update_dataset_method');

		// set module settings
		$this->setSetting('slideshows', 'modules', false);
	}
}

?>