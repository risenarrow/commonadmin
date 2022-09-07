# Host: localhost  (Version: 5.7.26)
# Date: 2022-05-31 18:54:09
# Generator: MySQL-Front 5.3  (Build 4.234)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "sy_test_table"
#

DROP TABLE IF EXISTS `sy_test_table`;
CREATE TABLE `sy_test_table` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='测试';

#
# Data for table "sy_test_table"
#

/*!40000 ALTER TABLE `sy_test_table` DISABLE KEYS */;
INSERT INTO `sy_test_table` VALUES (1,'asda'),(2,'dhhjj');
/*!40000 ALTER TABLE `sy_test_table` ENABLE KEYS */;

#
# Structure for table "sy_test_user"
#

DROP TABLE IF EXISTS `sy_test_user`;
CREATE TABLE `sy_test_user` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='测试用户';

#
# Data for table "sy_test_user"
#

/*!40000 ALTER TABLE `sy_test_user` DISABLE KEYS */;
INSERT INTO `sy_test_user` VALUES (1),(2),(3);
/*!40000 ALTER TABLE `sy_test_user` ENABLE KEYS */;
