CREATE TABLE IF NOT EXISTS `slideshows` (
  `id` int(11) NOT NULL auto_increment,
  `language` varchar(10) character set latin1 NOT NULL default 'nl',
  `extra_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `module` varchar(255) character set latin1 default NULL,
  `data_callback_method` text character set latin1,
  `name` varchar(255) character set latin1 NOT NULL,
  `width` int(11) default NULL,
  `height` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `slideshows_images` (
  `id` int(11) NOT NULL auto_increment,
  `slideshow_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `filename` varchar(255) default NULL,
  `caption` text,
  `sequence` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `slideshows_types` (
  `id` int(11) NOT NULL auto_increment,
  `type` varchar(255) character set latin1 NOT NULL,
  `settings` text character set latin1,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT AUTO_INCREMENT=1 ;

INSERT INTO `slideshows_types` (`type`, `settings`) VALUES
('basic', NULL);
