DROP TABLE IF EXISTS `__CL_MAIN__mobile_tokens`;
CREATE TABLE IF NOT EXISTS `__CL_MAIN__mobile_tokens` (
  `token` varchar(30) NOT NULL,
  `userId` int(11) NOT NULL,
  `requestedPath` text NOT NULL,
  `requestTime` datetime NOT NULL,
  `canRetry` bool DEFAULT 0,
  `wasFolder` bool DEFAULT 0,
  PRIMARY KEY (`token`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM;