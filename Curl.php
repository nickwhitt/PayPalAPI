<?php
/**
 * cURL Wrapper
 *
 * Abstract cURL object for use with API calls.
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/PayPalAPI Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

namespace PayPalAPI;
abstract class Curl {
	protected $url;
	protected $type;
	protected $body;
	protected $data;
	protected $options;
	
	public function __construct($url=NULL) {
		$this->data = $this->options = array();
		
		$this->setOption(CURLOPT_RETURNTRANSFER, 1);
		$this->setUrl($url);
		$this->setType('POST');
	}
	
	/**
	 * Setter Methods
	 *
	 * Each method returns the current object to allow for chaining calls.
	 */
	
	public function setUrl($url=NULL) {
		$this->url = $url;
		return $this;
	}
	
	public function setType($type) {
		$this->type = $type;
		return $this;
	}
	
	public function setData($key, $value) {
		$this->data[$key] = $value;
		return $this;
	}
	
	public function setOption($option, $value) {
		$this->options[$option] = $value;
		return $this;
	}
	
	
	/**
	 * Retrieves the internal data property
	 *
	 * By default, data elements are retrieved as they would be seen by the
	 * API call: as a valid HTTP query string. This can be overwritten with
	 * the $envelope flag to retrieve the data array instead.
	 *
	 * @param bool $envelope
	 * @return mixed
	 */
	public function getData($envelope=TRUE) {
		return $envelope === TRUE ? http_build_query($this->data) : $this->data;
	}
	
	/**
	 * Retrieves the internal options property
	 *
	 * @param void
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}
	
	/**
	 * Retrieves the internal body property
	 *
	 * By default, returns the envelope response given by the API result: an HTTP
	 * query string. This can be overwitten with the $envelope flag to retrieve
	 * the results as an array instead.
	 *
	 * @param bool $envelope
	 * @return mixed
	 */
	public function getBody($envelope=TRUE) {
		if ($envelope === TRUE) {
			return $this->body;
		}
		
		parse_str($this->body, $body);
		return $body;
	}
	
	/**
	 * Abstract method which makes API call
	 *
	 * Meant to return boolean success of API call.
	 *
	 * @param void
	 * @return bool
	 */
	abstract public function post();
	
	/**
	 * Helper method for use within ::post() call
	 *
	 * Ensures cURL options are property set based on cURL type. Meant to be called
	 * by concrete ::post() implementations.
	 *
	 * @param void
	 * @return void
	 */
	protected function prepost() {
		switch ($this->type) {
			case 'POST':
				$this->setOption(CURLOPT_URL, $this->url);
				$this->setOption(CURLOPT_POST, 1);
				$this->setOption(CURLOPT_POSTFIELDS, $this->getData());
				break;
			case 'GET':
			default:
				$this->setOption(CURLOPT_URL, sprintf('%s?%s', $this->url, $this->getData()));
		}
	}
}