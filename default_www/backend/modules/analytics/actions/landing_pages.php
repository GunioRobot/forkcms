<?php

/**
 * BackendAnalyticsLanding
 * This is the landing-pages-action, it will display the overview of analytics posts
 *
 * @package		backend
 * @subpackage	analytics
 *
 * @author		Dieter Vanden Eynde <dieter@netlash.com>
 * @author		Annelies Van Extergem <annelies@netlash.com>
 * @since		2.0
 */
class BackendAnalyticsLandingPages extends BackendAnalyticsBase
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

		// parse
		$this->parse();

		// display the page
		$this->display();
	}


	/**
	 * Parse this page
	 *
	 * @return	void
	 */
	protected function parse()
	{
		// call parent parse
		parent::parse();

		// get results
		$results = BackendAnalyticsModel::getLandingPages($this->startTimestamp, $this->endTimestamp);

		// there are some results
		if(!empty($results))
		{
			// get the datagrid
			$datagrid = new BackendDataGridArray($results);

			// hide columns
			$datagrid->setColumnsHidden('start_date', 'end_date', 'updated_on', 'page_encoded');

			// set headers values
			$headers['page_path'] = ucfirst(BL::getLabel('Page'));

			// set headers
			$datagrid->setHeaderLabels($headers);

			// set url
			$datagrid->setColumnURL('page_path', BackendModel::createURLForAction('detail_page') .'&amp;page=[page_encoded]');

			// add the multicheckbox column
			$datagrid->setMassActionCheckboxes('checkbox', '[id]');

			// add mass action dropdown
			$ddmMassAction = new SpoonFormDropdown('action', array('delete_landing_page' => BL::getLabel('Delete')), 'delete');
			$datagrid->setMassAction($ddmMassAction);

			// parse the datagrid
			$this->tpl->assign('dgPages', $datagrid->getContent());
		}
	}
}

?>