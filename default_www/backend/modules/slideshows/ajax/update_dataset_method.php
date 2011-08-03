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
class BackendSlideshowsAjaxUpdateDatasetMethod extends BackendBaseAJAXAction
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
		$label = SpoonFilter::getPostValue('value', null, '');
		$id = SpoonFilter::getPostValue('id', null, '0', 'int');

		// validate
		if($id === 0) $this->output(self::BAD_REQUEST, null, 'ID-parameter is missing.');
		if($module == '') $this->output(self::BAD_REQUEST, null, 'module-parameter is missing.');
		if($label == '') $this->output(self::BAD_REQUEST, null, 'label-parameter is missing.');

		// build record
		$item['id'] = $id;
		$item['module'] = $module;
		$item['method'] = $method;
		$item['label'] = $label;

		$methods = BackendSlideshowsModel::getDataSetMethods($module);

		if(!BackendSlideshowsModel::existsDataSetMethod($item) && $methods[$item['id']]['label'] !== $item['label'])
		{
			$item['id'] = BackendSlideshowsModel::updateDataSetMethod($item);

			$this->output(self::OK, $item['id']);
		}

		else
		{
			$this->output(self::ERROR, BL::err('AlreadyExists'));
		}
	}
}

?>