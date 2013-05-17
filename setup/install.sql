DROP TABLE IF EXISTS `__CL_MAIN__mobile_tokens`;
CREATE TABLE IF NOT EXISTS `__CL_MAIN__mobile_tokens` (
  `token` varchar(30) NOT NULL,
  `userId` int(11) NOT NULL,
  `cid` varchar(40) NOT NULL,
  `gid` int(11),
  `requestedPath` text NOT NULL,
  `requestTime` datetime NOT NULL,
  PRIMARY KEY (`token`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM;