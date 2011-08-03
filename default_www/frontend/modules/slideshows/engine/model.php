<?php

/**
 * In this file we store all generic DB functions that we will be using in the slideshows module
 *
 * @package		frontend
 * @subpackage	slideshows
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.5
 */
class FrontendSlideshowsModel
{
	/**
	 * Returns the slideshow record
	 *
	 * @return	array
	 * @param	int $id		The ID of the slideshow to return.
	 */
	public static function get($id)
	{
		$db = FrontendModel::getDB();

		$item = $db->getRecord('SELECT a.*, b.type, b.settings
								FROM slideshows AS a
								INNER JOIN slideshows_types AS b ON b.id = a.type_id
								WHERE
								a.id = ?',
								array((int) $id));

		$item['settings'] = unserialize($item['settings']);

		return $item;
	}


	/**
	 * Returns the dataset method
	 *
	 * @return	string
	 */
	public static function getDataSetMethod($id)
	{
		$db = FrontendModel::getDB();

		return $db->getVar('SELECT i.method
							FROM slideshows_datasets AS i
							WHERE
							i.id = ?',
							array($id));
	}


	/**
	 * Returns the images for a given slideshow ID
	 *
	 * @return	array
	 * @param	int $slideshowID	The ID of the slideshow to fetch the images for.
	 */
	public static function getImages($slideshowID)
	{
		$db = FrontendModel::getDB();

		$records = $db->getRecords('SELECT a.*
									FROM slideshows_images AS a
									WHERE
									a.slideshow_id = ?
									ORDER BY a.sequence',
									array((int) $slideshowID));

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