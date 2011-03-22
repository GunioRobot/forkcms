<?php

/**
 * MultiCurl class
 *
 * This source file can be used to do multi handle Curl calls.
 *
 * Based on the ParallelCurl code of Pete Warden (http://petewarden.typepad.com).
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 */
class MultiCurl
{
	/**
	 * max connections
	 *
	 * @var	int
	 */
    private $maxConnections;


    /**
	 * cURL options
	 *
	 * @var	array
	 */
    private $options;


    /**
	 * unfinished cURL requests
	 *
	 * @var	array
	 */
    private $outstandingRequests = array();


    /**
	 * cURL multi handle instance
	 *
	 * @var	resource
	 */
    private $multiHandle;


    /**
	 * Creates an instance of MultiCurl, setting the maximum connections and options.
	 *
	 * @return	void
	 * @param	int[optional] $maxRequests			The maximum connections.
	 * @param	array[optional] $options			The cURL options.
	 */
    public function __construct($maxConnections = 10, $options = array())
    {
        // set max connections
    	$this->maxConnections = $maxConnections;

    	// set curl options
        $this->options = $options;

        // create the multiple cURL handle
        $this->multiHandle = curl_multi_init();
    }


    /**
	 * Ensure all the requests finish nicely.
	 *
	 * @return	void
	 */
    public function __destruct()
    {
    	// finish outstanding requests
    	$this->finishAllRequests();
    }


    /**
	 * Start a fetch from the $url address, calling the $callback function passing the optional
	 * $userData value. The callback should accept 3 arguments, the url, curl handle and user
	 * data, eg on_request_done($url, $ch, $userData);
	 *
	 * @return	void
	 * @param	string $url							The url to call.
	 * @param	string $callback					The callback function.
	 * @param	array[optional] $userData			The optional user data.
	 * @param	array[optional] $postField			The post field.
	 */
    public function startRequest($url, $callback, $userData = array(), $postFields=null)
    {

		if($this->maxConnections > 0)
	        $this->waitForOutstandingRequestsToDropBelow($this->maxConnections);

        $ch = curl_init();
        curl_setopt_array($ch, $this->options);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if(isset($postFields))
        {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }

        curl_multi_add_handle($this->multiHandle, $ch);

        $this->outstandingRequests[intval($ch)] = array(
            'url' => $url,
            'callback' => $callback,
            'user_data' => $userData,
        );

        $this->checkForCompletedRequests();
    }


    /**
	 * You *MUST* call this function at the end of your script. It waits for any running requests
	 * to complete, and calls their callback functions.
	 *
	 * @return	void
	 */
    public function finishAllRequests()
    {
        $this->waitForOutstandingRequestsToDropBelow(1);
    }


    /**
	 * Checks to see if any of the outstanding requests have finished.
	 *
	 * @return	void
	 */
    private function checkForCompletedRequests()
    {

        // Call select to see if anything is waiting for us
        if (curl_multi_select($this->multiHandle, 0.0) === -1)
            return;

        // Since something's waiting, give curl a chance to process it
        do
        {
            $mrc = curl_multi_exec($this->multiHandle, $active);
        }
        while ($mrc == CURLM_CALL_MULTI_PERFORM);

        // Now grab the information about the completed requests
        while ($info = curl_multi_info_read($this->multiHandle))
        {

            $ch = $info['handle'];

            if (!isset($this->outstandingRequests[$ch]))
            {
                throw new MultiCurlException('handle wasn\'t found in requests: ' . $ch . ' in ' . print_r($this->outstandingRequests, true));
            }

            $request = $this->outstandingRequests[intval($ch)];

            $url = $request['url'];
            $content = curl_multi_getcontent($ch);
            $callback = $request['callback'];
            $userData = $request['user_data'];

            call_user_func($callback, $content, $url, $ch, $userData);

            unset($this->outstandingRequests[intval($ch)]);

            curl_multi_remove_handle($this->multiHandle, $ch);
        }
    }


    /**
	 * Blocks until there's less than the specified number of requests outstanding.
	 *
	 * @return	void
	 * @param	int $max				The maximum current connections.
	 */
    private function waitForOutstandingRequestsToDropBelow($max)
    {
        while (1)
        {
            $this->checkForCompletedRequests();
            if (count($this->outstandingRequests)<$max)
            	break;

            usleep(10000);
        }
    }
}


/**
 * MultiCurlException class
 *
 * @author		Jeroen Maes <jeroenmaes@netlash.com>
 */
class MultiCurlException extends Exception
{
	/**
	 * Class constructor.
	 *
	 * @return	void
	 * @param	string $message				The errormessage.
	 */
	public function __construct($message = null)
	{
		// call parent
		parent::__construct((string) $message);
	}
}

?>