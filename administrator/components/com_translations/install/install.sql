-- ----------------------------
--  Table structure for `tcbo4_translations_translations`
-- ----------------------------
CREATE TABLE `tcbo4_translations_translations` (
  `translations_translation_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `row` bigint(20) NOT NULL,
  `table` varchar(255) NOT NULL,
  `original` varchar(20) NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `modified_on` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `locked_on` datetime NOT NULL,
  `locked_by` int(11) NOT NULL,
  PRIMARY KEY (`translations_translation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `tcbo4_translations_translations_relations`
-- ----------------------------
CREATE TABLE `tcbo4_translations_translations_relations` (
  `translations_translations_relation_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `translations_translation_id` bigint(20) unsigned NOT NULL,
  `lang` varchar(255) NOT NULL,
  `translated` tinyint(1) NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `modified_on` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `locked_on` datetime NOT NULL,
  `locked_by` int(11) NOT NULL,
  PRIMARY KEY (`translations_translations_relation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;