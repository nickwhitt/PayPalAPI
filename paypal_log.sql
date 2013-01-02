CREATE TABLE `paypal_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `method` varchar(36) NOT NULL,
  `ack` enum('Success','SuccessWithWarning','Failure','FailureWithWarning') NOT NULL DEFAULT 'Failure',
  `correlationid` char(13) NOT NULL,
  `timestamp` datetime NOT NULL COMMENT 'GMT',
  `build` tinytext NOT NULL,
  `version` tinytext NOT NULL,
  `transactionid` char(17) DEFAULT NULL,
  `amt` decimal(10,2) DEFAULT NULL COMMENT 'USD',
  `l_errorcode0` int(11) unsigned DEFAULT NULL,
  `request` text NOT NULL,
  `response` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `correlationid` (`correlationid`),
  KEY `method` (`method`,`ack`),
  KEY `timestamp` (`timestamp`),
  KEY `transactionid` (`transactionid`)
);