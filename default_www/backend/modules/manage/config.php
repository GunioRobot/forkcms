<?php

// require the helper file
require_once 'engine/helper.php';

/**
 * BackendManageConfig
 * This is the configuration-object for the module-management module
 *
 * @package		backend
 * @subpackage	manage
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.0
 */
final class BackendManageConfig extends BackendBaseConfig
{
	/**
	 * The default action
	 *
	 * @var	string
	 */
	protected $defaultAction = 'index';


	/**
	 * The disabled actions
	 *
	 * @var	array
	 */
	protected $disabledActions = array();


	/**
	 * The disabled AJAX-actions
	 *
	 * @var	array
	 */
	protected $disabledAJAXActions = array();
}

?>