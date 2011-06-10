/**
 * Backend{$module.name|camelcase}Index
 * This is the configuration-object for the {$module.name-module
 *
 * @package		backend
 * @subpackage	{$module.name}

 *
 * @author		{$user.name} <{$user.email}>
 * @since		{$module.fork_version}

 */
final class Backend{$module.name|camelcase}Config extends BackendBaseConfig
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
}