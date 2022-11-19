SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ai_system_user_ruid
-- ----------------------------
DROP TABLE IF EXISTS `ai_system_user_ruid`;
CREATE TABLE `ai_system_user_ruid` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `client_id` int(11) DEFAULT NULL,
  `apps_id` int(11) DEFAULT NULL,
  `login_id` varchar(50) DEFAULT NULL COMMENT '登录ID',
  `secret` varchar(255) DEFAULT NULL COMMENT '秘钥',
  `login_ip` varchar(20) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `UID` (`uid`),
  KEY `APPSID` (`apps_id`),
  KEY `LOGIN` (`uid`,`client_id`,`login_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户认证';

-- ----------------------------
-- Table structure for ai_system_user_relation
-- ----------------------------
DROP TABLE IF EXISTS `ai_system_user_relation`;
CREATE TABLE `ai_system_user_relation` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `parent_id` bigint(20) DEFAULT '0' COMMENT '父ID',
  `layer` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户关系';

-- ----------------------------
-- Table structure for ai_system_user
-- ----------------------------
DROP TABLE IF EXISTS `ai_system_user`;
CREATE TABLE `ai_system_user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `apps_id` int(11) DEFAULT '0',
  `invite_code` varchar(50) DEFAULT NULL,
  `nickname` varchar(50) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `unionid` varchar(50) DEFAULT NULL,
  `face` varchar(255) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL COMMENT '登录密码',
  `safe_password` varchar(100) DEFAULT NULL COMMENT '安全密码',
  `is_lock` tinyint(1) DEFAULT '0',
  `is_delete` tinyint(1) DEFAULT '0',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `CODE` (`invite_code`),
  KEY `MINIAPP_ID` (`apps_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户信息';

-- ----------------------------
-- Table structure for ai_system_tenant_bill
-- ----------------------------
DROP TABLE IF EXISTS `ai_system_tenant_bill`;
CREATE TABLE `ai_system_tenant_bill` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) DEFAULT '0',
  `state` tinyint(1) DEFAULT '0',
  `money` decimal(10,2) DEFAULT '0.00',
  `message` tinytext,
  `order_sn` varchar(255) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `id_2` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='租户-财务记录';

-- ----------------------------
-- Table structure for ai_system_tenant_group
-- ----------------------------
DROP TABLE IF EXISTS `ai_system_tenant_group`;
CREATE TABLE `ai_system_tenant_group`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `apps_id` int(11) NULL DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `rank` int(11) NULL DEFAULT 0,
  `rank_text` varchar(255) DEFAULT NULL,
  `rules` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '[]',
  `menu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `update_time` int(11) NULL DEFAULT NULL,
  `create_time` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET=utf8 COMMENT = '租户角色';

-- ----------------------------
-- Table structure for ai_system_tenant
-- ----------------------------
DROP TABLE IF EXISTS `ai_system_tenant`;
CREATE TABLE `ai_system_tenant` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `agent_id` int(11) DEFAULT '0',
  `parent_id` bigint(20) DEFAULT '0',
  `phone_id` bigint(15) DEFAULT NULL,
  `wechat_id` varchar(250) DEFAULT NULL,
  `parent_apps_id` int(11) DEFAULT 0 COMMENT '子代父管理的APPS_ID',
  `group_id` int(11) DEFAULT 0 COMMENT '角色组',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '帐号余额',
  `lock_money` decimal(10,2) DEFAULT '0.00' COMMENT '锁定金额',
  `password` varchar(255) DEFAULT NULL,
  `safe_password` varchar(255) DEFAULT NULL,
  `is_lock` int(11) DEFAULT '0',
  `lock_config` tinyint(2) DEFAULT '0',
  `login_ip` varchar(20) DEFAULT NULL,
  `login_time` int(11) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  `ticket` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='租户';

-- ----------------------------
-- Table structure for ai_system_apps_release
-- ----------------------------
DROP TABLE IF EXISTS `ai_system_apps_release`;
CREATE TABLE `ai_system_apps_release` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `apps_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `tpl_id` int(11) DEFAULT '0' COMMENT '模板ID',
  `is_commit` tinyint(1) DEFAULT '0' COMMENT '0待上传1已上传2已提审3已发布',
  `state` tinyint(1) DEFAULT '0' COMMENT '0已通过 1审核中',
  `auditid` varchar(50) DEFAULT NULL COMMENT '审核ID(服务商返回）',
  `reason` varchar(255) DEFAULT NULL COMMENT '审核失败原因',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='租户应用-发布状态';

-- ----------------------------
-- Table structure for ai_system_apps_config
-- ----------------------------
DROP TABLE IF EXISTS `ai_system_apps_config`;
CREATE TABLE `ai_system_apps_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `apps_id` int(11) DEFAULT NULL,
  `title` char(30) DEFAULT NULL,
  `config` text,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `APPSID` (`apps_id`,`title`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='租户应用-配置信息';

-- ----------------------------
-- Table structure for ai_system_apps_client
-- ----------------------------
DROP TABLE IF EXISTS `ai_system_apps_client`;
CREATE TABLE `ai_system_apps_client` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` int(11) DEFAULT '0',
  `apps_id` int(11) DEFAULT NULL,
  `title` char(30) DEFAULT NULL,
  `api_id` bigint(20) DEFAULT NULL,
  `api_secret` varchar(200) DEFAULT NULL,
  `appid` char(50) DEFAULT NULL,
  `secret` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `aes_key` varchar(255) DEFAULT NULL,
  `config` text,
  `domain` varchar(255) DEFAULT NULL COMMENT '独立域名',
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `APIID` (`api_id`),
  KEY `DOMAIN` (`domain`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='租户应用-API接入';


-- ----------------------------
-- Table structure for ai_system_apps
-- ----------------------------
DROP TABLE IF EXISTS `ai_system_apps`;
CREATE TABLE `ai_system_apps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) DEFAULT '0' COMMENT '前台管理员ID',
  `app_id` int(11) DEFAULT '0',
  `tenant_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `about` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `qrcode_url` varchar(255) DEFAULT NULL COMMENT '应用二维码',
  `is_default` tinyint(1) DEFAULT '0' COMMENT '默认应用',
  `is_lock` tinyint(1) DEFAULT '0' COMMENT '0正常 1锁定',
  `end_time` bigint(20) DEFAULT NULL,
  `update_time` bigint(20) DEFAULT NULL,
  `create_time` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='租户应用';

-- ----------------------------
-- Table structure for ai_system_app
-- ----------------------------
DROP TABLE IF EXISTS `ai_system_app`;
CREATE TABLE `ai_system_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appname` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `about` varchar(255) DEFAULT NULL,
  `expire_day` int(11) DEFAULT '7',
  `price` decimal(10,2) DEFAULT '0.00',
  `theme` text,
  `qrcode` varchar(255) DEFAULT NULL,
  `is_lock` tinyint(1) DEFAULT '0',
  `sort` int(11) DEFAULT '0',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='应用商店';

-- ----------------------------
-- Table structure for ai_system_agent
-- ----------------------------
DROP TABLE IF EXISTS `ai_system_agent`;
CREATE TABLE `ai_system_agent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `price_gift` decimal(10,2) DEFAULT '0.00',
  `recharge_price` decimal(10,2) DEFAULT '0.00',
  `recharge_price_gift` decimal(10,2) DEFAULT '0.00',
  `discount` int(6) DEFAULT NULL,
  `is_enabled` tinyint(2) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='代理级别';

-- ----------------------------
-- Records of ai_system_agent
-- ----------------------------
INSERT INTO `ai_system_agent` VALUES ('1', '金牌代理', '100.00', '100.00', '100.00', '100.00', '6', '1');

-- ----------------------------
-- Table structure for ai_system_config
-- ----------------------------
DROP TABLE IF EXISTS `ai_system_config`;
CREATE TABLE `ai_system_config`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `config` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `update_time` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='系统配置';

-- ----------------------------
-- Table structure for ai_system_plugin
-- ----------------------------
DROP TABLE IF EXISTS `ai_system_plugin`;
CREATE TABLE `ai_system_plugin`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `about` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `price` decimal(10, 2) NULL DEFAULT 0.00,
  `is_lock` tinyint(1) NULL DEFAULT 0,
  `sort` int(11) NULL DEFAULT 0,
  `create_time` int(11) NULL DEFAULT NULL,
  `update_time` int(11) NULL DEFAULT NULL,
  `app_ids` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='应用商店';

-- ----------------------------
-- Table structure for ai_system_plugins
-- ----------------------------
DROP TABLE IF EXISTS `ai_system_plugins`;
CREATE TABLE `ai_system_plugins`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plugin_id` int(11) NULL DEFAULT 0 COMMENT '开通的插件ID',
  `apps_id` int(11) NULL DEFAULT 0,
  `tenant_id` int(11) NULL DEFAULT NULL,
  `is_lock` tinyint(1) NULL DEFAULT 0 COMMENT '0正常 1锁定',
  `group_ids` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '允许访问的权限组',
  `update_time` bigint(20) NULL DEFAULT NULL,
  `create_time` bigint(20) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='租户应用';

-- ----------------------------
-- Table structure for ai_system_admin
-- ----------------------------
DROP TABLE IF EXISTS `ai_system_admin`;
CREATE TABLE `ai_system_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `locks` tinyint(1) DEFAULT '0',
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `about` tinytext,
  `last_login_time` int(12) unsigned NOT NULL DEFAULT '0',
  `last_login_ip` varchar(15) DEFAULT '0',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='管理员';

-- ----------------------------
-- Records of ai_system_admin
-- ----------------------------
INSERT INTO `ai_system_admin` VALUES ('1', '0', 'admin', '$2y$10$h4NkivfWDCw7BYG61nSu6.K4KNqRWCB4yOY2ud1abaswqo0W7JCK2', '管理员', '1646191304', '127.0.0.1', '1642678956', '1646191304');
SET FOREIGN_KEY_CHECKS=1;