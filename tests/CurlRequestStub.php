<?php
/**
 * cURL Request Stub
 *
 * @author Nick Whitt
 */

namespace PayPalAPI\Tests;
class CurlRequestStub extends \PayPalAPI\Curl {
	public function setBody($body) {
		$this->body = $body;
	}
	
	public function post() {
		parent::prepost();
		return $this;
	}
}
