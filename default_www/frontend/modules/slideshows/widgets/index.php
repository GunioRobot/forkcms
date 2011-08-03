<?php

/**
 * This is a widget that loads in a new slideshow based on its settings.
 *
 * @package		frontend
 * @subpackage	slideshows
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.5
 */
class FrontendSlideshowsWidgetIndex extends FrontendBaseWidget
{
	/**
	 * The dataset to populate the slideshow with
	 *
	 * @var	array
	 */
	private $items = array();


	/**
	 * The slideshow record
	 *
	 * @var	array
	 */
	private $slideshow;


	/**
	 * Execute the extra
	 */
	public function execute()
	{
		parent::execute();

		$this->loadTemplate();

		// If we succesfully gotten data, we can load the slideshow type
		if($this->getData())
		{
			$this->loadSlideshowType();
		}

		else
		{
			// @todo show a friendly message stating there are no images in the slideshow right now.
		}

		$this->parse();
	}


	/**
	 * Fetches the dataset we need based on the slideshow's settings.
	 *
	 * @return	bool	Returns true if the data loaded succesfully.
	 */
	private function getData()
	{
		$this->slideshow = FrontendSlideshowsModel::get($this->data['id']);

		if(empty($this->slideshow)) return false;

		// both the module and the callable method should be set
		if($this->slideshow['module'] !== null && $this->slideshow['dataset_id'] !== null)
		{
			$this->loadEngineFiles();

			// get the dataset method, so we can call it
			$method = FrontendSlideshowsModel::getDataSetMethod($this->slideshow['dataset_id']);

			$this->items = call_user_func($method);
		}

		// this is a regular slideshow, and the user manages the images.
		else
		{
			$this->items = FrontendSlideshowsModel::getImages($this->data['id']);
		}

		// return true when data was set
		return !empty($this->items);
	}


	/**
	 * Loads all engine files of the module linked to this slideshow.
	 */
	private function loadEngineFiles()
	{
		$enginePath = FRONTEND_MODULES_PATH . '/' . $this->slideshow['module'] . '/engine';
		$engineFiles = SpoonFile::getList($enginePath);

		if(empty($engineFiles))
		{
			throw new Exception('You specified a module, but it has no model files!');
		}

		// get the dataset method, so we can call it
		$method = FrontendSlideshowsModel::getDataSetMethod($this->slideshow['dataset_id']);

		foreach($engineFiles as $file)
		{
			require_once $enginePath . '/' . $file;
		}

		// check if the method is callable/exists
		if(!is_callable($method))
		{
			throw new Exception($method . ' is not a valid callable method!');
		}
	}


	/**
	 * Loads the slideshow type's related files and settings
	 */
	private function loadSlideshowType()
	{
		$frontendThemePath = '/frontend/modules/slideshows/layout';
		$cssFile = FrontendTheme::getPath($frontendThemePath . '/css/' . $this->slideshow['type'] . '.css');
		$templateFile = FrontendTheme::getPath($frontendThemePath . '/templates/' . $this->slideshow['type'] . '.tpl');

		// add the CSS file for the active type
		if(SpoonFile::exists(PATH_WWW . $cssFile))
		{
			$this->addCSS($cssFile, true);
		}

		// add the javascript for the active type
		$this->addJS('slides.js');
		$this->addJS($this->slideshow['type'] . '.js');

		$templatePath = PATH_WWW . $templateFile;

		// set the slideshow template
		$this->slideshow['template'] = $templatePath;
	}


	/**
	 * Caches the widget and parses stuff into the template
	 */
	private function parse()
	{
		$cacheID = FRONTEND_LANGUAGE . '_slideshowCache_' . $this->slideshow['id'];

		// we will cache this widget for 15minutes
		$this->tpl->cache($cacheID, (24 * 60 * 60));

		// if the widget isn't cached, assign the variables
		if(!$this->tpl->isCached($cacheID))
		{
			$this->tpl->assign('slideshow', $this->slideshow);
			$this->tpl->assign('items', $this->items);
		}
	}
}

?>