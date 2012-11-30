<?php
/**
 * PayPal API Encapsulation
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/PayPalAPI Source
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public License, version 3
 */

class PayPal {
	protected $user;
	protected $password;
	protected $signature;
	protected $endpoint;
	protected $version;
	protected $response;
	
	public function __construct($user=NULL, $password=NULL, $signature=NULL, $version=NULL) {
		$this->user = is_null($user) ? PAYPAL_USER : $user;
		$this->password = is_null($password) ? PAYPAL_PASSWORD : $password;
		$this->signature = is_null($signature) ? PAYPAL_SIGNATURE : $signature;
		$this->version = is_null($version) ? PAYPAL_VERSION : $version;
		$this->endpoint = sprintf(
			'https://api-3t.%spaypal.com/nvp',
			ENVIRONMENT == 'production' ? '' : 'sandbox.'
		);
	}
	
	public function __get($property) {
		return $this->$property;
	}
	
	public function __call($method, $fields) {
		if (empty($fields)) {
			return $this->post($method);
		}
		
		return $this->post($method, $fields[0]);
	}
	
	
	/**
	 * cURL NVP call
	 *
	 * Builds (and logs) the cURL request envelope and processes the response.
	 * Returns success based on acknowledgement code.
	 *
	 * @param str $method
	 * @param array $fields
	 * @return bool
	 */
	protected function post($method, array $fields=array()) {
		// build request envelope
		$request = http_build_query(
			array_merge(
				$this->default_headers($method),
				$fields
			)
		);
		
		// make API call over cURL
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->endpoint);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		if (!$response = curl_exec($ch)) {
			return FALSE;
		}
		
		// store log
		PayPalLog::store($request, $response);
		
		// internalize response and return success
		parse_str($response, $this->response);
		return strpos($this->response['ACK'], 'Failure') === FALSE;
	}
	
	/**
	 * 3-T security headers
	 *
	 * User, password and signature elements, along with class-specific version
	 * and method fields.
	 *
	 * @param str $method
	 * @return array
	 */
	protected function default_headers($method) {
		return array(
			'USER' => $this->user,
			'PWD' => $this->password,
			'SIGNATURE' => $this->signature,
			'VERSION' => $this->version,
			'METHOD' => $method
		);
	}
}
