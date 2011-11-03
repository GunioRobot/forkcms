<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This class holds all methods related to groups.
 *
 * @author Dave Lens <dave.lens@netlash.com>
 */
class BackendMailmotorGroupsModel
{
	const QRY_DATAGRID_BROWSE = '
		SELECT mg.id, mg.name, mg.language, mg.is_default,
		UNIX_TIMESTAMP(mg.created_on) AS created_on
		FROM mailmotor_groups AS mg';

	/**
	 * Returns true if every working language has a default group set, false if at least one is missing.
	 *
	 * @return bool
	 */
	public static function checkDefaults()
	{
		// check if the defaults were set already, and return true if they were
		if(BackendModel::getModuleSetting('mailmotor', 'cm_groups_defaults_set')) return true;

		// get all default groups
		$defaults = BackendMailmotorGroupsModel::getDefaults();

		// if the total amount of working languages do not add up to the total amount of default groups not all default groups were set.
		if(count(BL::getWorkingLanguages()) === count($defaults))
		{
			// cm_groups_defaults_set status is now true
			BackendModel::setModuleSetting('mailmotor', 'cm_groups_defaults_set', true);

			// return true
			return true;
		}

		// if we made it here, not all default groups were set; return false
		return false;
	}

	/**
	 * Deletes one or more groups
	 *
	 * @param mixed $ids
	 */
	public static function delete($ids)
	{
		// make sure ids are set
		if(empty($ids)) return;

		// get DB
		$db = BackendModel::getDB(true);

		// if $ids is not an array, make one
		$ids = (array) $ids;

		// delete records
		$db->delete('mailmotor_groups', 'id IN (' . implode(',', $ids) . ')');
		$db->delete('mailmotor_addresses_groups', 'group_id IN (' . implode(',', $ids) . ')');
		$db->delete('mailmotor_mailings_groups', 'group_id IN (' . implode(',', $ids) . ')');
	}

	/**
	 * Checks if a group exists.
	 *
	 * @param int $id The id of the group to check.
	 * @return bool
	 */
	public static function exists($id)
	{
		return (bool) BackendModel::getDB()->getVar(
			'SELECT COUNT(mg.id)
			 FROM mailmotor_groups AS mg
			 WHERE mg.id = ?',
			array((int) $id)
		);
	}

	/**
	 * Checks if a group exists by a name.
	 *
	 * @param string $name The name of the group to check.
	 * @return bool
	 */
	public static function existsByName($name)
	{
		return (bool) BackendModel::getDB()->getVar(
			'SELECT COUNT(mg.id)
			 FROM mailmotor_groups AS mg
			 WHERE mg.name = ? AND mg.language = ?',
			array(
				(string) $name,
				BL::getWorkingLanguage()
			)
		);
	}

	/**
	 * Get all data for a given group ID.
	 *
	 * @param int $id The id of the group to fetch.
	 * @return array
	 */
	public static function get($id)
	{
		$record = (array) BackendModel::getDB()->getRecord(
			'SELECT mg.*, mci.cm_id
			 FROM mailmotor_groups AS mg
			 INNER JOIN mailmotor_campaignmonitor_ids AS mci ON mci.other_id = mg.id
			 WHERE mci.type = ? AND mg.id = ?',
			array('list', (int) $id)
		);

		// no record found
		if(empty($record)) return array();

		// unserialize the custom fields
		$record['custom_fields'] = ($record['custom_fields'] == null) ? array() : unserialize($record['custom_fields']);

		// return the record
		return (array) $record;
	}

	/**
	 * Get all group records.
	 *
	 * @return array
	 */
	public static function getAll()
	{
		$records = (array) BackendModel::getDB()->getRecords(
			'SELECT mg.id, mg.name, mci.cm_id, mg.custom_fields
			 FROM mailmotor_groups AS mg
			 INNER JOIN mailmotor_campaignmonitor_ids AS mci ON mci.other_id = mg.id
			 WHERE mci.type = ?',
			array('list'),
			'id'
		);

		// no records found
		if(empty($records)) return array();

		// loop the records
		foreach($records as &$record)
		{
			// unserialize the custom fields
			$record['custom_fields'] = ($record['custom_fields'] == null) ? array() : unserialize($record['custom_fields']);
		}

		// return the records
		return (array) $records;
	}

