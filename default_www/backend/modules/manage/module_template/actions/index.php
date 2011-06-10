/**
 * Backend{$module.name|camelcase}Index
 * This is the index-action (default), it will display the {$module.name}-overview
 *
 * @package		backend
 * @subpackage	{$module.name}

 *
 * @author		{$user.name} <{$user.email}>
 * @since		{$module.fork_version}

 */
class Backend{$module.name|camelcase}Index extends BackendBaseActionIndex
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

		// load the datagrid
		$this->loadDatagrid();

		// parse the datagrid
		$this->parse();

		// display the page
		$this->display();
	}


	/**
	 * Load the datagrid.
	 *
	 * @return	void
	 */
	private function loadDatagrid()
	{
		// create datagrid with an overview of all active and undeleted users
		$this->datagrid = new BackendDataGridDB(Backend{$module.name|camelcase}Model::QRY_BROWSE);

		// set colum URLs
		$this->datagrid->setColumnURL('name', BackendModel::createURLForAction('edit') .'&amp;id=[id]');

		// add the multicheckbox column
		$this->datagrid->setMassActionCheckboxes('checkbox', '[id]');

		// add mass action dropdown
		$ddmMassAction = new SpoonFormDropdown('action', array('delete' => BL::getLabel('Delete')), 'delete');
		$this->datagrid->setMassAction($ddmMassAction);

		// add edit column
		$this->datagrid->addColumn('edit', null, BL::getLabel('Edit'), BackendModel::createURLForAction('edit') .'&amp;id=[id]');
	}


	/**
	 * Parse the datagrid
	 *
	 * @return	void
	 */
	private function parse()
	{
		$this->tpl->assign('datagrid', ($this->datagrid->getNumResults() != 0) ? $this->datagrid->getContent() : false);
	}
}