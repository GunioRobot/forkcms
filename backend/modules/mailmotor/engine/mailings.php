<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This class holds all methods related to mailings.
 *
 * @author Dave Lens <dave.lens@netlash.com>
 */
class BackendMailmotorMailingsModel
{
	const QRY_DATAGRID_BROWSE_SENT =
		'SELECT mm.id, mm.name, mc.id AS campaign_id, mc.name AS campaign_name,
		 UNIX_TIMESTAMP(mm.send_on) AS sent, mm.language, mm.status
		 FROM mailmotor_mailings AS mm
		 LEFT OUTER JOIN mailmotor_campaigns AS mc ON mc.id = mm.campaign_id
		 WHERE mm.status = ?';

	const QRY_DATAGRID_BROWSE_SENT_FOR_CAMPAIGN =
		'SELECT mm.id, mm.name, mc.id AS campaign_id, mc.name AS campaign_name,
		 UNIX_TIMESTAMP(mm.send_on) AS sent, mm.language, mm.status
		 FROM mailmotor_mailings AS mm
		 INNER JOIN mailmotor_campaigns AS mc ON mc.id = mm.campaign_id
		 WHERE mm.status = ? AND mm.campaign_id = ?';

	const QRY_DATAGRID_BROWSE_UNSENT =
		'SELECT mm.id, mm.name, mc.id AS campaign_id, mc.name AS campaign_name,
		 UNIX_TIMESTAMP(mm.created_on) AS created_on, mm.language, mm.status
		 FROM mailmotor_mailings AS mm
		 LEFT OUTER JOIN mailmotor_campaigns AS mc ON mc.id = mm.campaign_id
		 WHERE mm.status = ?';

	const QRY_DATAGRID_BROWSE_UNSENT_FOR_CAMPAIGN =
		'SELECT mm.id, mm.name, mc.id AS campaign_id, mc.name AS campaign_name,
		 UNIX_TIMESTAMP(mm.created_on) AS created_on, mm.language, mm.status
		 FROM mailmotor_mailings AS mm
		 INNER JOIN mailmotor_campaigns AS mc ON mc.id = mm.campaign_id
		 WHERE mm.status = ? AND mm.campaign_id = ?';

	/**
	 * Deletes one or more mailings
	 *
	 * @param mixed $ids
	 */
	public static function delete($ids)
	{
		// make sure ids are set
		if(empty($ids)) return;

		// get DB
		$db = BackendModel::getDB(true);

		// if $ids is not an array, make it one
		$ids = (array) $ids;

		// delete records
		$db->delete('mailmotor_mailings', 'id IN (' . implode(',', $ids) . ')');
		$db->delete('mailmotor_mailings_groups', 'mailing_id IN (' . implode(',', $ids) . ')');

		// delete CampaignMonitor references
		$db->delete(
			'mailmotor_campaignmonitor_ids',
			'type = ? AND other_id IN (' . implode(',', $ids) . ')',
			array('campaign')
		);
	}

	/**
	 * Checks if a mailing exists
	 *
	 * @param int $id The id of the mailing to check.
	 * @return bool
	 */
	public static function exists($id)
	{
		return (bool) BackendModel::getDB()->getVar(
			'SELECT COUNT(mm.id)
			 FROM mailmotor_mailings AS mm
			 WHERE mm.id = ?',
			array((int) $id)
		);
	}

	/**
	 * Checks if a mailing exists by name.
	 *
	 * @param string $name The name of the mailing to check.
	 * @return bool
	 */
	public static function existsByName($name)
	{
		return (bool) BackendModel::getDB()->getVar(
			'SELECT COUNT(mm.id)
			 FROM mailmotor_mailings AS mm
			 WHERE mm.name = ?',
			array((string) $name)
		);
	}

	/**
	 * Exports the statistics of a given mailing in CSV format.
	 * This function will send headers to download the CSV and exit your script after use.
	 *
	 * @param int $id
	 */
	public static function exportStatistics($id)
	{
		// fetch the statistics by group
		$records = array();
		$records[] = BackendMailmotorCMHelper::getStatistics($id, true);

		// fetch separate arrays
		$statsClickedLinks = isset($records[0]['clicked_links']) ? $records[0]['clicked_links'] : array();
		$statsClickedLinksBy = isset($records[0]['clicked_links_by']) ? $records[0]['clicked_links_by'] : array();

		// unset multi-dimensional arrays
		unset($records[0]['clicked_links'], $records[0]['clicked_links_by'], $records[0]['opens'],
			$records[0]['clicks'], $records[0]['clicks_percentage'], $records[0]['clicks_total'],
			$records[0]['recipients_total'], $records[0]['recipients_percentage'],
			$records[0]['online_version']);

		// set columns
		$columns = array();
		$columns[] = BL::msg('MailingCSVRecipients');
		$columns[] = BL::msg('MailingCSVUniqueOpens');
		$columns[] = BL::msg('MailingCSVUnsubscribes');
		$columns[] = BL::msg('MailingCSVBounces');
		$columns[] = BL::msg('MailingCSVUnopens');
		$columns[] = BL::msg('MailingCSVBouncesPercentage');
		$columns[] = BL::msg('MailingCSVUniqueOpensPercentage');
		$columns[] = BL::msg('MailingCSVUnopensPercentage');

		// set start of the CSV
		$csv = SpoonFileCSV::arrayToString($records, $columns);

		// check set links
		if(!empty($statsClickedLinks))
		{
			// urldecode the clicked URLs
			$statsClickedLinks = SpoonFilter::arrayMapRecursive('urldecode', $statsClickedLinks);

			// fetch CSV strings
			$csv .= PHP_EOL . SpoonFileCSV::arrayToString($statsClickedLinks);
		}

		// set the filename and path
		$filename = 'statistics-' . SpoonDate::getDate('YmdHi') . '.csv';

		// set headers for download
		$headers = array();
		$headers[] = 'Content-type: application/octet-stream';
		$headers[] = 'Content-Disposition: attachment; filename="' . $filename . '"';

		// overwrite the headers
		SpoonHTTP::setHeaders($headers);

		// output the CSV string
		echo $csv;

		// exit here
		exit;
	}

