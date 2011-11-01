<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This class holds all methods related to subscribers.
 *
 * @author Dave Lens <dave.lens@netlash.com>
 */
class BackendMailmotorAddressesModel
{
	/**
	 * Deletes one or more e-mail addresses
	 *
	 * @param  mixed $emails The emails to delete.
	 */
	public static function delete($emails)
	{
		// make sure emails are set
		if(empty($emails)) return;

		// get DB
		$db = BackendModel::getDB(true);

		// if $ids is not an array, make one
		$emails = (array) $emails;

		// delete records
		$db->delete('mailmotor_addresses', 'email IN ("' . implode('","', $emails) . '")');
		$db->delete('mailmotor_addresses_groups', 'email IN ("' . implode('","', $emails) . '")');
	}

	/**
	 * Checks if an e-mailaddress exists
	 *
	 * @param string $email The emailaddress to check for existance.
	 * @return bool
	 */
	public static function exists($email)
	{
		$db = BackendModel::getDB();

		$query = '
		SELECT ma.email
		FROM mailmotor_addresses AS ma
		WHERE
		ma.email = ?';
		$parameters = array($email);

		return (bool) $db->getNumRows($query, $parameters);
	}

	/**
	 * Exports a series of e-mail address records in CSV format.
	 * This function will send headers to download the CSV and exit your script after use.
	 *
	 * @param array $emails The data to export.
	 */
	public static function export(array $emails)
	{
		// set the filename and path
		$filename = 'addresses-' . SpoonDate::getDate('YmdHi') . '.csv';
		$path = BACKEND_CACHE_PATH . '/mailmotor/' . $filename;

		// reformat the created_on date
		if(!empty($emails))
		{
			foreach($emails as &$email)
			{
				$email['created_on'] = SpoonDate::getDate('j F Y', $email['created_on'], BL::getWorkingLanguage());
			}
		}

		// generate the CSV and download the file
		SpoonFileCSV::arrayToFile($path, $emails, array(BL::lbl('Email'), BL::lbl('Created')), null, ';', '"', true);
	}

	/**
	 * Exports a series of e-mail address records by group ID in CSV format.
	 * This function will send headers to download the CSV and exit your script after use.
	 *
	 * @param int $id The id of the group to export.
	 */
	public static function exportByGroupID($id)
	{
		// set the filename and path
		$filename = 'addresses-' . SpoonDate::getDate('YmdHi') . '.csv';
		$path = BACKEND_CACHE_PATH . '/mailmotor/' . $filename;

		// fetch the addresses by group
		$records = self::getByGroupID($id);

		// fetch the group fields
		$groupFields = array_flip(BackendMailmotorGroupsModel::getCustomFields($id));

		// group custom fields found
		if(!empty($groupFields))
		{
			// loop the group fields and empty every value
			foreach($groupFields as &$field) $field = '';
		}

		// records found
		if(!empty($records))
		{
			// loop records
			foreach($records as $key => $record)
			{
				// reformat the date
				$records[$key]['created_on'] = SpoonDate::getDate('j F Y', $record['created_on'], BL::getWorkingLanguage());

				// fetch custom fields for this e-mail
				$customFields = self::getCustomFields($record['email']);
				$customFields = !empty($customFields[$id]) ? $customFields[$id] : $groupFields;

				// loop custom fields
				foreach($customFields as $column => $value)
				{
					// add the fields to this record
					$records[$key][$column] = $value;
				}
			}
		}

		// generate the CSV and download the file
		SpoonFileCSV::arrayToFile($path, $records, array(BL::lbl('Email'), BL::lbl('Created')), null, ';', '"', true);
	}

