delimiter $$

CREATE TABLE `stories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `creationDatetime` datetime DEFAULT NULL,
  `body` text NOT NULL,
  `topicId` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1$$

CREATE TABLE `topics` (
  `id` tinyint(3) NOT NULL AUTO_INCREMENT,
  `topicText` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1$$

