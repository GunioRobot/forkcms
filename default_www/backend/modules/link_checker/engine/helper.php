<?php

// include the multicurl class
require_once 'external/multicurl.php';

/**
 * BackendLinkCheckerHelper
 * In this file we store helper functions
 *
 * @package		backend
 * @subpackage	link_checker
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 * @since		2.1
 */
class BackendLinkCheckerHelper
{
	/**
	 * All dead links
	 *
	 * @var bool
	 */
	private static $allDeadLinks = array();


	/**
	 * cUrl options array
	 *
	 * @var array
	 */
	private static $curlOptions = array(
		CURLOPT_TIMEOUT => 10,
		CURLOPT_USERAGENT => 'Fork CMS LinkChecker',
		CURLOPT_FOLLOWLOCATION => 1,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_MAXREDIRS => 5,
		CURLOPT_HEADER => 1,
		CURLOPT_NOBODY => 1
	);


	/**
	 * All modules that will be checked
	 *
	 * @var array
	 */
	public static $modules = array('blog', 'content_blocks', 'pages', 'faq');


	/**
	 * Check the links
	 *
	 * @return	void
	 * @param	array $urls				The links to check.
	 */
	public static function checkLinks($urls)
	{
		// single call
		// curl multi threadig on windows doesn't work well...
		if(PHP_OS == "WIN32" || PHP_OS == "WINNT")
		{
			// loop the urls
			foreach($urls as $url)
			{
				// is the url a valid cache entry?
				if(self::isValidCache($url['url']))
				{
					// get the information we have in cache
					$cacheLink = BackendLinkCheckerModel::getCacheLink($url['url']);

					// insert only non working links
					if($cacheLink['error_code'] == 404 || $cacheLink['error_code'] == 0)
					{
						// build array
						$value = array();
					    $value = $url;
					    $value['error_code'] = $cacheLink['error_code'];
					    $value['date_checked'] = $cacheLink['date_checked'];

					    // add to all dead links array
					    self::$allDeadLinks[] = $value;
					}
				}

				// url not found in cache or too old
				else
				{
					// initialize
					$ch = curl_init();

					// set the url
					curl_setopt($ch, CURLOPT_URL, $url['url']);

					// set the curl options
					curl_setopt_array($ch, self::$curlOptions);

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

					// insert cache
					BackendLinkCheckerModel::insertCache($url['url'], $chinfo['http_code'], SpoonDate::getDate('Y-m-d H:i:s'));
				}
			}

			// insert into db
			BackendLinkCheckerModel::insertLinks(self::$allDeadLinks);
		}

		// multi call
		else
		{
			// max connections
			$maxRequests = 10;

			// new instance
			$multiCurl = new MultiCurl($maxRequests, self::$curlOptions);

			// loop the urls
			foreach($urls as $url)
			{
				// is the url a valid cache entry?
				if(self::isValidCache($url['url']))
				{
					// get the information we have in cache
					$cacheLink = BackendLinkCheckerModel::getCacheLink($url['url']);

					// insert only non working links
					if($cacheLink['error_code'] == 404 || $cacheLink['error_code'] == 0)
					{
						// build array
						$value = array();
					    $value = $url;
					    $value['error_code'] = $cacheLink['error_code'];
					    $value['date_checked'] = $cacheLink['date_checked'];

					    // add to all dead links array
					    self::$allDeadLinks[] = $value;
					}
				}

				// url not found in cache or too old
				else
				{
					// add request
					$multiCurl->startRequest($url['url'], array('BackendLinkCheckerHelper', 'onMultiCurlRequestDone'), $url);
				}
			}

			// finish all open requests
			$multiCurl->finishAllRequests();

			// insert into db
			BackendLinkCheckerModel::insertLinks(self::$allDeadLinks);
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
		$allLinks = self::getAllLinks();

		// get all dead links
		$deadLinks = BackendLinkCheckerModel::getDeadUrls();

		// check if all dead links are still on the website
		foreach($deadLinks as $url)
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
	 * Check a givin text if it contains a dead link
	 *
	 * @return	bool
	 * @param	string $text		The string to be checked.
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

			// loop $matches[1] as this contains the URLs that matched our regular expression
			foreach($matches[1] as $url)
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
	 * Get all links on a website
	 *
	 * @return	array
	 * @param	string[optional] $returnMode			The way we want the array to be build. 'singleArray' or 'multiArray'.
	 */
	public static function getAllLinks($returnMode = 'singleArray')
	{
		// all the links
		$allLinks = array();

		// all the modules we want to check
		$modules = self::$modules;

		// loop the modules
		foreach($modules as $module)
		{
			// fetch all entries from a module
			$entries = BackendLinkCheckerModel::getModuleEntries($module);

			// seach every entry for links, if the module is not empty
			if(isset($entries))
			{
				// we check everye entry in this module for links
				foreach($entries as $entry)
				{
					// get all links in this entry
					if(preg_match_all("!href=\"(.*?)\"!", $entry['text'], $matches))
					{
						// all urls we find in this entry
						$urlList = array();

						// loop $matches[1] as this contains the URLs that matched our regular expression
						foreach($matches[1] as $url)
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
							if($returnMode == 'multiArray')
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

		// at this point it is possible that we have duplicate entries in our array
		// as some pages contain multiple text area's, where the same dead link might be used multiple times
		$allLinks = self::removeDuplicates($allLinks);

		// return the links
		return $allLinks;
	}


	/**
	 * Column function to convert the http error code into a human readable message.
	 *
	 * @return	string
	 * @param	string $errorCode		The http error code.
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
	 * @param	string $module			The module name.
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
	 * @param	string $date				The date the link was checked.
	 */
	public static function getTimeAgo($date)
	{
		// return time ago
		return SpoonDate::getTimeAgo(strtotime($date), BL::getWorkingLanguage());
	}


	/**
	 * Is the link a valid cache url?
	 *
	 * @return	bool
	 * @param	string $url		The url to check.
	 */
	public static function isValidCache($url)
	{
		// max cache time
		$maxTime = 1800;

		// retrieve most recent saved cache url
		$return = BackendLinkCheckerModel::getCacheLink($url);

		// check if most recent is still valid
		if(count($return) > 0)
		{
			if((time() - strtotime($return['date_checked'])) < $maxTime) return true;
		}

		// else
		return false;
	}


	/**
	 * This function gets called back for each request that completes
	 *
	 * @return	void
	 * @param	string $content		The HTML output.
	 * @param	string $url			The checked url.
	 * @param	object $ch			The cURL instance.
	 * @param	array $userData		The passed through userData array.
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

	    // insert cache
		BackendLinkCheckerModel::insertCache($userData['url'], $httpcode, SpoonDate::getDate('Y-m-d H:i:s'));
	}


	/**
	 * Recursive array unique function to remove the duplicate entries from an (multi-dimensional) array.
	 * http://www.php.net/manual/en/function.array-unique.php#97285
	 *
	 * @return	$array
	 * @param	array $array				The (multi-dimensional) array.
	 */
	public static function removeDuplicates($array)
	{
		$result = array_map('unserialize', array_unique(array_map('serialize', $array)));

	  	foreach($result as $key => $value)
	  	{
	    	if(is_array($value))
	    	{
	      		$result[$key] = self::removeDuplicates($value);
	    	}
	  	}

	  	return $result;
	}
}

?>