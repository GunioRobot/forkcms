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
	 * @param	int[optional] $maxConnections			The maximum connections.
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
	 * Checks to see if any of the outstanding requests have finished.
	 *
	 * @return	void
	 */
    private function checkForCompletedRequests()
    {

        // call select to see if anything is waiting for us
        if(curl_multi_select($this->multiHandle, 0.0) === -1) return;

        // since something's waiting, give curl a chance to process it
        do
        {
            $mrc = curl_multi_exec($this->multiHandle, $active);
        }
        while($mrc == CURLM_CALL_MULTI_PERFORM);

        // now grab the information about the completed requests
        while($info = curl_multi_info_read($this->multiHandle))
        {
			// get the handle instance
            $ch = $info['handle'];

            // check if we can find this handle in the oustanding requests
            if(!isset($this->outstandingRequests[intval($ch)]))
            {
                // throw exception
            	throw new MultiCurlException('Handle wasn\'t found in outstanding requests.');
            }

            // get the request
            $request = $this->outstandingRequests[intval($ch)];
            $url = $request['url'];
            $callback = $request['callback'];
            $userData = $request['user_data'];

            // fetch the content
            $content = curl_multi_getcontent($ch);

            // call the callback funtion
            call_user_func($callback, $content, $url, $ch, $userData);

            // the handle is finished, remove it from the array
            unset($this->outstandingRequests[intval($ch)]);

            // remove the handle
            curl_multi_remove_handle($this->multiHandle, $ch);
        }
    }


	/**
	 * You *MUST* call this function at the end of your script. It waits for any running requests
	 * to complete, and calls their callback functions.
	 *
	 * @return	void
	 */
    public function finishAllRequests()
    {
        // waint until there is less then 1 open connection (= all requests done)
    	$this->waitForOutstandingRequestsToDropBelow(1);
    }


	/**
	 * Start a fetch from the $url address, calling the $callback function passing the optional
	 * $userData value. The callback should accept 3 arguments, the url, curl handle and user
	 * data, eg onMultiCurlRequestDone($url, $ch, $userData);
	 *
	 * @return	void
	 * @param	string $url							The url to call.
	 * @param	string $callback					The callback function.
	 * @param	array[optional] $userData			The optional user data.
	 * @param	array[optional] $postFields			The post fields.
	 */
    public function startRequest($url, $callback, $userData = array(), $postFields = null)
    {
		// check if there is an open connection
		if($this->maxConnections > 0) $this->waitForOutstandingRequestsToDropBelow($this->maxConnections);

        // initialize
	    $ch = curl_init();

	    // set the options
	    curl_setopt_array($ch, $this->options);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

	    // set the url
	    curl_setopt($ch, CURLOPT_URL, $url);

		// set post fields if needed
        if(isset($postFields))
        {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }

        // add handle
        curl_multi_add_handle($this->multiHandle, $ch);

        // add the request to the outstanding requests array, with its process number as index
        $this->outstandingRequests[intval($ch)] = array(
            'url' => $url,
            'callback' => $callback,
            'user_data' => $userData,
        );

        // check for completed requests
        $this->checkForCompletedRequests();
    }


    /**
	 * Blocks until there's less than the specified number of requests outstanding.
	 *
	 * @return	void
	 * @param	int $max				The maximum current connections.
	 */
    private function waitForOutstandingRequestsToDropBelow($max)
    {
    	// loop
    	while(1)
    	{
            // check for completed requests
        	$this->checkForCompletedRequests();

        	// only break if the number of oustanding requests had dropped under the maximum number of connections
            if(count($this->outstandingRequests)<$max) break;

            // pause the code
            usleep(1000);
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
	 * @param	string[optional] $message				The errormessage.
	 */
	public function __construct($message = null)
	{
		// call parent
		parent::__construct((string) $message);
	}
}

?>