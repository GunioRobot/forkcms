<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This class holds all methods related to templates.
 *
 * @author Dave Lens <dave.lens@netlash.com>
 */
class BackendMailmotorTemplatesModel
{
	/**
	 * Get the template record.
	 *
	 * @param string $language The language.
	 * @param string $name The name of the template.
	 * @return array
	 */
	public static function get($language, $name)
	{
		// set the path to the template folders for this language
		$path = BACKEND_MODULE_PATH . '/templates/' . $language;

		// load all templates in the 'templates' folder for this language
		$templates = SpoonDirectory::getList($path, false, array('.svn'));

		// stop here if no directories were found
		if(empty($templates) || !in_array($name, $templates)) return array();

		// load all templates in the 'templates' folder for this language
		if(!SpoonFile::exists($path . '/' . $name . '/template.tpl'))
		{
			// @todo add to locale
			$exception = 'The template fold "%s" exists, but no template.tpl file was found.';
			throw new SpoonException(sprintf($exception, $name));
		}
		if(!SpoonFile::exists($path . '/' . $name . '/css/screen.css'))
		{
			// @todo add to locale
			$exception = 'The template folder "%s" exists, but no screen.css file was found.';
			throw new SpoonException(sprintf($exception, $name));
		}

		// set template data
		$record = array();
		$record['name'] = $name;
		$record['language'] = $language;
		$record['label'] = BL::lbl('Template' . SpoonFilter::toCamelCase($record, array('-', '_')));
		$record['path_content'] = $path . '/' . $name . '/template.tpl';
		$record['path_css'] = $path . '/' . $name . '/css/screen.css';
		$record['url_css'] = SITE_URL . '/backend/modules/mailmotor/templates/' . $language . '/' . $name . '/css/screen.css';

		// check if the template file actually exists
		if(SpoonFile::exists($record['path_content']))
		{
			$record['content'] = SpoonFile::getContent($record['path_content']);
		}

		if(SpoonFile::exists($record['path_css']))
		{
			$record['css'] = SpoonFile::getContent($record['path_css']);
		}

		// return the record
		return $record;
	}

	/**
	 * Get all data for templates in a format acceptable for SpoonForm::addRadioButton()
	 *
	 * @param string $language
	 * @return array
	 */
	public static function getAllForCheckboxes($language)
	{
		// load all templates in the 'templates' folder for this language
		$records = SpoonDirectory::getList(
			BACKEND_MODULE_PATH . '/templates/' . $language . '/', false,
			array('.svn')
		);

		// stop here if no directories were found
		if(empty($records)) return array();

		// loop and complete the records
		foreach($records as $key => $record)
		{
			// add additional values
			$records[$record]['language'] = $language;
			$records[$record]['value'] = $record;
			$records[$record]['label'] = BL::lbl('Template' . SpoonFilter::toCamelCase($record, array('-', '_')));

			// unset the key
			unset($records[$key]);
		}

		// return the records
		return (array) $records;
	}
}