	/**
	 * Get all data for a given mailing
	 *
	 * @param int $id The id of the mailing.
	 * @return array
	 */
	public static function get($id)
	{
		$record = (array) BackendModel::getDB()->getRecord(
			'SELECT mm.*, UNIX_TIMESTAMP(mm.send_on) AS send_on
			 FROM mailmotor_mailings AS mm
			 WHERE mm.id = ?',
			array((int) $id)
		);

		// stop here if record is empty
		if(empty($record)) return array();

		// get groups for this mailing ID
		$record['groups'] = BackendMailmotorGroupsModel::getAllIDsByMailingID($id);
		$record['recipients'] = BackendMailmotorAddressesModel::getByGroupID($record['groups']);
		$record['data'] = unserialize($record['data']);

		// fetch CM id for this mailing
		$record['cm_id'] = BackendMailmotorCMHelper::getCampaignMonitorID('campaign', $record['id']);

		// return the record
		return $record;
	}

	/**
	 * Get all sent mailing records.
	 *
	 * @param int[optional] $limit The maximum number of items to retrieve.
	 * @return array
	 */
	public static function getAllSent($limit = null)
	{
		// build query & parameters
		$query = self::QRY_DATAGRID_BROWSE_SENT . ' ORDER BY send_on DESC';
		$parameters = array('sent');

		// limit is set
		if(!empty($limit))
		{
			$query .= ' LIMIT ?';
			$parameters[] = $limit;
		}

		return (array) BackendModel::getDB()->getRecords($query, $parameters);
	}

	/**
	 * Get the maximum id for mailings.
	 *
	 * @return int
	 */
	public static function getMaximumId()
	{
		return (int) BackendModel::getDB()->getVar(
			'SELECT MAX(id)
			 FROM mailmotor_mailings
			 LIMIT 1'
		);
	}

	/**
	 * Get a preview URL to the specific mailing
	 *
	 * @param int $id The id of the mailing.
	 * @param string[optional] $contentType The content-type, possible values are: html, plain.
	 * @param bool[optional] $forCM Is the URL intended for Campaign Monitor.
	 * @return string
	 */
	public static function getPreviewURL($id, $contentType = 'html', $forCM = false)
	{
		// check input
		$contentType = SpoonFilter::getValue($contentType, array('html', 'plain'), 'html');
		$forCM = SpoonFilter::getValue($forCM, array(false, true), false, 'int');

		// return the URL
		$detailPageURL = BackendModel::getURLForBlock('mailmotor', 'detail');
		return SITE_URL . $detailPageURL . '/' . $id . '?type=' . $contentType . '&cm=' . $forCM;
	}

	/**
	 * Inserts a new mailing into the database.
	 *
	 * @param array $item The data to insert for the mailing.
	 * @return int
	 */
	public static function insert(array $item)
	{
		return (int) BackendModel::getDB(true)->insert('mailmotor_mailings', $item);
	}

	/**
	 * Updates a mailing in the database.
	 *
	 * @param array $item The data to update for the mailing.
	 * @return int
	 */
	public static function update(array $item)
	{
		return BackendModel::getDB(true)->update('mailmotor_mailings', $item, 'id = ?', array($item['id']));
	}

	/**
	 * Updates the queued mailings with 'sent' status if they were sent
	 *
	 * @return int
	 */
	public static function updateQueuedStatus()
	{
		// get DB
		$db = BackendModel::getDB(true);

		// fetch all mailings that aren't sent
		$records = $db->getRecords(self::QRY_DATAGRID_BROWSE_SENT, array('queued'));

		// no records found, so stop here
		if(empty($records)) return false;

		// reserve update stack
		$updateIds = array();

		// loop the records
		foreach($records as $record)
		{
			// if the sent date is smaller than the current date, update status to 'sent'
			if(date('Y-m-d H:i:s', $record['sent']) < date('Y-m-d H:i:s')) $updateIds[] = $record['id'];
		}

		// if don't need to update any record, stop here
		if(empty($updateIds)) return false;

		// update all mailings that are queued and were sent
		return (int) $db->update('mailmotor_mailings', array('status' => 'sent'), 'id IN (' . implode(',', $updateIds) . ')');
	}
}
