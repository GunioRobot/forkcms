/**
 * Backend{$module.name|camelcase}MassAction
 * This action is used to control mass actions for the {$module.name}-module datagrid.
 *
 * @package		backend
 * @subpackage	{$module.name}

 *
 * @author		{$user.name} <{$user.email}>
 * @since		{$module.fork_version}

 */
class Backend{$module.name|camelcase}MassAction extends BackendBaseAction
{
	/**
	 * Execute the action
	 *
	 * @return	void
	 */
	public function execute()
	{
		// call parent, this will probably add some general CSS/JS or other required files
		parent::execute();

		// action to execute
		$action = SpoonFilter::getGetValue('action', array('delete'), 'delete');

		// no id's provided
		if(!isset($_GET['id'])) $this->redirect(BackendModel::createURLForAction('index') .'&error=no-selection');

		// at least one id
		else
		{
			// redefine id's
			$aIds = (array) $_GET['id'];

			// delete comment(s)
			if($action == 'delete') Backend{$module.name|camelcase}Model::delete($aIds);
		}

		// redirect
		$this->redirect(BackendModel::createURLForAction('index') .'&report=deleted');
	}
}