	/**
	 * Get an e-mail address record
	 *
	 * @param string $email The emailaddress to get.
	 * @return array
	 */
	public static function get($email)
	{
		// get record and return it
		$record = BackendModel::getDB()->getRecord('SELECT ma.*
													FROM mailmotor_addresses AS ma
													WHERE ma.email = ?', array((string) $email));

		// no record means we stop here
		if(empty($record)) return array();

		// fetch groups for this address
		$record['groups'] = (array) BackendMailmotorGroupsModel::getAllIDsByEmail($email);
		$record['custom_fields'] = array();

		// user is subscribed to groups
		if(!empty($record['groups']))
		{
			// reserve custom fields array
			$record['custom_fields'] = self::getCustomFields($email);
		}

		// return the record
		return $record;
	}

	/**
	 * Get all e-mail addresses
	 *
	 * @param int[optional] $limit Maximum number of addresses to get.
	 * @return array
	 */
	public static function getAll($limit = null)
	{
		// build query
		$query = 'SELECT ma.email, UNIX_TIMESTAMP(ma.created_on) AS created_on
					FROM mailmotor_addresses AS ma
					ORDER BY ma.created_on DESC';

		// set parameters
		$parameters = array();

		// check if a limit was set
		if(!empty($limit))
		{
			// add limit to query and parameters
			$query .= ' LIMIT ?';
			$parameters[] = $limit;
		}

		// get record and return it
		return (array) BackendModel::getDB()->getRecords($query, $parameters);
	}

	/**
	 * Get all e-mail addresses as pairs
	 *
	 * @return array
	 */
	public static function getAllAsPairs()
	{
		// get record and return it
		return BackendModel::getDB()->getColumn('SELECT ma.email
													FROM mailmotor_addresses AS ma');
	}

	/**
	 * Get the e-mail addresses by group ID(s)
	 *
	 * @param array $ids The ids of the groups.
	 * @param bool[optional] $getColumn If this is true, the function returns a column of addresses instead.
	 * @param int[optional] $limit Maximum number if addresses to return.
	 * @return array
	 */
	public static function getByGroupID($ids, $getColumn = false, $limit = null)
	{
		// make sure ids are set
		if(empty($ids)) return array();

		// check if an array was given
		$ids = (array) $ids;

		// get DB
		$db = BackendModel::getDB();

		// build query
		$query = 'SELECT ma.email, UNIX_TIMESTAMP(ma.created_on) AS created_on
					FROM mailmotor_addresses AS ma
					INNER JOIN mailmotor_addresses_groups AS mag ON mag.email = ma.email
					INNER JOIN mailmotor_groups AS mg ON mg.id = mag.group_id
					WHERE mag.status = ? AND mag.group_id IN (' . implode(',', $ids) . ')
					GROUP BY ma.email';

		// set parameters
		$parameters = array('subscribed');

		// limit was found
		if(!empty($limit))
		{
			$query .= ' LIMIT ?';
			$parameters[] = $limit;
		}

		// get record and return it
		if(!$getColumn) return (array) $db->getRecords($query, $parameters);

		// don't fetch a column of addresses
		return (array) $db->getColumn($query, $parameters);
	}

	/**
	 * Get all custom fields and their values for a given e-mail address
	 *
	 * @param string $email The emailaddress to get the custom fields for.
	 * @return array
	 */
	public static function getCustomFields($email)
	{
		// email is not valid
		if(!SpoonFilter::isEmail($email)) throw new SpoonException('No valid e-mail given.');

		// fetch all group IDs
		$groupIds = BackendMailmotorGroupsModel::getAllIDs();

		// no groups found = stop here
		if(empty($groupIds)) return array();

		// fetch address group records
		$records = BackendModel::getDB()->getRecords('SELECT mag.group_id, mag.custom_fields
														FROM mailmotor_addresses_groups AS mag
														WHERE mag.email = ? AND mag.group_id IN (' . implode(',', $groupIds) . ')',
														array($email), 'group_id');

		// no records found = stop here
		if(empty($records)) return array();

		// loop the caught records and unserialize the fields
		foreach($records as $key => $record)
		{
			// unserialize the custom fields
			$records[$key] = unserialize($record['custom_fields']);
		}

		// return the fields
		return (array) $records;
	}

	/**
	 * Get the unsubscribed e-mail addresses by group ID(s)
	 *
	 * @param mixed $ids The ids of the groups.
	 * @return array
	 */
	public static function getUnsubscribersByGroupID($ids)
	{
		// check input
		if(empty($ids)) return array();

		// check if an array was given
		$ids = (array) $ids;

		// get record and return it
		return (array) BackendModel::getDB()->getRecords('SELECT ma.email, UNIX_TIMESTAMP(ma.created_on) AS created_on
															FROM mailmotor_addresses AS ma
															INNER JOIN mailmotor_addresses_groups AS mag ON mag.email = ma.email
															INNER JOIN mailmotor_groups AS mg ON mg.id = mag.group_id
															WHERE mag.group_id IN (' . implode(',', $ids) . ') AND mag.status = ?
															GROUP BY ma.email',
															array('unsubscribed'));
	}

	/**
	 * Inserts a new e-mail address into the database
	 *
	 * @param array $item The data to insert for the address.
	 */
	public static function insert(array $item)
	{
		// get DB
		$db = BackendModel::getDB(true);

		// set record values
		$record = array();
		$record['email'] = $item['email'];
		$record['source'] = $item['source'];
		$record['created_on'] = $item['created_on'];

		// insert record
		$db->insert('mailmotor_addresses', $record);

		// no groups = stop here
		if(empty($item['groups'])) return;

		// check if groups was an array or not
		$item['groups'] = (array) $item['groups'];

		// insert record(s)
		foreach($item['groups'] as $id)
		{
			// set variables
			$variables = array();
			$variables['group_id'] = $id;
			$variables['status'] = 'subscribed';
			$variables['subscribed_on'] = BackendModel::getUTCDate('Y-m-d H:i:s');
			$variables['email'] = $item['email'];

			// insert the record
			$db->insert('mailmotor_addresses_groups', $variables);
		}
	}

	/**
	 * Checks if a given e-mail address is subscribed to the given group
	 *
	 * @param string $email The emailaddress to check
	 * @param int $groupId The id of the group
	 * @return bool
	 */
	public static function isSubscribed($email, $groupID)
	{
		return (bool) BackendModel::getDB()->getVar('SELECT COUNT(ma.email)
														FROM mailmotor_addresses AS ma
														INNER JOIN mailmotor_addresses_groups AS mag ON mag.email = ma.email
														WHERE ma.email = ? AND mag.group_id = ? AND mag.status = ?',
														array($email, $groupID, 'subscribed'));
	}

	/**
	 * Inserts or updates a subscriber record.
	 *
	 * @param array $item The data to update for the e-mail address.
	 * @param int $groupId The group to subscribe the address to.
	 * @param array[optional] $fields The custom fields for the address in the given group.
	 * @return bool
	 */
	public static function save(array $item, $groupId, $fields = array())
	{
		// get DB
		$db = BackendModel::getDB(true);

		// set record values
		$record['email'] = $item['email'];
		$record['source'] = $item['source'];
		$record['created_on'] = $item['created_on'];

		// insert/update the user
		$db->execute(
			'INSERT INTO mailmotor_addresses(email, source, created_on)
			 VALUES (?, ?, ?)
			 ON DUPLICATE KEY UPDATE email = ?',
			 array(
			 	$record['email'], $record['source'], $record['created_on'],
				$record['email']
			 )
		);

		// set values
		$subscription['email'] = $item['email'];
		$subscription['custom_fields'] = serialize($fields);
		$subscription['group_id'] = $groupId;

		// insert/update the user
		$db->execute(
			'INSERT INTO mailmotor_addresses_groups(email, custom_fields, group_id, status, subscribed_on)
			 VALUES (?, ?, ?, ?, ?)
			 ON DUPLICATE KEY UPDATE custom_fields = ?',
			 array(
			 	$subscription['email'],
			 	$subscription['custom_fields'],
			 	$subscription['group_id'],
			 	'subscribed',
			 	BackendModel::getUTCDate(),
				$subscription['custom_fields']
			)
		);
	}

	/**
	 * Subscribes an e-mailadress to the given group
	 *
	 * @param string $email
	 * @param int $groupID
	 */
	public static function subscribe($email, $groupID)
	{
		$db = BackendModel::getDB(true);

		$variables = array();
		$variables['email'] = $email;
		$variables['group_id'] = $groupID;
		$variables['status'] = 'subscribed';
		$variables['subscribed_on'] = BackendModel::getUTCDate('Y-m-d H:i:s');

		if(self::isSubscribed($email, $groupID))
		{
			$db->update('mailmotor_addresses_groups', $variables, 'email = ?', $email);
		}
		else
		{
			$db->insert('mailmotor_addresses_groups', $variables);
		}
	}

	/**
	 * Subscribes an e-mailadress to the given group
	 *
	 * @param string $email
	 * @param int $groupID
	 */
	public static function unsubscribe($email, $groupID)
	{
		$db = BackendModel::getDB(true);
		$db->delete('mailmotor_addresses_groups', 'email = ? AND group_id = ?', array($email, $groupID));
	}

	/**
	 * Inserts a new e-mail address into the database
	 *
	 * @param array $item The data to insert for the address.
	 */
	public static function update(array $item)
	{
		// get DB
		$db = BackendModel::getDB(true);

		// set record values
		$record = array();
		$record['email'] = $item['email'];
		$record['source'] = $item['source'];

		// insert record
		$db->update('mailmotor_addresses', $record, 'email = ?', $record['email']);

		// no groups = stop here
		if(empty($item['groups'])) return;

		// check if groups was an array or not
		$item['groups'] = (array) $item['groups'];

		$db->delete('mailmotor_addresses_groups', 'email = ?', $record['email']);

		// insert record(s)
		foreach($item['groups'] as $groupID)
		{
			self::subscribe($item['email'], $groupID);
		}
	}
}
