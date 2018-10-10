-- --------------------------------------------------------
-- 主机:                           127.0.0.1
-- 服务器版本:                        5.6.40 - MySQL Community Server (GPL)
-- 服务器操作系统:                      Win64
-- HeidiSQL 版本:                  9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 导出  表 shanzhu.sz_sms_log 结构
CREATE TABLE IF NOT EXISTS `sz_sms_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sms_type` varchar(50) NOT NULL DEFAULT '' COMMENT '短信供应商 如 dysms',
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '短信类型 如 register',
  `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `params` varchar(3000) NOT NULL DEFAULT '' COMMENT '参数(json)',
  `out_id` varchar(255) NOT NULL DEFAULT '' COMMENT '返回的第三方id',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `mobile` (`mobile`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='短信日志表';

-- 正在导出表  shanzhu.sz_sms_log 的数据：~2 rows (大约)
/*!40000 ALTER TABLE `sz_sms_log` DISABLE KEYS */;
INSERT INTO `sz_sms_log` (`id`, `sms_type`, `type`, `mobile`, `params`, `out_id`, `create_time`) VALUES
	(1, 'local', 'register', '13800138000', '["2630"]', 'local_181010185451_3694', 1539168891),
	(2, 'local', 'register', '13800138000', '["2630"]', 'local_181010185550_8329', 1539168950),
	(3, 'local', 'register', '13800138000', '["1503"]', 'local_181010191243_3553', 1539169963),
	(4, 'local', 'register', '13800138000', '["1462"]', 'local_181010191318_5678', 1539169998),
	(5, 'local', 'register', '13800138000', '["9443"]', 'local_181010191355_2024', 1539170035),
	(6, 'local', 'register', '13800138001', '["9282"]', 'local_181010191514_6086', 1539170114);
/*!40000 ALTER TABLE `sz_sms_log` ENABLE KEYS */;


-- 导出  表 shanzhu.sz_user 结构
CREATE TABLE IF NOT EXISTS `sz_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `pwd` char(32) NOT NULL DEFAULT '' COMMENT '密码（MD5）',
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '邀请者ID',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  `create_ip` char(15) NOT NULL DEFAULT '',
  `delete_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile` (`mobile`),
  KEY `parent_id` (`parent_id`),
  KEY `delete_time` (`delete_time`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='用户表';

-- 正在导出表  shanzhu.sz_user 的数据：~0 rows (大约)
/*!40000 ALTER TABLE `sz_user` DISABLE KEYS */;
INSERT INTO `sz_user` (`id`, `mobile`, `pwd`, `parent_id`, `create_time`, `update_time`, `create_ip`, `delete_time`) VALUES
	(1, '13800138000', '25f9e794323b453885f5181f1b624d0b', 0, 1539170004, 1539170004, '127.0.0.1', 0),
	(2, '13800138001', '25f9e794323b453885f5181f1b624d0b', 1, 1539170116, 1539170116, '127.0.0.1', 0);
/*!40000 ALTER TABLE `sz_user` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
