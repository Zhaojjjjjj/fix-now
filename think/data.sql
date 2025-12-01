/*
 Navicat Premium Data Transfer

 Source Server         : localhost_3306
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : localhost:3306
 Source Schema         : mybug

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 07/06/2023 15:50:51
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for issue
-- ----------------------------
DROP TABLE IF EXISTS `issue`;
CREATE TABLE `issue`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` datetime(0) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `user_id` int(10) NULL DEFAULT 0 COMMENT '@user@',
  `cur_user_id` int(10) NULL DEFAULT 0 COMMENT '@user@',
  `project_id` int(10) NULL DEFAULT 0 COMMENT '@project@',
  `module_id` int(10) NULL DEFAULT 0 COMMENT '@module@',
  `sn` int(10) NULL DEFAULT 0 COMMENT '序号',
  `type` tinyint(1) NULL DEFAULT 0 COMMENT '类型',
  `priority` tinyint(1) NULL DEFAULT 0 COMMENT '优先级',
  `bug_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'Bug类型: bug/style/experience/design',
  `environment` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '开发环境: test/dev/prod',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '标题',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '问题内容',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '问题' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of issue
-- ----------------------------

-- ----------------------------
-- Table structure for issue_log
-- ----------------------------
DROP TABLE IF EXISTS `issue_log`;
CREATE TABLE `issue_log`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` datetime(0) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `issue_id` int(10) NULL DEFAULT 0 COMMENT '@issue@',
  `user_id` int(10) NULL DEFAULT 0 COMMENT '@user',
  `next_user_id` int(10) NULL DEFAULT 0 COMMENT '@user',
  `type` tinyint(1) NULL DEFAULT 0 COMMENT '类型',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '内容',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '问题日志' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of issue_log
-- ----------------------------

-- ----------------------------
-- Table structure for module
-- ----------------------------
DROP TABLE IF EXISTS `module`;
CREATE TABLE `module`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` datetime(0) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort` int(10) NULL DEFAULT 0 COMMENT '排序',
  `project_id` int(10) NULL DEFAULT 0 COMMENT '@project@',
  `parent_id` int(10) NULL DEFAULT 0 COMMENT '@module@',
  `name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '名称',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '模块' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of module
-- ----------------------------

-- ----------------------------
-- Table structure for project
-- ----------------------------
DROP TABLE IF EXISTS `project`;
CREATE TABLE `project`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` datetime(0) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `img_cover` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '封面图',
  `description` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '项目描述',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '项目名称',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '项目' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of project
-- ----------------------------
INSERT INTO `project` VALUES (1, '2023-06-05 14:00:32', '2023-06-05 16:34:33', 1, '//qn1.10soo.net/assets/20230328151436.jpg', '', 'Mybug');
INSERT INTO `project` VALUES (2, '2023-06-05 20:46:25', '0000-00-00 00:00:00', 1, '//qn1.10soo.net/assets/20230328151436.jpg', '', 'Test');

-- ----------------------------
-- Table structure for setting
-- ----------------------------
DROP TABLE IF EXISTS `setting`;
CREATE TABLE `setting`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` datetime(0) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `m` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '模块',
  `g` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '组别',
  `k` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '名称',
  `v` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '内容',
  `is_load` tinyint(1) NULL DEFAULT 0 COMMENT '自动加载',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '设置' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of setting
-- ----------------------------

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` datetime(0) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `role_id` int(10) NULL DEFAULT 0 COMMENT '角色',
  `username` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '用户名',
  `pwd` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '密码',
  `wxmp_openid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '公众号ID',
  `mobile` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '手机',
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '昵称',
  `img_avatar` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '头像',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (1, '2023-06-06 15:34:22', '0000-00-00 00:00:00', 1, 1, 'ywl', '123456', '', '', '殷文龙', NULL);
INSERT INTO `user` VALUES (2, '2023-06-06 15:34:28', '0000-00-00 00:00:00', 1, 1, 'M', '123456', '', '', 'M', NULL);
INSERT INTO `user` VALUES (3, '2023-06-06 15:34:36', '0000-00-00 00:00:00', 1, 1, 'jxj', '123456', '', '', '贾晓杰', NULL);
INSERT INTO `user` VALUES (4, '2023-06-06 15:34:41', '0000-00-00 00:00:00', 1, 1, 'zhj', 'ZHJ123', '', '', '赵浩杰', NULL);
INSERT INTO `user` VALUES (5, '2023-06-06 15:35:00', '0000-00-00 00:00:00', 1, 1, 'hzq', '753951', '', '', '韩志强', NULL);
INSERT INTO `user` VALUES (6, '2023-06-06 15:35:08', '2023-06-07 15:40:16', 1, 1, 'hzh', '123456', '', '', '韩志豪', NULL);

-- ----------------------------
-- Table structure for user_project
-- ----------------------------
DROP TABLE IF EXISTS `user_project`;
CREATE TABLE `user_project`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` datetime(0) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `user_id` int(10) NULL DEFAULT 0 COMMENT '@user@',
  `project_id` int(10) NULL DEFAULT 0 COMMENT '@project@',
  `user_project_status` tinyint(1) NULL DEFAULT 0 COMMENT '用户项目状态',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户项目关联表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_project
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
