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
class BackendLinkCheckerAjaxContainsDeadLinks extends BackendBaseAJAXAction
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

		// require the helper class
		require_once BACKEND_MODULES_PATH . '/link_checker/engine/helper.php';

		// get post data
		$text = SpoonFilter::getPostValue('text', null, '');

		// get data
		$containsDeadLinks = BackendLinkCheckerHelper::containsDeadLink($text);

		// return status and data
		$this->output(self::OK, array('status' => 'success', 'containsDeadLinks' => $containsDeadLinks, 'message' => 'Data has been retrieved.'));
	}
}

?>