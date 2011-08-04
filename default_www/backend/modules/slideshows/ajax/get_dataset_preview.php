<?php

/**
 * This action will get a list of preview images for the selected dataset.
 *
 * @package		backend
 * @subpackage	slideshows
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.5
 */
class BackendSlideshowsAjaxGetDatasetPreview extends BackendBaseAJAXAction
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		// call parent, this will probably add some general CSS/JS or other required files
		parent::execute();

		// get parameters
		$id = SpoonFilter::getPostValue('id', null, '');

		// validate
		if($id == '') $this->output(self::BAD_REQUEST, null, 'module-parameter is missing.');

		// get record
		$record = BackendSlideshowsModel::getDataSet($id);

		// validate
		if(empty($record)) $this->output(self::BAD_REQUEST, null, 'no matching dataset found.');

		require_once FRONTEND_MODULES_PATH . '/' . $record['module'] . '/engine/slideshows.php';

		if(is_callable($record['method']))
		{
			$results = call_user_func($record['method']);

			if(!empty($results))
			{
				foreach($results as $result)
				{
					$images[] = $result['image_url'];
				}
			}
		}

		// output
		$this->output(self::OK, $images);
	}
}

?>