	/**
	 * Get all groups in key/value pairs.
	 *
	 * @param string $email The emailaddress to get the groups for.
	 * @return array
	 */
	public static function getAllByEmailAsPairs($email)
	{
		return (array) BackendModel::getDB()->getPairs(
			'SELECT mg.id, mg.name
			 FROM mailmotor_groups AS mg
			 INNER JOIN mailmotor_addresses_groups AS mag ON mag.group_id = mg.id
			 WHERE mag.email = ? AND mag.status <> ?',
			array((string) $email, 'unsubscribed')
		);
	}

	/**
	 * Get all groups by their IDs.
	 *
	 * @param array $ids The ids of the required groups.
	 * @return array
	 */
	public static function getAllByIds($ids)
	{
		// no ids set = stop here
		if(empty($ids)) return false;

		// check if an array was given
		$ids = (array) $ids;

		// return records
		return (array) BackendModel::getDB()->getRecords(
			'SELECT mg.id, mg.name, mci.cm_id
			 FROM mailmotor_groups AS mg
			 INNER JOIN mailmotor_campaignmonitor_ids AS mci ON mci.other_id = mg.id
			 WHERE mci.type = ? AND mg.id IN (' . implode(',', $ids) . ')',
			array('list'),
			'id'
		);
	}

	/**
	 * Get all groups in a format acceptable for SpoonForm::addRadioButton().
	 *
	 * @return array
	 */
	public static function getAllForCheckboxes()
	{
		return (array) BackendModel::getDB()->getRecords(
			'SELECT mg.id AS value, mg.name AS label
			 FROM mailmotor_groups AS mg
			 GROUP BY mg.id'
		);
	}

	/**
	 * Get all groups with recipients in a format acceptable for SpoonForm::addRadioButton().
	 *
	 * @return array
	 */
	public static function getAllForCheckboxesWithRecipients()
	{
		$records = (array) BackendModel::getDB()->getRecords(
			'SELECT mg.id AS value, mg.name AS label, COUNT(mag.email) AS recipients
			 FROM mailmotor_groups AS mg
			 LEFT OUTER JOIN mailmotor_addresses_groups AS mag ON mag.group_id = mg.id
			 WHERE status = ?
			 GROUP BY mg.id',
			array('subscribed')
		);

		// no records found
		if(empty($records)) return array();

		// loop the records
		foreach($records as &$record)
		{
			// store variables array
			$record['variables'] = array(
				'recipients' => ($record['recipients'] != 0) ? $record['recipients'] : false,
				'single' => ($record['recipients'] == 1) ? true : false
			);

			// unset the recipients from this stack
			unset($record['recipients']);
		}

		// return records
		return $records;
	}

	/**
	 * Get all group IDs
	 *
	 * @return array
	 */
	public static function getAllIDs()
	{
		return (array) BackendModel::getDB()->getColumn('SELECT mg.id FROM mailmotor_groups AS mg');
	}

	/**
	 * Get all groups for a given e-mail address.
	 *
	 * @param string $email The emailaddress to get the groupID for.
	 * @return array
	 */
	public static function getAllIDsByEmail($email)
	{
		return (array) BackendModel::getDB()->getColumn(
			'SELECT mg.id
			 FROM mailmotor_groups AS mg
			 LEFT OUTER JOIN mailmotor_addresses_groups AS mag ON mag.group_id = mg.id
			 WHERE mag.email = ? AND status = ?
			 GROUP BY mg.id',
			array($email, 'subscribed')
		);
	}

