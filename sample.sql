CREATE TABLE IF NOT EXISTS `Ariel2_log_WKB` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sqltimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `phptime` int(11) NOT NULL,
  `source` text NOT NULL,
  `target` text NOT NULL,
  `destination` text NOT NULL,
  `message` text NOT NULL,
  `event` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

