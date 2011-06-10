<?php

/**
 * BackendManageHelper
 * In this file we store all non-database functions that we will be using in the manage-modules module.
 *
 * @package		backend
 * @subpackage	manage
 *
 * @author 		Dave Lens <dave@netlash.com>
 * @since		2.0
 */
class BackendManageHelper
{
	/**
	 * Get the formfield prefix for the given input type
	 *
	 * @return	string
	 * @param	string $type
	 */
	public static function getHungarianNotationByInputType($type)
	{
		switch($type)
		{
			case 'checkbox':
				return 'chk';
			break;

			case 'dropdown':
				return 'ddm';
			break;

			case 'filefield':
				return 'file';
			break;

			case 'image':
				return 'file';
			break;

			case 'radiobutton':
				return 'rbt';
			break;

			case 'textfield':
				return 'txt';
			break;

			case 'textarea':
				return 'txt';
			break;
		}
	}


	/**
	 * Get the formfield method for the given input type
	 *
	 * @return	string
	 * @param	string $type
	 */
	public static function getMethodByInputType($type)
	{
		switch($type)
		{
			case 'checkbox':
				return 'addCheckbox';
			break;

			case 'dropdown':
				return 'addDropdown';
			break;

			case 'filefield':
				return 'addFilefield';
			break;

			case 'image':
				return 'addImage';
			break;

			case 'radiobutton':
				return 'addRadiobutton';
			break;

			case 'textfield':
				return 'addText';
			break;

			case 'textarea':
				return 'addTextarea';
			break;
		}
	}


	/**
	 * Get the SQL-type for the given input type
	 *
	 * @return	string
	 * @param	string $type
	 */
	public static function getSQLTypeByInputType($type)
	{
		switch($type)
		{
			case 'checkbox':
				return "enum('N','Y')";
			break;

			case 'filefield':
				return 'varchar(255)';
			break;

			case 'image':
				return 'varchar(255)';
			break;

			case 'radiobutton':
				return "enum('N','Y')";
			break;

			case 'textfield':
				return 'varchar(255)';
			break;

			case 'textarea':
				return 'text';
			break;
		}
	}
}

?>