	/**
	 * Get all groups for a given mailing ID.
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getAllIDsByMailingID($id)
	{
		return (array) BackendModel::getDB()->getColumn(
			'SELECT mmg.group_id
			 FROM mailmotor_mailings AS mm
			 LEFT OUTER JOIN mailmotor_mailings_groups AS mmg ON mmg.mailing_id = mm.id
			 WHERE mmg.mailing_id = ?
			 GROUP BY mmg.group_id',
			array($id));
	}

	/**
	 * Get all custom fields for a given group ID.
	 *
	 * @param int $groupId
	 * @return array
	 */
	public static function getCustomFields($groupId)
	{
		$group = self::get($groupId);

		return (array) $group['custom_fields'];
	}

	/**
	 * Returns the default group ID.
	 *
	 * @param string[optional] $language Accepts an abbreviation (ex: en, nl, fr,...)
	 * @return int
	 */
	public static function getDefaultID($language = null)
	{
		// filter input
		$language = empty($language) ? BL::getWorkingLanguage() : (string) $language;

		// return the group ID
		return (int) BackendModel::getDB()->getVar(
			'SELECT mg.id
			 FROM mailmotor_groups AS mg
			 WHERE mg.is_default = ? AND mg.language = ?
			 LIMIT 1',
			array('Y', $language)
		);
	}

	/**
	 * Returns the default group IDs
	 *
	 * @return array
	 */
	public static function getDefaultIDs()
	{
		return (array) BackendModel::getDB()->getColumn(
			'SELECT mg.id
			 FROM mailmotor_groups AS mg
			 WHERE mg.is_default = ?',
			array('Y'));
	}

	/**
	 * Returns the default group records.
	 *
	 * @return array
	 */
	public static function getDefaults()
	{
		return (array) BackendModel::getDB()->getRecords(
			'SELECT mg.id, mg.language, mg.name, mg.created_on
			 FROM mailmotor_groups AS mg
			 WHERE mg.is_default = ?',
			array('Y'),
			'language'
		);
	}

	/**
	 * Get the maximum id for groups.
	 *
	 * @return int
	 */
	public static function getMaximumId()
	{
		return (int) BackendModel::getDB()->getVar(
			'SELECT MAX(id)
			 FROM mailmotor_groups
			 LIMIT 1'
		);
	}

	/**
	 * Inserts a new group into the database,
	 *
	 * @param array $item The data to insert for the group.
	 * @return int
	 */
	public static function insert(array $item)
	{
		$db = BackendModel::getDB(true);
		$workingLanguage = BL::getWorkingLanguage();

		$defaultGroupSet = (bool) $db->getVar(
			'SELECT COUNT(mg.id)
			 FROM mailmotor_groups AS mg
			 WHERE mg.is_default = ? AND mg.language = ?',
			array('Y', $workingLanguage)
		);

		if($defaultGroupSet === false)
		{
			// this list will be a default list
			$item['language'] = BL::getWorkingLanguage();
			$item['is_default'] = 'Y';
		}

		return (int) $db->insert('mailmotor_groups', $item);
	}

	/**
	 * Updates a group record.
	 *
	 * @param array $item The data to update for the group.
	 * @return int
	 */
	public static function update(array $item)
	{
		return BackendModel::getDB(true)->update(
			'mailmotor_groups',
			$item,
			'id = ?',
			array($item['id'])
		);
	}

	/**
	 * Updates the groups for a given mailing
	 *
	 * @param int $mailingId The id of the mailing.
	 * @param array $groupIds A list of group-ids.
	 */
	public static function updateForMailing($mailingId, $groupIds)
	{
		// get DB
		$db = BackendModel::getDB(true);

		// delete all groups for this mailing
		$db->delete('mailmotor_mailings_groups', 'mailing_id = ?', array((int) $mailingId));

		// stop here if groups are empty
		if(empty($groupIds)) return false;

		// insert record(s)
		foreach($groupIds as $id)
		{
			// set variables
			$variables = array();
			$variables['mailing_id'] = (int) $mailingId;
			$variables['group_id'] = (int) $id;

			// update
			$db->insert('mailmotor_mailings_groups', $variables);
		}
	}
}
