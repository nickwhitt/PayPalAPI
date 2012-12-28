<?php
/**
 * PayPal NVP
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/PayPalAPI Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

namespace PayPalAPI;
class NVP {
	protected $user;
	protected $password;
	protected $signature;
	protected $endpoint;
	protected $version;
	protected $response;
	protected $curl;
	
	public function __construct($user, $password, $signature, $version='95.0') {
		$this->user = $user;
		$this->password = $password;
		$this->signature = $signature;
		$this->version = $version;
		
		$this->setEndpoint();
	}
	
	/**
	 * Defines PayPal API Endpoint
	 *
	 * Builds the endpoint for a Merchant NVP call using Certificate authentication.
	 * cURL SSL verification is automatically disabled in sandbox mode.
	 *
	 * @param bool $sandbox Sandbox endpoint?
	 * @param str $curl_class Class name
	 * @return void
	 */
	public function setEndpoint($sandbox=FALSE, $curl_class='\PayPalAPI\CurlRequest') {
		$this->endpoint = sprintf(
			'https://api-3t.%spaypal.com/nvp',
			$sandbox === FALSE ? '' : 'sandbox.'
		);
		
		$this->curl = new $curl_class($this->endpoint);
		$this->curl->setOption(CURLOPT_SSL_VERIFYHOST, !$sandbox);
	}
	
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
	 * Make API call with cURL handler
	 *
	 * Validates API method and builds the data envelope. Returns
	 * success as indicated by API response code: ACK.
	 *
	 * @param str $method API method
	 * @param array $fields
	 * @return bool
	 */
	public function __call($method, $fields) {
		$this->defaultData($method);
		if (!empty($fields) AND is_array($fields[0])) {
			foreach ($fields[0] as $option => $value) {
				$this->curl->setData($option, $value);
			}
		}
		
		if (!$response = $this->curl->post()) {
			return FALSE;
		}
		
		parse_str($response, $this->response);
		return strpos($this->response['ACK'], 'Failure') === FALSE;
	}
	
	/**
	 * 3t security headers
	 *
	 * User, password and signature elements, along with class-specific version
	 * and method fields.
	 *
	 * @param str $method
	 * @return void
	 */
	protected function defaultData($method) {
		$this->validateMethod($method);
		$this->curl
			->setData('USER', $this->user)
			->setData('PWD', $this->password)
			->setData('SIGNATURE', $this->signature)
			->setData('VERSION', $this->version)
			->setData('METHOD', $method);
	}
	
	/**
	 * Ensures only methods we have defined will be sent over cURL
	 *
	 * @todo Fetch the list from PayPal, perhaps using the WSDL?
	 *
	 * @param str $method API method call
	 * @return void
	 */
	protected function validateMethod($method) {
		$valids = array('DoDirectPayment', 'DoCapture');
		
		if (!in_array($method, $valids)) {
			throw new \Exception('Unknown API Call');
		}
	}
}