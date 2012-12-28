<?php
/**
 * cURL Request
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/PayPalAPI Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

namespace PayPalAPI;
class CurlRequest extends Curl {
	protected $handle;
	
	public function __construct($url=NULL) {
		$this->handle = curl_init();
		parent::__construct($url);
	}
	
	public function __destruct() {
		curl_close($this->handle);
	}
	
	public function setOption($option, $value) {
		curl_setopt($this->handle, $option, $value);
		return parent::setOption($option, $value);
	}
	
	public function post() {
		parent::prepost();
		$this->body = curl_exec($this->handle);
		return $this;
	}
}