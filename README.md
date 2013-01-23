# PayPalAPI [![Build Status](https://travis-ci.org/nickwhitt/PayPalAPI.png)](https://travis-ci.org/nickwhitt/PayPalAPI)

PayPal Payments Pro API written for PHP 5.3  
<https://github.com/nickwhitt/PayPalAPI>

## Usage

### NVP
	
	$api = new \PayPalAPI\NVP($username, $password, $signature[, $version]);
	if (!$api->DoDirectPayment($fields)) {
		// failure recovery
	}
	// profit

See [PayPal API Reference](https://www.x.com/developers/paypal/documentation-tools/api) for the necessary request fields.

	// example DoDirectPayment fields
	$fields = array(
		'PAYMENTACTION' => 'Authorization',
		'IPADDRESS' => $_SERVER['REMOTE_ADDR'],
		'RETURNFMFDETAILS' => 1,
		'ACCT' => $cc_acct,
		'EXPDATE' => $cc_exp,
		'CVV2' => $cc_cvv2,
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

## History

### Version 0.2

Bundling NVP Usage with logging capability.

### Version 0.1

Proof of concept build.

## Copyright
Copyright (c) 2012, Nicholas Whitt (<nick.whitt@gmail.com>)

## License
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.