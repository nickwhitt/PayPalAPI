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
class NVPTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Creates a fresh sandbox API connection for each unit test
	 */
	public function setUp() {
		$this->api = new \PayPalAPI\NVP(API_USERNAME, API_PASSWORD, API_SIGNATURE, API_VERSION);
		$this->api->setEndpoint(TRUE, 'PayPalAPI\Tests\CurlRequestStub');
	}
	
	public function testConstructor() {
		// validate our unit test API
		$this->assertEquals(API_USERNAME, $this->api->user);
		$this->assertEquals(API_PASSWORD, $this->api->password);
		$this->assertEquals(API_SIGNATURE, $this->api->signature);
		$this->assertEquals(API_VERSION, $this->api->version);
		
		// invalid credentials should not make the object fail
		$api = new \PayPalAPI\NVP('user', 'pass', 'secret');
		$this->assertEquals('user', $api->user);
		$this->assertEquals('pass', $api->password);
		$this->assertEquals('secret', $api->signature);
		$this->assertEquals(API_VERSION, $api->version);
	}
	
	public function testSandboxEndpoint() {
		// test Merchant API for Name-Value Pair with Signature authentication
		$this->assertEquals('https://api-3t.sandbox.paypal.com/nvp', $this->api->endpoint);
		
		// ensure we're using cURL stub object
		$this->assertInstanceOf('PayPalAPI\Tests\CurlRequestStub', $this->api->curl);
		
		// do not verify cURL SSL host in sandbox
		$options = $this->api->curl->getOptions();
		$this->assertTrue(array_key_exists(CURLOPT_SSL_VERIFYHOST, $options));
		$this->assertFalse($options[CURLOPT_SSL_VERIFYHOST]);
	}
	
	public function testProductionEndpoint() {
		// refresh endpoint for production environment
		$this->api->setEndpoint();
		$this->assertEquals('https://api-3t.paypal.com/nvp', $this->api->endpoint);
		
		// ensure we're using the real cURL object
		$this->assertInstanceOf('PayPalAPI\CurlRequest', $this->api->curl);
		$this->assertNotInstanceOf('PayPalAPI\Tests\CurlRequestStub', $this->api->curl);
		
		// we need to verify cURL SSL host
		$options = $this->api->curl->getOptions();
		$this->assertTrue(array_key_exists(CURLOPT_SSL_VERIFYHOST, $options));
		$this->assertTrue($options[CURLOPT_SSL_VERIFYHOST]);
	}
	
	public function testUnknownAPICall() {
		$this->setExpectedException('Exception', 'Unknown API Call');
		$this->api->badCall();
	}
	
	public function testDoDirectPayment() {
		// build required fields
		$fields = array(
			'PAYMENTACTION' => 'Authorization',
			'IPADDRESS' => '192.168.0.1',
			'RETURNFMFDETAILS' => 1,
			'ACCT' => '4024275403188768',
			'EXPDATE' => '122017',
			'CVV2' => '123',
			'FIRSTNAME' => 'Joe',
			'LASTNAME' => 'Public',
			'STREET' => '123 Main St',
			'STREET2' => '',
			'CITY' => 'Columbus',
			'STATE' => 'OH',
			'ZIP' => '43215',
			'COUNTRYCODE' => 'US',
			'AMT' => '50.00'
		);
		
		// prepend 3t info
		$compare = array_merge(
			array(
				'USER' => API_USERNAME,
				'PWD' => API_PASSWORD,
				'SIGNATURE' => API_SIGNATURE,
				'VERSION'   => API_VERSION,
				'METHOD'    => 'DoDirectPayment'
			),
			$fields
		);
		
		$this->api->DoDirectPayment($fields);
		$this->assertEquals($compare, $this->api->curl->getData(FALSE));
		$this->assertEquals(http_build_query($compare), $this->api->curl->getData());
		
		$this->api->curl->setBody('TIMESTAMP=2012%2d12%2d28T14%3a09%3a48Z&CORRELATIONID=ef9f79c2b709&ACK=Success&VERSION=95%2e0&BUILD=4137385&AMT=50%2e00&CURRENCYCODE=USD&AVSCODE=X&CVV2MATCH=M&TRANSACTIONID=1D382620KK449161R');
		$this->assertEquals('TIMESTAMP=2012%2d12%2d28T14%3a09%3a48Z&CORRELATIONID=ef9f79c2b709&ACK=Success&VERSION=95%2e0&BUILD=4137385&AMT=50%2e00&CURRENCYCODE=USD&AVSCODE=X&CVV2MATCH=M&TRANSACTIONID=1D382620KK449161R', $this->api->curl->getBody());
	}
}