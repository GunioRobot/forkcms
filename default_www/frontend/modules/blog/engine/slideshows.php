<?php

class FrontendBlogSlideshowsModel
{
	public static function getImages()
	{
		$db = FrontendModel::getDB();

		$records = $db->getRecords('SELECT a.*
									FROM slideshows_images AS a
									WHERE
									a.slideshow_id = ?
									ORDER BY a.sequence',
									array((int) 1));

		if(empty($records)) return array();

		// fetch the slideshow so we know the measurements
		$slideshow = self::get($slideshowID);

		// define the format and the slideshow image folder
		$format = $slideshow['width'] . 'x' . $slideshow['height'];
		$slideshowImageURI = SITE_URL . '/' . FRONTEND_FILES_URL . '/slideshows/' . $slideshowID;

		foreach($records as $key => &$record)
		{
			$record['index'] = $key;
			$record['image_url'] = $slideshowImageURI . '/' . $format . '/' . $record['filename'];
		}

		return $records;
	}
}

?>