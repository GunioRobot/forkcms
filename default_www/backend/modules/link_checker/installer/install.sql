CREATE TABLE IF NOT EXISTS `link_checker_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `error_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `external` enum('Y','N') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `language` varchar(5) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `date_checked` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

CREATE TABLE IF NOT EXISTS `link_checker_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `error_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `date_checked` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;