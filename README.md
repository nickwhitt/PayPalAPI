# PayPalAPI
PayPal Payments Pro API written for PHP 5.3  
<https://github.com/nickwhitt/PayPalAPI>

## Usage
	
	$api = new \PayPalAPI\NVP($username, $password, $signature[, $version]);
	if (!$api->DoDirectPayment($fields)) {
		// failure recovery
	}
	// profit

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