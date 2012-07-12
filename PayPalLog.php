<?php
/**
 * PayPal Log Encapsulation
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/PayPalAPI Source
 * @link https://github.com/nickwhitt/CRUD Dependency
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public License, version 3
 */

class PayPalLog extends CRUD {
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
		unset($sent['PWD']);
		unset($sent['SIGNATURE']);
		if (isset($sent['CVV2'])) {
			unset($sent['CVV2']);
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
