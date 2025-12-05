SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for issue
-- ----------------------------
DROP TABLE IF EXISTS `issue`;
CREATE TABLE `issue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status` int(11) DEFAULT '1' COMMENT '1:未解决, 2:待审核, 8:已关闭',
  `user_id` int(11) DEFAULT NULL COMMENT '创建人ID',
  `cur_user_id` int(11) DEFAULT NULL COMMENT '当前处理人ID',
  `project_id` int(11) DEFAULT NULL,
  `module_id` int(11) DEFAULT NULL,
  `sn` int(11) DEFAULT NULL COMMENT '序号',
  `type` tinyint(4) DEFAULT '1' COMMENT '1:缺陷, 2:任务, 3:需求',
  `priority` tinyint(4) DEFAULT '1' COMMENT '1:普通, 2:紧急',
  `bug_type` varchar(255) DEFAULT NULL,
  `environment` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for issue_log
-- ----------------------------
DROP TABLE IF EXISTS `issue_log`;
CREATE TABLE `issue_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status` int(11) DEFAULT '1',
  `issue_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `next_user_id` int(11) DEFAULT NULL,
  `type` tinyint(4) DEFAULT NULL COMMENT '1:发起, 2:修复, 3:驳回, 4:不通过, 5:评论, 6:指派, 8:审核通过, 9:修改',
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for module
-- ----------------------------
DROP TABLE IF EXISTS `module`;
CREATE TABLE `module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status` int(11) DEFAULT '1',
  `sort` int(11) DEFAULT '0',
  `project_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for project
-- ----------------------------
DROP TABLE IF EXISTS `project`;
CREATE TABLE `project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status` int(11) DEFAULT '1' COMMENT '1:进行中, 2:已归档',
  `img_cover` varchar(255) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for setting
-- ----------------------------
DROP TABLE IF EXISTS `setting`;
CREATE TABLE `setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status` int(11) DEFAULT '1',
  `m` varchar(50) DEFAULT NULL COMMENT '模块',
  `g` varchar(50) DEFAULT NULL COMMENT '分组',
  `k` varchar(100) DEFAULT NULL COMMENT '键',
  `v` text COMMENT '值',
  `is_load` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status` int(11) DEFAULT '1',
  `role_id` int(11) DEFAULT '1' COMMENT '1:管理员/开发, 2:普通用户',
  `username` varchar(50) DEFAULT NULL,
  `pwd` varchar(255) DEFAULT NULL,
  `wxmp_openid` varchar(100) DEFAULT NULL,
  `nickname` varchar(50) DEFAULT NULL,
  `img_avatar` varchar(255) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for user_project
-- ----------------------------
DROP TABLE IF EXISTS `user_project`;
CREATE TABLE `user_project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status` int(11) DEFAULT '1',
  `user_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `user_project_status` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (1, NOW(), NOW(), 1, 1, 'admin', '123456', NULL, '管理员', '', '');

SET FOREIGN_KEY_CHECKS = 1;
