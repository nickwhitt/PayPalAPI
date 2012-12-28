<?php
/**
 * cURL Request Wrapper
 *
 * For use within API objects.
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/PayPalAPI Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

namespace PayPalAPI;
class CurlRequest extends Curl {
	protected $handle;
	
	/**
	 * Initialize the wraper with an internal cURL handle
	 *
	 * @param str $url
	 * @return void
	 */
	public function __construct($url=NULL) {
		$this->handle = curl_init();
		parent::__construct($url);
	}
	
	/**
	 * Close the internal cURL handle in garbage collection
	 *
	 * @param void
	 * @return void
	 */
	public function __destruct() {
		curl_close($this->handle);
	}
	
	/**
	 * Adds cURL options to wrapper's internal cURL handle
	 *
	 * Typically $option will be one of the predefined cURL constants: CURLOPT_*.
	 * Returns the object itself to allow for chaining setter methods.
	 *
	 * @param int $option
	 * @return self
	 */
	public function setOption($option, $value) {
		curl_setopt($this->handle, $option, $value);
		return parent::setOption($option, $value);
	}
	
	/**
	 * Executes the API call using internal cURL handle
	 *
	 * Results are stored internally and returned.
	 *
	 * @param void
	 * @return str
	 */
	public function post() {
		parent::prepost();
		$this->body = curl_exec($this->handle);
		return $this->body;
	}
}