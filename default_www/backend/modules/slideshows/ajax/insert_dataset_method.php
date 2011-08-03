<?php

/**
 * This action will save a selected method + label for the given module.
 *
 * @package		backend
 * @subpackage	slideshows
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.5
 */
class BackendSlideshowsAjaxInsertDatasetMethod extends BackendBaseAJAXAction
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
		$method = SpoonFilter::getPostValue('method', null, '');
		$label = SpoonFilter::getPostValue('label', null, '');

		// validate
		if($module == '') $this->output(self::BAD_REQUEST, null, 'module-parameter is missing.');
		if($label == '') $this->output(self::BAD_REQUEST, null, 'label-parameter is missing.');

		// build record
		$item['module'] = $module;
		$item['method'] = $method;
		$item['label'] = $label;

		if(!BackendSlideshowsModel::existsDataSetMethod($item))
		{
			$item['id'] = BackendSlideshowsModel::insertDataSetMethod($item);

			$this->output(self::OK, $item['id']);
		}

		else
		{
			$this->output(self::ERROR, BL::err('AlreadyExists'));
		}
	}
}

?>