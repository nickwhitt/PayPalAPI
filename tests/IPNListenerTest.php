<?php
/**
 * NVP Unit Test
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/PayPalAPI Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

namespace PayPalAPI\Tests;
class IPNListenerTest extends \PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->ipn = new \PayPalAPI\IPNListener();
		$this->ipn->setEndpoint(TRUE, 'PayPalAPI\Tests\CurlRequestStub');
	}
	
	public function testSandboxEndpoint() {
		// test sandbox endpoint
		$this->assertEquals('https://www.sandbox.paypal.com/cgi-bin/webscr', $this->ipn->endpoint);
		
		// ensure we're using cURL stub object
		$this->assertInstanceOf('PayPalAPI\Tests\CurlRequestStub', $this->ipn->curl);
		
		// do not verify cURL SSL host in sandbox
		$options = $this->ipn->curl->getOptions();
		$this->assertTrue(array_key_exists(CURLOPT_SSL_VERIFYHOST, $options));
		$this->assertFalse($options[CURLOPT_SSL_VERIFYHOST]);
	}
	
	public function testProductionEndpoint() {
		// refresh endpoint for production environment
		$this->ipn->setEndpoint();
		$this->assertEquals('https://www.paypal.com/cgi-bin/webscr', $this->ipn->endpoint);
		
		// ensure we're using the real cURL object
		$this->assertInstanceOf('PayPalAPI\CurlRequest', $this->ipn->curl);
		$this->assertNotInstanceOf('PayPalAPI\Tests\CurlRequestStub', $this->ipn->curl);
		
		// we need to verify cURL SSL host
		$options = $this->ipn->curl->getOptions();
		$this->assertTrue(array_key_exists(CURLOPT_SSL_VERIFYHOST, $options));
		$this->assertTrue($options[CURLOPT_SSL_VERIFYHOST]);
	}
	
	public function testVerifyReceipt() {
		$notification = $this->sampleNotification();
		$this->assertFalse($this->ipn->verifyReceipt($notification));
		
		$this->ipn->curl->setBody('VERIFIED');
		$this->assertTrue($this->ipn->verifyReceipt($notification));
		$this->assertEquals($this->ipn->notification, $notification);
		$this->assertEquals($this->ipn->curl->getData(FALSE), array_merge(array('cmd'=>'_notify-validate'), $notification));
	}
	
	protected function sampleNotification() {
		return array(
			'mc_gross' => "19.95",
			'protection_eligibility' => "Eligible",
			'address_status' => "confirmed",
			'payer_id' => "LPLWNMTBWMFAY",
			'tax' => "0.00",
			'address_street' => "1 Main St",
			'payment_date' => "20:12:59 Jan 13, 2009 PST",
			'payment_status' => "Completed",
			'charset' => "windows-1252",
			'address_zip' => "95131",
			'first_name' => "Test",
			'mc_fee' => "0.88",
			'address_country_code' => "US",
			'address_name' => "Test User",
			'notify_version' => "2.6",
			'custom' => "",
			'payer_status' => "verified",
			'address_country' => "United States",
			'address_city' => "San Jose",
			'quantity' => "1",
			'verify_sign' => "AtkOfCXbDm2hu0ZELryHFjY-Vb7PAUvS6nMXgysbElEn9v-1XcmSoGtf",
			'payer_email' => "gpmac_1231902590_per@paypal.com",
			'txn_id' => "61E67681CH3238416",
			'payment_type' => "instant",
			'last_name' => "User",
			'address_state' => "CA",
			'receiver_email' => "gpmac_1231902686_biz@paypal.com",
			'payment_fee' => "0.88",
			'receiver_id' => "S8XGHLYDW9T3S",
			'txn_type' => "express_checkout",
			'item_name' => "",
			'mc_currency' => "USD",
			'item_number' => "",
			'residence_country' => "US",
			'test_ipn' => "1",
			'handling_amount' => "0.00",
			'transaction_subject' => "",
			'payment_gross' => "19.95",
			'shipping' => "0.00"
		);
	}
}