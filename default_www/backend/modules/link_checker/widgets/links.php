<?php

/*
	@todo	I'd put all methods starting with "load" into execute(), and everything involving $this->tpl->assign() into the parse() method,
			because $this->tpl->assign() essentially parses content into the template.
*/

/**
 * This is the linkchecker widget
 *
 * @package		backend
 * @subpackage	linkchecker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.0
 */
class BackendLinkCheckerWidgetLinks extends BackendBaseWidget
{
	/**
	 * Execute the widget
	 *
	 * @return	void
	 */
	public function execute()
	{
		// set column
		$this->setColumn('left');

		// delete non used dead links
		BackendLinkCheckerHelper::cleanup();

		// parse
		$this->parse();

		// add refresh javascript
		$this->header->addJavascript('dashboard.js', 'link_checker');

		// display
		$this->display();
	}


	/**
	 * Load the datagrid for all links
	 *
	 * @return	void
	 */
	private function loadAll()
	{
		// fetch all links
		$all = BackendLinkCheckerModel::getAll();

		// there are some results
		if(!empty($all))
		{
			// message all
			$this->tpl->assign('msgAll', 'Found broken links.');

			// num results
			$this->tpl->assign('numAll', count($all));
		}
		else
		{
			// no results
			$this->tpl->assign('numAll', 0);

			// message all
			$this->tpl->assign('msgAll', 'No broken links.');
		}
	}


	/**
	 * Load the datagrid for internal links
	 *
	 * @return	void
	 */
	private function loadInternal()
	{
		// fetch internal links
		$all = BackendLinkCheckerModel::getInternal();

		// there are some results
		if(!empty($all))
		{
			// message all
			$this->tpl->assign('msgInternal', 'Found broken links.');

			// num results
			$this->tpl->assign('numInternal', count($all));
		}
		else
		{
			// no results
			$this->tpl->assign('numInternal', 0);

			// message internal
			$this->tpl->assign('msgInternal', 'No broken links.');
		}
	}


	/**
	 * Load the datagrid for external links
	 *
	 * @return	void
	 */
	private function loadExternal()
	{
		// fetch external links
		$all = BackendLinkCheckerModel::getExternal();

		// there are some results
		if(!empty($all))
		{
			// message all
			$this->tpl->assign('msgExternal', 'Found broken links.');

			// num results
			$this->tpl->assign('numExternal', count($all));
		}
		else
		{
			// no results
			$this->tpl->assign('numExternal', 0);

			// message external
			$this->tpl->assign('msgExternal', 'No broken links.');
		}
	}


	/**
	 * Parse stuff into the template
	 *
	 * @return	void
	 */
	private function parse()
	{
		$this->loadAll();
		$this->loadInternal();
		$this->loadExternal();
	}
}

?>