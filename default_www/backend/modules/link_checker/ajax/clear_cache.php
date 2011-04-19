<?php

/**
 * This action will clear the cache
 *
 * @package		backend
 * @subpackage	link_checker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.1
 */
class BackendLinkCheckerAjaxClearCache extends BackendBaseAJAXAction
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

		// clear cache
		BackendLinkCheckerModel::clearCache();

		// return status and data
		$this->output(self::OK, array('status' => 'success', 'message' => 'Cache has been cleared.'), 'Cache has been cleared.');
	}
}

?>