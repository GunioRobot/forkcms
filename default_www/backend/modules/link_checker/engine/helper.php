<?php

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
	 * Check a link using CURL
	 *
	 * @return	string
	 * @param	string $url						The link to check.
	 */
	public static function checkLink($urls, $doMultiCall)
	{
		// single call
		if(!$doMultiCall)
		{
			// start timer (debug only)
			$timeStart = microtime(true);

			foreach ($urls as $url)
			{
				// initialize
				$ch = curl_init();

				// set the url
				curl_setopt($ch, CURLOPT_URL, $url['url']);

				// set the options
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

				// echo and insert (debug only)
				echo  $url['url'] . ' => ' . $chinfo['http_code'] . PHP_EOL;

				// insert only non working link
				if($chinfo['http_code'] != 200)
				{
					// build array
					$value = array();
				    $value = $url;
				    $value['code'] = $chinfo['http_code'];

				    // insert into db
					BackendLinkCheckerModel::insertLinks($value);
				}
			}

			// end timer (debug only)
			$timeEnd = microtime(true);
			$time = $timeEnd - $timeStart;
			echo 'I did it in ' . round($time, 2) . ' seconds.';

		}

		// multi call
		else
		{
			// max connections
			$maxRequests = 5;

			// set the options
			$curlOptions = array(
			    CURLOPT_TIMEOUT => 10,
			    CURLOPT_USERAGENT => 'Spoon ' . SPOON_VERSION,
			    CURLOPT_RETURNTRANSFER =>  1,
			    CURLOPT_FOLLOWLOCATION => 1,
			    CURLOPT_MAXREDIRS => 5,
			    CURLOPT_HEADER => 1,
			    CURLOPT_NOBODY => 1
			);

			// start timer (debug only)
			$timeStart = microtime(true);

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

			// end timer (debug only)
			$timeEnd = microtime(true);
			$time = $timeEnd - $timeStart;
			echo 'I did it in ' . round($time, 2) . ' seconds.';
		}
	}


	// This function gets called back for each request that completes
	public static function onMultiCurlRequestDone($content, $url, $ch, $userData)
	{
	    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	    // echo the url and httpcode (debug only)
	    echo $url . ' => ' . $httpcode . PHP_EOL;

		// insert only if non working link
	    if($httpcode != 200)
		{
			// build array
			$value = array();
		    $value = $userData;
		    $value['code'] = $httpcode;

		    // insert into db
			BackendLinkCheckerModel::insertLinks($value);
		}
	}


	/**
	 * Get module edit url
	 *
	 * @return	array
	 */
	public static function getModuleEditUrl($module)
	{
		// contains the editUrl
		$editUrl = '';

		// every module has an unique url
		switch ($module)
		{
		    case 'blog':
    			$editUrl = '/private/' . BL::getInterfaceLanguage() . '/blog/edit?token=true&id=';
		    break;

		    case 'content_blocks':
		        $editUrl = '/private/' . BL::getInterfaceLanguage() . '/content_blocks/edit?token=true&id=';
		    break;

		    case 'pages':
		        $editUrl = '/private/' . BL::getInterfaceLanguage() . '/pages/edit?id=';
		    break;

		    case 'faq':
		        $editUrl = '/private/' . BL::getInterfaceLanguage() . '/faq/edit?id=';
		    break;
		}

		// return the editUrl
		return $editUrl;
	}


	/**
	 * Get module public url
	 *
	 * @return	array
	 */
	public static function getModulePublicUrl($module)
	{
		// contains the publicUrl
		$publicUrl = '';

		// every module has an unique url
		switch ($module)
		{
		    case 'blog':
		        $publicBaseUrl = '/blog/detail/';
		    break;

		    case 'content_blocks':
		        $publicBaseUrl = '/';
		    break;

		    case 'pages':
		        $publicBaseUrl = '/';
		    break;

		    case 'faq':
		        $publicBaseUrl = '/faq/';
		    break;
		}

		// return the publicUrl
		return $publicUrl;
	}
}

?>