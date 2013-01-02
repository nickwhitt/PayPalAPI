<?php
/**
 * PayPal Base
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/PayPalAPI Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

namespace PayPalAPI;
abstract class Base {
	protected $endpoint;
	protected $curl;
	protected $response = NULL;
	protected $log = NULL;
	
	/**
	 * Retrieves object property
	 *
	 * @param str $property
	 * @return mixed
	 */
	public function __get($property) {
		return $this->$property;
	}
	
	/**
	 * Defines API Endpoint
	 *
	 * Builds the API endpoint given a base URL. cURL SSL verification will be automatically
	 * disabled in sandbox mode.
	 *
	 * @param str $endpoint
	 * @param bool $sandbox Sandbox Endpoint?
	 * @param str $curl_class Class Name
	 * @return void
	 */
	public function setEndpoint($endpoint, $sandbox=FALSE, $curl_class='\PayPalAPI\CurlRequest') {
		$this->endpoint = sprintf($endpoint, $sandbox === FALSE ? '' : 'sandbox.');
		
		$this->curl = new $curl_class($this->endpoint);
		$this->curl->setOption(CURLOPT_SSL_VERIFYHOST, !$sandbox);
	}
}