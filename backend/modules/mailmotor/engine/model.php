<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This class holds all general methods related to the mailmotor.
 *
 * @author Dave Lens <dave.lens@netlash.com>
 */
class BackendMailmotorModel
{
	/**
	 * Checks the settings and optionally returns an array with warnings
	 *
	 * @return	array
	 */
	public static function checkSettings()
	{
		$warnings = array();
		$settingsURL = BackendModel::createURLForAction('settings', 'mailmotor');

		if(BackendModel::getModuleSetting('mailmotor', 'cm_account') == false)
		{
			$errorLabel = BL::err('AnalysisNoCMAccount', 'mailmotor');
			$warnings[] = array('message' => sprintf($errorLabel, $settingsURL));
		}
		elseif(BackendModel::getModuleSetting('mailmotor', 'cm_client_id') == '')
		{
			$errorLabel = BL::err('AnalysisNoCMClientID', 'mailmotor');
			$warnings[] = array('message' => sprintf($errorLabel, $settingsURL));
		}

		return $warnings;
	}
}

?>