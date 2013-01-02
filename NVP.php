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
	protected $version;

	protected $endpoint;
	protected $curl;
	protected $response = NULL;
	protected $log = NULL;
	
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
	 * If enabled, the API call (request and response) will be logged to the
	 * database.
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
		if ($this->isLogEnabled()) $this->logCall();
		
		return strpos($this->response['ACK'], 'Failure') === FALSE;
	}
	
	/**
	 * Tests if the log has been enabled
	 *
	 * @param void
	 * @return bool
	 */
	public function isLogEnabled() {
		return !is_null($this->log);
	}
	
	/**
	 * Enables API call logs
	 *
	 * Ensures API calls are logged to the database automatically.
	 *
	 * Utilizes CRUD\ActiveModel for database access.
	 * @link https://github.com/nickwhitt/CRUD
	 *
	 * @param CRUD\DatabaseLayer $dba
	 * @param str $table
	 * @return void
	 */
	public function enableLog(\CRUD\DatabaseLayer $dba, $table='paypal_log') {
		$this->log = new \CRUD\ActiveModel($dba, $table);
	}
	
	/**
	 * Logs API Call
	 *
	 * @param void
	 * @return bool
	 */
	public function logCall() {
		// sanity check
		if (!$this->isLogEnabled()) {
			throw new \Exception('Logging is not enabled');
		}
		
		$sent = $this->curl->getData(FALSE);
		$received = $this->curl->getBody(FALSE);
		if (empty($received)) {
			return FALSE;
		}
		
		// PCI compliance: can't store entire request envelope
		foreach (array('PWD', 'SIGNATURE', 'CVV2', 'ACCT') as $mask) {
			if (isset($sent[$mask])) {
				$sent[$mask] = '*';
			}
		}
		
		// store the responses
		foreach ($received as $name => $value) {
			try {
				$this->log->__set(strtolower($name), $value);
			} catch (Exception $e) {
				// ignore exception
			}
		}
		
		// capture method and envelopes
		$this->log->method = $sent['METHOD'];
		$this->log->request = http_build_query($sent);
		$this->log->response = $this->curl->getBody();
		
		return $this->log->save();
	}
	
	/**
	 * Disables API call logs
	 *
	 * @param void
	 * @return void
	 */
	public function disableLog() {
		$this->log = NULL;
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