<?php

/**
 * This action will output a list of all get-methods for a given module.
 *
 * @package		backend
 * @subpackage	slideshows
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.5
 */
class BackendSlideshowsAjaxGetDatasetMethods extends BackendBaseAJAXAction
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		// call parent, this will probably add some general CSS/JS or other required files
		parent::execute();

		// get parameters
		$module = SpoonFilter::getPostValue('m', null, '');

		// validate
		if($module == '') $this->output(self::BAD_REQUEST, null, 'module-parameter is missing.');

		// get items
		$items = BackendSlideshowsModel::getDataSetMethods($module);

		// output
		$this->output(self::OK, $items);
	}
}

?>