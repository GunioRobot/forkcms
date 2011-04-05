<?php

// include the multicurl class
require_once 'external/multicurl.php';

/**
 * BackendLinkCheckerHelper
 * In this file we store helper functions
 *
 * @package		backend
 * @subpackage	linkchecker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.0
 */
class BackendLinkCheckerHelper
{
	/**
	 * All modules that will be checked
	 *
	 * @var array
	 */
	public static $modules = array('blog', 'content_blocks', 'pages', 'faq');


	/**
	 * All dead links
	 *
	 * @var bool
	 */
	private static $allDeadLinks = array();


	/**
	 * Check the links
	 *
	 * @param	array $urls				The links to check.
	 */
	public static function checkLinks($urls)
	{
		// get module setting
		$doMultiCall = (bool) BackendModel::getModuleSetting('link_checker', 'multi_call');

		// single call
		if(!$doMultiCall)
		{
			// loop the urls
			foreach ($urls as $url)
			{
				// initialize
				$ch = curl_init();

				// set the url
				curl_setopt($ch, CURLOPT_URL, $url['url']);

				// set the curl options
				curl_setopt($ch, CURLOPT_HEADER, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_NOBODY, 1);
				curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Spoon ' . SPOON_VERSION);

				// execute and fetch the resulting HTML output
				curl_exec($ch);

				// get the info on the curl handle
				$chinfo = curl_getinfo($ch);

				// free up the curl handle
				curl_close($ch);

				// insert only non working links
				if($chinfo['http_code'] == 404 || $chinfo['http_code'] == 0)
				{
					// build array
					$value = array();
				    $value = $url;
				    $value['error_code'] = $chinfo['http_code'];
				    $value['date_checked'] = SpoonDate::getDate('Y-m-d H:i:s');

				    // add to all dead links array
				    self::$allDeadLinks[] = $value;
				}
			}

			// insert into db
			BackendLinkCheckerModel::insertLinks(self::$allDeadLinks);
		}

		// multi call
		else
		{
			// max connections
			$maxRequests = (int) BackendModel::getModuleSetting('link_checker', 'num_connections');

			// set the curl options
			$curlOptions = array(
			    CURLOPT_TIMEOUT => 10,
			    CURLOPT_USERAGENT => 'Spoon ' . SPOON_VERSION,
			    CURLOPT_FOLLOWLOCATION => 1,
			    CURLOPT_MAXREDIRS => 5,
			    CURLOPT_HEADER => 1,
			    CURLOPT_NOBODY => 1
			);

			// new instance
			$multiCurl = new MultiCurl($maxRequests, $curlOptions);

			// loop the urls
			foreach ($urls as $url)
			{
				// add request
				$multiCurl->startRequest($url['url'], array('BackendLinkCheckerHelper', 'onMultiCurlRequestDone'), $url);
			}

			// finish all open requests
			$multiCurl->finishAllRequests();

			// insert into db
			BackendLinkCheckerModel::insertLinks(self::$allDeadLinks);
		}
	}


	/**
	 * This function gets called back for each request that completes
	 *
	 * @param	string $content				The HTML output.
	 * @param   string $url					The checked url.
	 * @param	object $ch					The cURL instance.
	 * @param	array $userData				The passed through userData array.
	 */
	public static function onMultiCurlRequestDone($content, $url, $ch, $userData)
	{
	    // get the httpcode
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	    // insert only non working links
	    if($httpcode == 404 || $httpcode == 0)
		{
			// build array
			$value = array();
		    $value = $userData;
		    $value['error_code'] = $httpcode;
		    $value['date_checked'] = SpoonDate::getDate('Y-m-d H:i:s');

		    // add to all dead links array
		    self::$allDeadLinks[] = $value;
		}
	}


