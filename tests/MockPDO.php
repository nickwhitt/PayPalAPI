<?php
/**
 * Mock PDO
 *
 * For use in API unit tests.
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/PayPalAPI Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

class MockPDO extends PDO {
	// necessary to prevent phpunit errors
	public function __construct() {}
}
