<?php
/**
 * cURL Request Stub
 *
 * For use in API unit tests.
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/PayPalAPI Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

namespace PayPalAPI\Tests;
class CurlRequestStub extends \PayPalAPI\Curl {
	/**
	 * Setter method to allow mocking of API response body
	 *
	 * @param str $body
	 * @return void
	 */
	public function setBody($body) {
		$this->body = $body;
	}
	
	/**
	 * Abstract ::post definition
	 *
	 * Returns the previously set property: body.
	 *
	 * @param void
	 * @return str
	 */
	public function post() {
		parent::prepost();
		return $this->body;
	}
}