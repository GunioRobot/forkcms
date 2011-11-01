<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This class holds all methods related to custom fields.
 *
 * @author Dave Lens <dave.lens@netlash.com>
 */
class BackendMailmotorCustomFieldsModel
{
	/**
	 * Inserts the custom fields for a given group. Accepts an optional third parameter $email that will insert the values for that e-mail.
	 *
	 * @todo rework this in multiple methods, there are too many situations/contexts involved.
	 *
	 * @return	bool
	 * @param	array $fields							The fields to insert.
	 * @param	int $groupId							The ID of the group for which the fields will be inserted.
	 * @param	string[optional] $email					The email you want to insert the custom fields for.
	 * @param	int[optional] $customFieldsGroup		If this is set it will only update the custom fields for this group.
	 * @param	bool[optional] $import					This method is called through the import action.
	 */
	public static function insert(array $fields, $groupId, $email = null, $customFieldsGroup = null, $import = false)
	{
		// get DB
		$db = BackendModel::getDB(true);

		// no fields given
		if(empty($fields)) return false;

		// no email address set means we just update the custom fields (ie adding new ones)
		if(!empty($email) && SpoonFilter::isEmail($email))
		{
			// set custom fields values
			$subscription['email'] = $email;
			$subscription['custom_fields'] = serialize($fields);
			$subscription['group_id'] = $groupId;

			$query = '
			INSERT INTO mailmotor_addresses_groups(email, custom_fields, group_id, status, subscribed_on)
			VALUES (?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE custom_fields = ?';

			$parameters = array(
				$subscription['email'],
				$subscription['custom_fields'],
				$subscription['group_id'],
				'subscribed',
				BackendModel::getUTCDate('Y-m-d H:i:s'),
				$subscription['custom_fields']
			);

			// insert/update the user
			$db->execute($query, $parameters);
		}

		// if this is called through the import action OR the given group equals the current ID, we continue
		if($customFieldsGroup == $groupId || $import == true)
		{
			// fetch array keys if $fields isn't a boolean
			if($fields !== false) $fields = array_keys($fields);

			// overwrite custom fields so we only have the keys
			$values['custom_fields'] = serialize($fields);

			// update the field values for this e-mail address
			return (bool) $db->update('mailmotor_groups', $values, 'id = ?', $groupId);
		}
	}


	/**
	 * Updates the custom fields for a given group. Accepts an optional third parameter $email that will update the values for that e-mail.
	 *
	 * @return	int
	 * @param	array $fields				The fields.
	 * @param	int $groupId				The group to update.
	 * @param	string[optional] $email		The email you want to update the custom fields for.
	 */
	public static function update($fields, $groupId, $email = null)
	{
		// get DB
		$db = BackendModel::getDB(true);

		// set values to update
		$values = array();

		// no email address set means we just update the custom fields (ie adding new ones)
		if(!empty($email) && SpoonFilter::isEmail($email))
		{
			// set custom fields values
			$values['custom_fields'] = serialize($fields);

			// update field values for this email
			$db->update('mailmotor_addresses_groups', $values, 'email = ? AND group_id = ?', array($email, (int) $groupId));
		}

		// fetch array keys if $fields isn't a boolean
		if($fields !== false && !isset($fields[0])) $fields = array_keys($fields);

		// overwrite custom fields so we only have the keys
		$values['custom_fields'] = serialize($fields);

		// update the field values for this e-mail address
		return (int) $db->update('mailmotor_groups', $values, 'id = ?', array((int) $groupId));
	}
}
