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
class IPNListener extends Base {
	protected $notification;
	
	public function __construct() {
		$this->setEndpoint();
	}
	
	/**
	 * Defines IPN Endpoint
	 *
	 * Builds the IPN validation endpoint.
	 *
	 * @param bool $sandbox Sandbox Endpoint?
	 * @param str $curl_class Class Name
	 * @return void
	 */
	public function setEndpoint($sandbox=FALSE, $curl_class='\PayPalAPI\CurlRequest') {
		parent::setEndpoint('https://www.%spaypal.com/cgi-bin/webscr', $sandbox, $curl_class);
	}
	
	public function verifyReceipt($contents) {
		$this->notification = $contents;
		
		$this->curl->setData('cmd', '_notify-validate');
		foreach ($this->notification as $key => $value) {
			$this->curl->setData($key, $value);
		}
		
		$this->response = $this->curl->post();
		return $this->response == 'VERIFIED';
	}
}