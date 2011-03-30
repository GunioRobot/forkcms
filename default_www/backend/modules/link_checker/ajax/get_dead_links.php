<?php

/**
 * This edit-action will return all the dead links from the database using Ajax
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
	 * All dead links found
	 *
	 * @var bool
	 */
	private $allDeadLinks = array();


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
		$this->allDeadLinks = BackendLinkCheckerModel::getDeadUrls();

		// return status and data
		$this->output(self::OK, array('status' => 'success', 'allDeadLinks' => $this->allDeadLinks, 'message' => 'Data has been retrieved.'));
	}
}

?>