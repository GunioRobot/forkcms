<?php

/**
 * In this file we store all generic functions that we will be using in the slideshows module
 *
 * @package		backend
 * @subpackage	slideshows
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.5
 */
class BackendSlideshowsModel
{
	/**
	 * Overview of all slideshows
	 *
	 * @var	string
	 */
	const QRY_DATAGRID_BROWSE = 'SELECT i.id, i.name, i.module, a.type
									FROM slideshows AS i
									INNER JOIN slideshows_types AS a ON a.id = i.type_id
									WHERE i.language = ?
									GROUP BY i.id';

	/**
	 * Overview of all images for a slideshow
	 *
	 * @var	string
	 */
	const QRY_DATAGRID_BROWSE_IMAGES = 'SELECT
										i.id, i.slideshow_id, i.filename, i.title,
										i.caption, i.sequence
										FROM slideshows_images AS i
										WHERE i.slideshow_id = ?
										GROUP BY i.id';


	/**
	 * Deletes a slideshow.
	 *
	 * @return	void
	 * @param 	mixed $ids	The ids to delete.
	 */
	public static function delete($ids)
	{
		$db = BackendModel::getDB(true);

		if(!is_array($ids)) $ids = array($ids);

		$imageIDs = $db->getColumn('SELECT id
									FROM slideshows_images
									WHERE
									slideshow_id IN(' . implode(',', $ids) . ')');

		if(!empty($imageIDs))
		{
			self::deleteImage($imageIDs);
		}

		foreach($ids as $id)
		{
			SpoonDirectory::delete(FRONTEND_FILES_PATH . '/slideshows/' . $id);

			$slideshow = self::get($id);

			// build extra
			$extra = array('id' => $slideshow['extra_id'],
							'module' => 'slideshows',
							'type' => 'widget',
							'action' => 'index');

			// delete extra
			$db->delete(
				'pages_extras',
				'id = ? AND module = ? AND type = ? AND action = ?',
				array($extra['id'], $extra['module'], $extra['type'], $extra['action'])
			);

			$db->delete('slideshows', 'id IN(' . implode(',', $ids) . ')');
		}
	}


	/**
	 * Deletes slideshow images
	 *
	 * @return	void
	 * @param 	mixed $ids	The ids to delete.
	 */
	public static function deleteImage($ids)
	{
		$db = BackendModel::getDB(true);

		if(!is_array($ids)) $ids = array($ids);

		if(empty($ids)) return;

		foreach($ids as $id)
		{
			$item = self::getImage($id);
			$slideshow = self::get($item['slideshow_id']);

			$db->delete('slideshows_images', 'id = ?', $id);

			SpoonFile::delete(FRONTEND_FILES_PATH . '/slideshows/' . $item['slideshow_id'] . '/source/' . $item['filename']);
			SpoonFile::delete(FRONTEND_FILES_PATH . '/slideshows/' . $item['slideshow_id'] . '/64x64/' . $item['filename']);
			SpoonFile::delete(FRONTEND_FILES_PATH . '/slideshows/' . $item['slideshow_id'] . '/' . $slideshow['format'] . '/' . $item['filename']);
		}
	}


	/**
	 * Check if a slideshow exists.
	 *
	 * @return	bool
	 * @param	int $id		The id to check for existence.
	 */
	public static function exists($id)
	{
		return (bool) BackendModel::getDB()->getVar('SELECT i.id
														FROM slideshows AS i
														WHERE i.id = ?',
														array((int) $id));
	}


	/**
	 * Check if a slideshow image exists.
	 *
	 * @return	bool
	 * @param	int $id		The id to check for existence.
	 */
	public static function existsImage($id)
	{
		return (bool) BackendModel::getDB()->getVar('SELECT i.id
														FROM slideshows_images AS i
														WHERE i.id = ?',
														array((int) $id));
	}


	/**
	 * Get slideshow record.
	 *
	 * @return	array
	 * @param	int $id		The id of the record to get.
	 */
	public static function get($id)
	{
		return BackendModel::getDB()->getRecord('SELECT i.*,
													CONCAT(i.width, "x", i.height) AS format
													FROM slideshows AS i
													WHERE i.id = ?',
													array((int) $id));
	}


	/**
	 * Get slideshow image record.
	 *
	 * @return	array
	 * @param	int $id		The id of the record to get.
	 */
	public static function getImage($id)
	{
		$db = BackendModel::getDB();

		$item = $db->getRecord('SELECT i.*, i.filename AS image, a.width, a.height
								FROM slideshows_images AS i
								INNER JOIN slideshows AS a ON a.id = i.slideshow_id
								WHERE i.id = ?',
								array((int) $id));

		$item['image_url'] = FRONTEND_FILES_URL . '/slideshows/' . $item['slideshow_id'] . '/' . $item['width'] . 'x' . $item['height'] . '/' . $item['image'];

		return $item;
	}


	/**
	 * Get images from slideshow
	 *
	 * @return	array
	 * @param	int $slideshowID		The ID of the slideshow to fetch the images for.
	 */
	public static function getImages($slideshowID)
	{
		$db = BackendModel::getDB();

		return $db->getRecords(self::QRY_DATAGRID_BROWSE_IMAGES, (int) $slideshowID);
	}


	/**
	 * Get the slideshow types as pairs
	 *
	 * @return	array
	 */
	public static function getTypesAsPairs()
	{
		$db = BackendModel::getDB();

		return $db->getPairs('SELECT id, type
								FROM slideshows_types');
	}


	/**
	 * Get the slideshow type settings for the given type ID
	 *
	 * @return	array
	 * @param	int $id	The ID of the type to fetch.
	 */
	public static function getTypeSettings($id)
	{
		$db = BackendModel::getDB();

		$settings = $db->getVar('SELECT settings
									FROM slideshows_types
									WHERE id = ?', (int) $id);

		return unserialize($settings);
	}


	/**
	 * Insert a new slideshow
	 *
	 * @return	int
	 * @param	string $item	The data for the slideshow.
	 */
	private static function insert($item)
	{
		$db = BackendModel::getDB(true);

		// build extra
		$extra = array('module' => 'slideshows',
						'type' => 'widget',
						'label' => 'Slideshows',
						'action' => 'index',
						'data' => null,
						'hidden' => 'N',
						'sequence' => $db->getVar('SELECT MAX(i.sequence) + 1
													FROM pages_extras AS i
													WHERE i.module = ?', array('slideshows')));
		if(is_null($extra['sequence']))
		{
			$extra['sequence'] = $db->getVar('SELECT CEILING(MAX(i.sequence) / 1000) * 1000
												FROM pages_extras AS i');
		}

		// insert extra
		$item['extra_id'] = $db->insert('pages_extras', $extra);
		$extra['id'] = $item['extra_id'];

		// insert and return the new id
		$item['id'] = $db->insert('slideshows', $item);

		// update extra (item id is now known)
		$extra['data'] = serialize(array('id' => $item['id'],
											'extra_label' => ucfirst(BL::lbl('Slideshows', 'core')) . ': ' . $item['name'],
											'language' => $item['language'],
											'edit_url' => BackendModel::createURLForAction('edit') . '&id=' . $item['id']));
		$db->update('pages_extras', $extra, 'id = ? AND module = ? AND type = ? AND action = ?', array($extra['id'], $extra['module'], $extra['type'], $extra['action']));

		return $item['id'];
	}


	/**
	 * Insert a new slideshow image
	 *
	 * @return	int
	 * @param	string $item	The data for the image.
	 */
	private static function insertImage($item)
	{
		return (int) BackendModel::getDB(true)->insert('slideshows_images', $item);
	}


	/**
	 * Saves a slideshow record
	 *
	 * @return	int
	 * @param	array $item		The record to save.
	 */
	public static function save($item)
	{
		if(isset($item['id']) && self::exists($item['id']))
		{
			self::update($item);

			$id = $item['id'];
		}

		else
		{
			$id = self::insert($item);
		}

		return $id;
	}


	/**
	 * Saves a slideshow image record
	 *
	 * @return	int
	 * @param	array $item		The image record to save.
	 */
	public static function saveImage($item)
	{
		if(isset($item['id']) && self::existsImage($item['id']))
		{
			self::updateImage($item);

			$id = $item['id'];
		}

		else
		{
			$id = self::insertImage($item);
		}

		return $id;
	}


	/**
	 * Update a slideshow
	 * Remark: $slideshow['id'] should be available.
	 *
	 * @return	int	The amount of updated records
	 * @param	array $item		The new data for the slideshow.
	 */
	private static function update($item)
	{
		$db = BackendModel::getDB(true);

		// build extra
		$extra = array('id' => $item['extra_id'],
						'module' => 'slideshows',
						'type' => 'widget',
						'label' => 'Slideshows',
						'action' => 'index',
						'data' => serialize(array('id' => $item['id'],
													'extra_label' => ucfirst(BL::lbl('Slideshows', 'core')) . ': ' . $item['name'],
													'language' => $item['language'],
													'edit_url' => BackendModel::createURLForAction('edit') . '&id=' . $item['id'])),
						'hidden' => 'N');

		// update extra
		$db->update(
			'pages_extras',
			$extra,
			'id = ? AND module = ? AND type = ? AND action = ?',
			array($extra['id'], $extra['module'], $extra['type'], $extra['action'])
		);

		return $db->update('slideshows', $item, 'id = ?', $item['id']);
	}


	/**
	 * Update a slideshow image
	 *
	 * @return	int	The amount of updated records
	 * @param	array $item		The new data for the slideshow.
	 */
	private static function updateImage($item)
	{
		$db = BackendModel::getDB(true);

		return $db->update('slideshows_images', $item, 'id = ?', $item['id']);
	}
}

?>