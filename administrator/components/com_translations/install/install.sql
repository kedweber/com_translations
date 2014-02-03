-- ----------------------------
--  Table structure for `#__translations_translations`
-- ----------------------------
CREATE TABLE `#__translations_translations` (
  `translations_translation_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `iso_code` varchar(255) NOT NULL,
  `row` bigint(20) NOT NULL,
  `table` varchar(255) NOT NULL,
  `original` varchar(20) NOT NULL,
  `translated` varchar(255) NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `modified_on` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `locked_on` datetime NOT NULL,
  `locked_by` int(11) NOT NULL,
  PRIMARY KEY (`translations_translation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;