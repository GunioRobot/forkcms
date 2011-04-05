<?php

/**
 * This action will return all the dead links from the database
 *
 * @package		backend
 * @subpackage	linkchecker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.0
 */
class BackendLinkCheckerAjaxGetDeadLinks extends BackendBaseAJAXAction
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

		// get data
		$allDeadLinks = BackendLinkCheckerModel::getDeadUrls();

		// return status and data
		$this->output(self::OK, array('status' => 'success', 'deadLinks' => $allDeadLinks, 'message' => 'Data has been retrieved.'));
	}
}

?>