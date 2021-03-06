<?php
/**
 * Test Suite Bootstrap
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/PayPalAPI Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

// PayPal sandbox API credentials
define('API_USERNAME', 'username');
define('API_PASSWORD', 'password');
define('API_SIGNATURE', 'signature');
define('API_VERSION', '95.0');

// libraries
require_once dirname(__FILE__) . '/../Curl.php';
require_once dirname(__FILE__) . '/../CurlRequest.php';
require_once dirname(__FILE__) . '/CurlRequestStub.php';

// objects
require_once dirname(__FILE__) . '/../Base.php';
require_once dirname(__FILE__) . '/../NVP.php';
require_once dirname(__FILE__) . '/../IPNListener.php';

// vendor
require_once dirname(__FILE__) . '/../vendor/CRUD/Query.php';
require_once dirname(__FILE__) . '/../vendor/CRUD/DatabaseLayer.php';
require_once dirname(__FILE__) . '/../vendor/CRUD/MysqlLayer.php';
require_once dirname(__FILE__) . '/../vendor/CRUD/ActiveBase.php';
require_once dirname(__FILE__) . '/../vendor/CRUD/ActiveModel.php';