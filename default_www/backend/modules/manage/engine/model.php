<?php

/**
 * BackendManageModel
 * In this file we store all generic functions that we will be using in the manage-modules module.
 *
 * @package		backend
 * @subpackage	manage
 *
 * @author 		Dave Lens <dave@netlash.com>
 * @since		2.0
 */
class BackendManageModel
{
	// overview of all modules
	const QRY_BROWSE_MODULES = 'SELECT i.name, i.active
								FROM modules AS i;';


	/**
	 * Mark the module(s) as active.
	 *
	 * @return	void
	 * @param	array $modules		The module(s) to activate.
	 */
	public static function activateModules($modules)
	{
		BackendModel::getDB(true)->update('modules', array('active' => 'Y'), 'name IN("'. implode('","', $modules) .'")');
	}


	/**
	 * Deletes a module
	 *
	 * @return	void
	 * @param	string $module		The module to delete.
	 */
	public static function delete($module)
	{
		// get db
		$db = BackendModel::getDB(true);

		// delete the module
		$db->delete('modules', 'name = ?', $module);
		$db->delete('modules_settings', 'module = ?', $module);

		// delete the physical module in backend
		if(SpoonDirectory::exists(BACKEND_MODULES_PATH .'/'. $module)) SpoonDirectory::delete(BACKEND_MODULES_PATH .'/'. $module);

		// drop table
		$db->drop($module);
	}


	/**
	 * Mark the module(s) as not active
	 *
	 * @return	void
	 * @param	array $modules		The module(s) to deactivate.
	 */
	public static function deactivateModules($modules)
	{
		BackendModel::getDB(true)->update('modules', array('active' => 'N'), 'name IN("'. implode('","', $modules) .'")');
	}


	/**
	 * Does the module exist.
	 *
	 * @return	bool
	 * @param	string $module				The module to check for existance.
	 * @param	bool[optional] $active		Should the module be active also?
	 */
	public static function existsModule($module, $active = true)
	{
		// redefine
		$active = (bool) $active;

		// get db
		$db = BackendModel::getDB();

		// if the module should also be active, there should be at least one row to return true
		if($active) return (bool) ((int) $db->getVar('SELECT COUNT(i.name)
														FROM modules AS i
														WHERE i.name = ? AND i.active = ?;',
														array($module, 'Y')) > 0);

		// fallback, this doesn't take the active status in account
		return (bool) ((int) $db->getVar('SELECT COUNT(i.name)
											FROM modules AS i
											WHERE i.name = ?;',
											array($module)) > 0);
	}


	/**
	 * Get all available modules, or a specific module
	 *
	 * @return	array
	 * @param	string[optional] $module
	 */
	public static function getModules($module = null)
	{
		// get db
		$db = BackendModel::getDB(true);

		// build query and parameters
		$query = 'SELECT i.*
					FROM modules AS i';
		$parameters = null;

		// check if module param was given
		if(!empty($module))
		{
			$query .= ' WHERE i.name = ?';
			$parameters = array($module);

			// return the result
			return (array) $db->getRecord($query, $parameters);
		}

		// return the results
		return (array) $db->getRecords($query, $parameters);
	}


