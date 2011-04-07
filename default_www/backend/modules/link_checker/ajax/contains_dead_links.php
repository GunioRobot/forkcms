<?php

/**
 * This action will check a given text on dead links
 *
 * @package		backend
 * @subpackage	link_checker
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

		// check if the given string has dead links
		$containsDeadLinks = BackendLinkCheckerHelper::containsDeadLink($text);

		// return status and data
		$this->output(self::OK, array('status' => 'success', 'containsDeadLinks' => $containsDeadLinks, 'message' => 'Data has been retrieved.'));
	}
}

?>