<?php
/**
 * 
 *
 * @author Nick Whitt
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
	
	public function getData($envelope=TRUE) {
		return $envelope === TRUE ? http_build_query($this->data) : $this->data;
	}
	
	public function getOptions() {
		return $this->options;
	}
	
	public function getBody() {
		return $this->body;
	}
	
	abstract public function post();
	
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
