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
	
	public function testBody() {
		$body = $this->sampleResponse();
		$envelope = http_build_query($body);
		
		$this->api->curl->setBody($envelope);
		$this->assertEquals($envelope, $this->api->curl->getBody());
		$this->assertEquals($body, $this->api->curl->getBody(FALSE));
	}
	
	public function testDoDirectPayment() {
		// build sample data envelope
		$fields = $this->sampleRequest();
		
		// prepend 3t info
		$compare = array_merge($this->sampleHeaders('DoDirectPayment'), $fields);
		
		// first response should fail as there is no stub response body
		$this->assertFalse($this->api->DoDirectPayment($fields), 'API call did not fail');
		
		// validate data envelope
		$this->assertEquals($compare, $this->api->curl->getData(FALSE));
		$this->assertEquals(http_build_query($compare), $this->api->curl->getData());
		
		// simulate a response body and re-attempt API "call"
		$this->api->curl->setBody(http_build_query($this->sampleResponse()));
		$this->assertTrue($this->api->DoDirectPayment($fields), 'API call failed');
		
		// validate data envelope was maintained with both calls
		$this->assertEquals($compare, $this->api->curl->getData(FALSE));
		$this->assertEquals(http_build_query($compare), $this->api->curl->getData());
	}
	
	public function testLogException() {
		$this->setExpectedException('Exception', 'Logging is not enabled');
		$this->api->logCall();
	}
	
	public function testLogCall() {
		// enable log
		$this->assertFalse($this->api->isLogEnabled(), 'Log should not be enabled by default');
		$this->api->enableLog($this->mockDba());
		$this->assertTrue($this->api->isLogEnabled(), 'Log should have been enabled');
		$this->assertFalse($this->api->logCall(), 'There is no call to log');
		
		// make API call
		$this->assertFalse($this->api->log->exists(), 'Log should not exist');
		$this->api->curl->setBody(http_build_query($this->sampleResponse()));
		$this->assertTrue($this->api->DoDirectPayment($this->sampleRequest()), 'API call failed');
		$this->assertTrue($this->api->log->exists(), 'Log should exist');
		
		// verify logged data matches cURL
		$data = $this->api->curl->getData(FALSE);
		$data['PWD'] = $data['SIGNATURE'] = $data['CVV2'] = $data['ACCT'] = '*';
		$this->assertEquals(http_build_query($data), $this->api->log->request);
		$this->assertEquals($this->api->curl->getBody(), $this->api->log->response);
		
		// disable log
		$this->api->disableLog();
		$this->assertFalse($this->api->isLogEnabled(), 'Log should have been disabled');
	}
	
	/**
	 * Builds an array of cURL headers defined by the PayPal API
	 *
	 * @param str $method PayPal API Call
	 * @return array
	 */
	protected function sampleHeaders($method) {
		return array(
			'USER' => API_USERNAME,
			'PWD' => API_PASSWORD,
			'SIGNATURE' => API_SIGNATURE,
			'VERSION'   => API_VERSION,
			'METHOD'    => $method
		);
	}
	
	/**
	 * Builds an array of cURL data elements
	 *
	 * Elements correspond to the necessary fields for the DoDirectPayment API call.
	 *
	 * @param void
	 * @return array
	 */
	protected function sampleRequest() {
		return array(
			'PAYMENTACTION' => 'Authorization',
			'IPADDRESS' => '192.168.0.1',
			'RETURNFMFDETAILS' => 1,
			'ACCT' => '4024275403188768',
			'EXPDATE' => date('mY', strtotime('+2 years')),
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
	}
	
	/**
	 * Builds an array which emulates a DoDirectPayment API response
	 *
	 * @param void
	 * @return array
	 */
	protected function sampleResponse() {
		return array(
			'TIMESTAMP' => date('Y-m-d\TH:i:s\Z'),
			'CORRELATIONID' => substr(md5(rand()), 0, 12),
			'ACK' => "Success",
			'VERSION' => API_VERSION,
			'BUILD' => substr(rand(), 0, 7),
			'AMT' => "50.00",
			'CURRENCYCODE' => "USD",
			'AVSCODE' => "X",
			'CVV2MATCH' => "M",
			'TRANSACTIONID' => substr(md5(rand()), 0, 17)
		);
	}
	
	/**
	 * Creates a mock DBA for database testing
	 *
	 * @param void
	 * @return Mock CRUD\MysqlLayer
	 */
	protected function mockDba() {
		$fields = array(
			array('id', 'PRI'),
			array('method', 'MUL'),
			array('ack', '', 'Failure'),
			array('correlationid', 'UNI'),
			array('timestamp', 'MUL'),
			array('build'),
			array('version'),
			array('transactionid', 'MUL'),
			array('amt'),
			array('l_errorcode0'),
			array('request'),
			array('response')
		);
		
		$table = array();
		foreach ($fields as $field) {
			$row = new \stdClass();
			$row->Field = $field[0];
			$row->Key = isset($field[1]) ? $field[1] : '';
			$row->Default = isset($field[2]) ? $field[2] : NULL;
			
			$table[] = $row;
		}
		
		$dba = $this->getMockBuilder('\CRUD\MysqlLayer')->disableOriginalConstructor()->getMock();
		$dba->expects($this->any())->method('describeTable')->will($this->returnValue($table));
		$dba->expects($this->any())->method('insert')->will($this->returnValue('123'));
		
		return $dba;
	}
}