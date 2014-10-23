/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50612
Source Host           : localhost:3306
Source Database       : yunmayi

Target Server Type    : MYSQL
Target Server Version : 50612
File Encoding         : 65001

Date: 2014-10-23 09:46:54
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ymy_tuihuo
-- ----------------------------
DROP TABLE IF EXISTS `ymy_tuihuo`;
CREATE TABLE `ymy_tuihuo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Orderid` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `UserName` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `reason` text CHARACTER SET utf8 NOT NULL,
  `account_type` tinyint(4) DEFAULT NULL,
  `account` varchar(255) DEFAULT NULL,
  `checked` tinyint(4) NOT NULL DEFAULT '0',
  `proid` int(11) NOT NULL DEFAULT '0',
  `proname` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(20,2) NOT NULL DEFAULT '0.00',
  `procount` int(11) NOT NULL DEFAULT '0',
  `proimages` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute` int(11) NOT NULL DEFAULT '0',
  `attprice` decimal(20,2) NOT NULL DEFAULT '0.00',
  `attributes` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `cprice` decimal(20,2) NOT NULL DEFAULT '0.00',
  `pronumber` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `state` int(11) NOT NULL DEFAULT '0',
  `oksenddate` int(11) NOT NULL DEFAULT '0',
  `okreceivedate` int(11) NOT NULL DEFAULT '0',
  `okexitdate` int(11) NOT NULL DEFAULT '0',
  `postdate` int(11) NOT NULL DEFAULT '0',
  `okpaydate` int(11) NOT NULL DEFAULT '0',
  `prize` int(11) NOT NULL DEFAULT '0',
  `tuanid` int(11) DEFAULT '0' COMMENT '团购id  如果购买时商品未处于团购状态则为0',
  `tuanprice` decimal(10,2) DEFAULT '0.00' COMMENT '商品处于团购时的销售价格  如果不是团购商品则为0',
  `tuandiscount` tinyint(3) DEFAULT '0' COMMENT '商品处于团购时的折扣  如果不是团购商品则为0',
  `activity` varchar(30) CHARACTER SET utf8 DEFAULT NULL COMMENT '活动备注',
  `memo` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1000018398 DEFAULT CHARSET=latin1;
