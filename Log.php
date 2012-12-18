<?php
/**
 * PayPal Log Encapsulation
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/PayPalAPI Source
 * @link https://github.com/nickwhitt/CRUD Dependency
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

namespace PayPalAPI;
class Log extends CRUD {
	public function __construct($id=NULL) {
		parent::__construct('paypal_log', $id);
	}
	
	/**
	 * Logs the PayPal API request and response envelopes
	 *
	 * Breaks important fields out of envelopes for easy database searching.
	 * Maintains security and PCI Compliance by stripping out specific elements.
	 *
	 * @param str $request
	 * @param str $response
	 * @return bool
	 */
	public static function store($request, $response) {
		// decode envelopes
		parse_str($request, $sent);
		parse_str($response, $received);
		
		// PCI compliance: can't store entire request envelope
		foreach (array('PWD', 'SIGNATURE', 'CVV2', 'ACCT') as $mask) {
			if (isset($sent[$mask])) {
				$sent[$mask] = '*';
			}
		}
		
		// store the responses
		$log = new PayPalLog();
		foreach ($received as $name => $value) {
			try {
				$log->__set(strtolower($name), $value);
			} catch (Exception $e) {
				// ignore exception
			}
		}
		
		// capture method and envelopes
		$log->method = $sent['METHOD'];
		$log->request = http_build_query($sent);
		$log->response = $response;
		
		return $log->save();
	}
}