	/**
	 * Get all links on a website
	 *
	 * @return array
	 * @param	string [optional]$returnMode			The way we want the array to be build. 'singleArray' or 'multiArray'
	 */
	public static function getAllLinks($returnMode = 'singleArray')
	{
		// all the links
		$allLinks = array();

		// all the modules we want to check
		$modules = BackendLinkCheckerHelper::$modules;

		// loop the modules
		foreach($modules as $module)
		{
			// fetch all entries from a module
			$entries = BackendLinkCheckerModel::getModuleEntries($module);

			// seach every entry for links, if the module is not empty
			if(isset($entries))
			{
				// we check everye entry in this module for links
				foreach ($entries as $entry)
				{
					// get all links in this entry
					if (preg_match_all("!href=\"(.*?)\"!", $entry['text'], $matches))
					{
						// all urls we find in this entry
						$urlList = array();

						// @todo	comment what happens inside the loop, and what you're looping (like an example of the format in case of $matches)
						foreach ($matches[1] as $url)
						{
							// add the url to the list
							$urlList[] = $url;
						}

						// remove duplicates
						$urlList = array_values(array_unique($urlList));

						// store every link inside this entry in the database
						foreach($urlList as $url)
						{
							// return array with only the links
							if($returnMode == 'singleArray')
							{
								// check if a link is external or internal
								// fork saves an internal link 'invalid'
								$external = (spoonfilter::isURL($url)) ? 'Y' : 'N';
								$saveUrl = ($external === 'Y') ? $url : SITE_URL . $url;

								// add to allLinks array
								$allLinks[] = $saveUrl;
							}

							// return multi array with all information about the link
							else if($returnMode == 'multiArray')
							{
								// build the array to insert
								$values = array();
								$values['item_title'] = $entry['title'];
								$values['module'] = $module;
								$values['language'] = $entry['language'];
								$values['item_id'] = $entry['id'];

								// check if a link is external or internal
								// fork saves an internal link 'invalid'
								$values['external'] = (spoonfilter::isURL($url)) ? 'Y' : 'N';
								$values['url'] = ($values['external'] === 'Y') ? $url : SITE_URL . $url;

								// add to allLinks array
								$allLinks[] = $values;
							}
						}
					}
				}
			}
		}

		// return the links
		return $allLinks;
	}


	/**
	 * Check a givin text if it contains a dead link
	 *
	 * @return	bool
	 */
	public static function containsDeadLink($text)
	{
		// decode char encoding
		$text = SpoonFilter::htmlspecialcharsDecode($text);

		// check if text contains urls
		if(preg_match_all("!href=\"(.*?)\"!", $text, $matches))
		{
			// all urls we find in this text
			$urlList = array();

			// retrieve the dead urls we know
			$deadUrlList = BackendLinkCheckerModel::getDeadUrls();

			// loop the matches
			foreach ($matches[1] as $url)
			{
				// rewrite internal urls, to become compatible with the way internal links are stored in the database
				$externalUrl = (spoonfilter::isURL($url)) ? 'Y' : 'N';
				$url = ($externalUrl === 'Y') ? $url : SITE_URL . $url;

				// if a url from the text is found in the array with dead urls, we know enough...
				if(in_array($url, $deadUrlList))
				{
					// text has dead urls
					return true;
				}
			}

			// text has no dead urls
			return false;

		}
		// text has no urls
		else
		{
			return false;
		}
	}


	/**
	 * Cleanup the dead links, check if the earlier found dead links are still used on the site,
	 * otherwise we can delete them and asume the user had them corrected.
	 *
	 * @return	array
	 */
	public static function cleanup()
	{
		// get all links on the website
		$allLinks = BackendLinkCheckerHelper::getAllLinks();

		// get all dead links
		$deadLinks = BackendLinkCheckerModel::getDeadUrls();

		// check if all dead links are still on the website
		foreach ($deadLinks as $url)
		{
			// the dead link is not found ont the site
			if(!in_array($url, $allLinks))
			{
				// remove it
				BackendLinkCheckerModel::deleteLink($url);
			}
		}
	}


	/**
	 * Column function to convert the http error code into a human readable message.
	 *
	 * @return	string
	 * @param $errorCode		The http error code.
	 */
	public static function getDescription($errorCode)
	{
		// return the label for the error code
		return BL::msg('ErrorCode' . $errorCode);
	}


	/**
	 * Column function to convert the module name into a label.
	 *
	 * @return	string
	 * @param $module			The module name.
	 */
	public static function getModuleLabel($module)
	{
		// return the label for the module
		return ucfirst(BL::lbl(str_replace(' ', '', ucwords(str_replace('_', ' ', $module)))));
	}


	/**
	 * Column function to get the time ago since the link was checked.
	 *
	 * @return	string
	 * @param $date				The date the link was checked.
	 */
	public static function getTimeAgo($date)
	{
		// return time ago
		return SpoonDate::getTimeAgo(strtotime($date), BL::getWorkingLanguage());
	}
}

?>