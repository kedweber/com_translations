-- ----------------------------
--  Table structure for `#__translations_translations`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `#__translations_translations` (
  `translations_translation_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `row` bigint(20) NOT NULL,
  `table` varchar(255) CHARACTER SET latin1 NOT NULL,
  `iso_code` varchar(255) CHARACTER SET latin1 NOT NULL,
  `lang` varchar(255) NOT NULL,
  `original` tinyint(1) NOT NULL DEFAULT '0',
  `translated` tinyint(1) NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `modified_on` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `locked_on` datetime NOT NULL,
  `locked_by` int(11) NOT NULL,
  PRIMARY KEY (`translations_translation_id`),
  UNIQUE KEY `unique` (`row`,`table`,`iso_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;