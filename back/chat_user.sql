/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50540
Source Host           : localhost:3306
Source Database       : chat

Target Server Type    : MYSQL
Target Server Version : 50540
File Encoding         : 65001

Date: 2016-08-30 14:49:16
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for chat_user
-- ----------------------------
DROP TABLE IF EXISTS `chat_user`;
CREATE TABLE `chat_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uname` varchar(155) DEFAULT NULL,
  `status` varchar(55) DEFAULT NULL,
  `sign` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chat_user
-- ----------------------------
INSERT INTO `chat_user` VALUES ('1', '纸飞机', 'online', '在深邃的编码世界，做一枚轻盈的纸飞机', 'http://cdn.firstlinkapp.com/upload/2016_6/1465575923433_33812.jpg');
INSERT INTO `chat_user` VALUES ('2', '马云', 'online', '让天下没有难写的代码', 'http://tp4.sinaimg.cn/2145291155/180/5601307179/1');
INSERT INTO `chat_user` VALUES ('3', '罗玉凤', 'outline', '在自己实力不济的时候，不要去相信什么媒体和记者。他们不是善良的人，有时候候他们的采访对当事人而言就是陷阱', 'http://tp1.sinaimg.cn/1241679004/180/5743814375/0');
