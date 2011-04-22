<?php

// @todo	jeroen: require the multicurl class here by expanding this class with its construct, so you can add additional methods. Check mailmotor's config.php if you don't know what the hell I'm talking about.

/**
 * This is the configuration-object for the linkchecker module
 *
 * @package		backend
 * @subpackage	link_checker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.1
 */
final class BackendLinkCheckerConfig extends BackendBaseConfig
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