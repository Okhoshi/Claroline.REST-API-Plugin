CREATE TABLE IF NOT EXISTS `__CL_MAIN__mobile_libs`(
    id INT(11) NOT NULL AUTO_INCREMENT,
    lib_name VARCHAR(200) NOT NULL,
	functions TEXT NOT NULL,
	lib_file TEXT NOT NULL,
    PRIMARY KEY(id),
	UNIQUE (lib_name)
) ENGINE=MyISAM;

INSERT INTO `__CL_MAIN__mobile_libs` (`lib_name`, `functions`, `lib_file`)
VALUES ('General', 'getUserData;getCourseList;getCourseToolList;getUpdates', 'General.lib.php'),
('Announce', 'getAnnounceList;getSingleAnnounce', 'Announce.lib.php'),
('Documents', 'getDocList', 'Documents.lib.php');