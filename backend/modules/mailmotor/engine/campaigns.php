<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This class holds all methods related to campaigns.
 *
 * @author Dave Lens <dave.lens@netlash.com>
 */
class BackendMailmotorCampaignsModel
{
	const QRY_DATAGRID_BROWSE =
		'SELECT c.*, UNIX_TIMESTAMP(c.created_on) AS created_on
		 FROM mailmotor_campaigns AS c';

	/**
	 * Deletes one or more campaigns
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
		$db->delete('mailmotor_campaigns', 'id IN (' . implode(',', $ids) . ')');

		// update all mailings for the ids
		$db->update('mailmotor_mailings', array('campaign_id' => 0), 'campaign_id IN (' . implode(',', $ids) . ')');
	}

	/**
	 * Checks if a campaign exists by ID.
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function exists($id)
	{
		return (bool) BackendModel::getDB()->getVar(
			'SELECT COUNT(mc.id)
			 FROM mailmotor_campaigns AS mc
			 WHERE mc.id = ?',
			array((int) $id)
		);
	}

	/**
	 * Checks if a campaign exists
	 *
	 * @param string $name The name of the campaign to check for existance.
	 * @return bool
	 */
	public static function existsByName($name)
	{
		return (bool) BackendModel::getDB()->getVar(
			'SELECT COUNT(mc.id)
			 FROM mailmotor_campaigns AS mc
			 WHERE mc.name = ?',
			array((string) $name)
		);
	}

	/**
	 * Exports the statistics of all mailings for a given campaign ID in CSV format.
	 * This function will send headers to download the CSV and exit your script after use.
	 *
	 * @param int $id
	 */
	public static function exportStatistics($id)
	{
		// set the filename and path
		$filename = 'statistics-' . SpoonDate::getDate('YmdHi') . '.csv';

		// fetch the statistics by campaign ID
		$records = array();
		$records[] = BackendMailmotorCMHelper::getStatisticsByCampaignID($id);

		// unset some records
		unset($records[0]['opens'], $records[0]['clicks'], $records[0]['clicks_percentage'],
				$records[0]['recipients_total'], $records[0]['recipients_percentage']);

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

		// fetch all mailings in this campaign
		$query = BackendMailmotorMailingsModel::QRY_DATAGRID_BROWSE_SENT_FOR_CAMPAIGN;
		$mailings = BackendModel::getDB()->getRecords($query, array('sent', $id));

		// mailings set
		if(!empty($mailings))
		{
			// set mailings columns
			$mailingColumns = array();
			$mailingColumns['name'] = BL::lbl('Name');
			$mailingColumns['language'] = BL::lbl('Language');

			$csvString = SpoonFileCSV::arrayToString(
				$mailings,
				$mailingColumns,
				array('id', 'campaign_id', 'campaign_name', 'send_on', 'status')
			);

			// add the records to the csv string
			$csv .= PHP_EOL . 'Mailings:' . PHP_EOL . $csvString;
		}

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
	 * Get all data for a given id
	 *
	 * @param int $id The id of the campaign to fetch.
	 * @return array
	 */
	public static function get($id)
	{
		return (array) BackendModel::getDB()->getRecord(
			'SELECT *
			 FROM mailmotor_campaigns
			 WHERE id = ?',
			array((int) $id)
		);
	}

	/**
	 * Get all campaigns in key/value format for id/name
	 *
	 * @return int
	 */
	public static function getAllAsPairs()
	{
		$record = BackendModel::getDB()->getPairs(
			'SELECT mc.id AS value, mc.name AS label
			 FROM mailmotor_campaigns AS mc'
		);

		// prepend an additional option
		array_unshift($record, ucfirst(BL::lbl('NoCampaign')));

		// return the record
		return $record;
	}

	/**
	 * Get a campaign ID by name.
	 *
	 * @param string $name
	 * @return int
	 */
	public static function getID($name)
	{
		return (int) BackendModel::getDB()->getVar(
			'SELECT mc.id
			 FROM mailmotor_campaigns AS mc
			 WHERE mc.name = ?',
			array((string) $name)
		);
	}

	/**
	 * Checks if a given campaign has sent mailings under it
	 *
	 * @param int $id The id of the campaign to check.
	 * @return bool
	 */
	public static function hasSentMailings($id)
	{
		return (bool) BackendModel::getDB()->getVar(
			'SELECT COUNT(mm.id)
			 FROM mailmotor_mailings AS mm
			 WHERE mm.campaign_id = ? AND mm.status = ?',
			array((int) $id, 'sent')
		);
	}

	/**
	 * Inserts a new campaign into the database
	 *
	 * @param array $item The data to insert for the campaign.
	 * @return int
	 */
	public static function insert(array $item)
	{
		return (int) BackendModel::getDB(true)->insert('mailmotor_campaigns', $item);
	}

	/**
	 * Updates a campaign record.
	 *
	 * @param array $item The data to update for the campaign.
	 * @return int
	 */
	public static function update(array $item)
	{
		return BackendModel::getDB(true)->update('mailmotor_campaigns', $item, 'id = ?', array($item['id']));
	}
}