	/**
	 * Checks if a module already has labels in the database
	 *
	 * @return	void
	 * @param	string $module
	 */
	public static function hasLabels($module)
	{
		return (bool) BackendModel::getDB()->getNumRows('SELECT *
															FROM locale
															WHERE module = ?', (string) $module);
	}


	/**
	 * Add a new module.
	 * Remark: $module['name'] should be available
	 *
	 * @return	void
	 * @param	array $module				The module data.
	 * @param	array $settings				The settings for the new module.
	 * @param	array[optional] $fields		The fields to add in this module
	 */
	public static function insertModule(array $module, array $settings, $fields = array())
	{
		// get db
		$db = BackendModel::getDB(true);

		// insert module
		$db->insert('modules', $module);
		$moduleSettings = array();

		// loop settings
		foreach($settings as $key => $value) $moduleSettings[] = array('module' => $module['name'], 'name' => $key, 'value' => serialize($value));

		// prepare the backend user
		$user = BackendAuthentication::getUser();

		// insert all settings at once
		if(!empty($moduleSettings)) $db->insert('modules_settings', $moduleSettings);

		// insert the rights for the active group
		$db->insert('groups_rights_modules', array('group_id' => $user->getGroupId(), 'module' => $module['name']));

		// add action rights for this group
		$db->insert('groups_rights_actions', array('group_id' => $user->getGroupId(), 'module' => $module['name'], 'action' => 'add', 'level' => 7));
		$db->insert('groups_rights_actions', array('group_id' => $user->getGroupId(), 'module' => $module['name'], 'action' => 'delete', 'level' => 7));
		$db->insert('groups_rights_actions', array('group_id' => $user->getGroupId(), 'module' => $module['name'], 'action' => 'edit', 'level' => 7));
		$db->insert('groups_rights_actions', array('group_id' => $user->getGroupId(), 'module' => $module['name'], 'action' => 'index', 'level' => 7));

		// @todo is the modules folder writable?

		// add the fork version to the module stack
		$module['fork_version'] = FORK_VERSION;

		// set the module path
		$modulePath = BACKEND_MODULES_PATH .'/'. $module['name'];
		$templatesPath = BACKEND_MODULE_PATH .'/module_template';

		// create module files in backend
		SpoonFile::setContent($modulePath .'/config.php', self::getParsedActionTemplate($templatesPath .'/config.php', $module, $fields));
		SpoonFile::setContent($modulePath .'/engine/model.php', self::getParsedActionTemplate($templatesPath .'/engine/model.php', $module, $fields));
		SpoonFile::setContent($modulePath .'/actions/index.php', self::getParsedActionTemplate($templatesPath .'/actions/index.php', $module, $fields));
		SpoonFile::setContent($modulePath .'/actions/add.php', self::getParsedActionTemplate($templatesPath .'/actions/add.php', $module, $fields));
		SpoonFile::setContent($modulePath .'/actions/edit.php', self::getParsedActionTemplate($templatesPath .'/actions/edit.php', $module, $fields));
		SpoonFile::setContent($modulePath .'/actions/mass_action.php', self::getParsedActionTemplate($templatesPath .'/actions/mass_action.php', $module, $fields));
		SpoonFile::setContent($modulePath .'/layout/templates/index.tpl', self::getParsedActionTemplate($templatesPath .'/layout/templates/index.tpl', $module, $fields));
		SpoonFile::setContent($modulePath .'/layout/templates/add.tpl', self::getParsedActionTemplate($templatesPath .'/layout/templates/add.tpl', $module, $fields));
		SpoonFile::setContent($modulePath .'/layout/templates/edit.tpl', self::getParsedActionTemplate($templatesPath .'/layout/templates/edit.tpl', $module, $fields));
		SpoonFile::setContent($modulePath .'/js/'. $module['name'] .'.js', self::getParsedActionTemplate($templatesPath .'/js/module.js', $module, $fields));
		SpoonFile::setContent($modulePath .'/installer/install.php', self::getParsedActionTemplate($templatesPath .'/installer/install.php', $module, $fields));
		SpoonFile::setContent($modulePath .'/installer/install.sql', self::getParsedActionTemplate($templatesPath .'/installer/install.sql', $module, $fields));

		// run the install query to create a default table for the module
		$db->execute(SpoonFile::getContent($modulePath .'/installer/install.sql'));

		// @todo add to navigation.php, will probably have to wait until it's automated and takes user rights into account
	}


	/**
	 * Loads a template, parses variables and writes to a new action file
	 *
	 * @return	void
	 * @param
	 */
	public static function getParsedActionTemplate($template, $module, $fields)
	{
		// check if a template exists
		if(!SpoonFile::exists($template)) throw new SpoonException('The given template does not exist.');

		// prepare the backend user
		$user = BackendAuthentication::getUser();

		// make a user record to parse
		$userRecord = array();
		$userRecord['is_god'] = $user->isGod();
		$userRecord['is_authenticated'] = $user->isAuthenticated();
		$userRecord['name'] = $user->getSetting('name') .' '. $user->getSetting('surname');
		$userRecord['email'] = $user->getEmail();

		// make new template
		$tpl = new SpoonTemplate($template);
		$tpl->setCompileDirectory(BACKEND_CACHE_PATH .'/templates');
		$tpl->setForceCompile(true);

		// add a new modifier to the template
		$tpl->mapModifier('camelcase', array('SpoonFilter', 'toCamelCase'));

		// assign values to the template
		$tpl->assign('module', $module);
		$tpl->assign('fieldsLeft', $fields['left']);
		$tpl->assign('fieldsRight', $fields['right']);
		$tpl->assign('user', $userRecord);

		// set content
		$content = $tpl->getContent($template);

		// replace [ with { if we were dealing with a template
		if(strpos($template, '.tpl') !== false) $content = str_replace(array('[', ']'), array('{', '}'), $tpl->getContent($template));

		// add php opening/closing tags if we're dealing with a php file
		if(strpos($template, '.php') !== false) $content = '<?php' . PHP_EOL . PHP_EOL . $content . PHP_EOL . PHP_EOL . '?>';

		// return the content
		return (string) $content;
	}


	/**
	 * Save the changes for a given module
	 * Remark: $module['name'] should be available
	 *
	 * @return	void
	 * @param	array $module		The module data.
	 * @param	array $settings		The settings for the module.
	 */
	public static function updateModule(array $module, array $settings)
	{
		// get db
		$db = BackendModel::getDB(true);

		// update module
		$db->update('modules', $module, 'name = ?', $module['name']);
		$moduleSettings = array();

		// loop settings
		foreach($settings as $key => $value) $moduleSettings[] = array('module' => $module['name'], 'name' => $key, 'value' => serialize($value));

		// delete all module settings
		$db->delete('modules_settings', 'module = ?', $module['name']);

		// insert all settings at once
		$db->insert('modules_settings', $moduleSettings);
	}
}

?>