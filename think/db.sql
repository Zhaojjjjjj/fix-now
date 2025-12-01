# 用户
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
	`id`         INT(10) NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME NOT NULL DEFAULT NOW(),
	`updated_at` DATETIME NOT NULL,
	`status`     TINYINT(1) NOT NULL DEFAULT 1,

	`role_id`            INT(10)       DEFAULT 0            COMMENT '角色',
	`username`           VARCHAR(30)   DEFAULT ''           COMMENT '用户名',
	`pwd`                VARCHAR(50)   DEFAULT ''           COMMENT '密码',
	`wxmp_openid`        VARCHAR(50)   DEFAULT ''           COMMENT '公众号ID',
	`nickname`           VARCHAR(50)   DEFAULT ''           COMMENT '昵称',
	`img_avatar`         VARCHAR(200)  DEFAULT ''           COMMENT '头像',
	`mobile`             VARCHAR(30)   DEFAULT ''           COMMENT '手机',

	PRIMARY KEY(`id`)
) ENGINE=InnoDb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '用户';


# 设置
DROP TABLE IF EXISTS `setting`;
CREATE TABLE `setting` (
	`id`         INT(10) NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME NOT NULL DEFAULT NOW(),
	`updated_at` DATETIME NOT NULL,
	`status`     TINYINT(1) NOT NULL DEFAULT 1,

	`m`                  VARCHAR(20)   DEFAULT ''           COMMENT '模块',
	`g`                  VARCHAR(20)   DEFAULT ''           COMMENT '组别',
	`k`                  VARCHAR(50)   DEFAULT ''           COMMENT '名称',
	`v`                  VARCHAR(200)  DEFAULT ''           COMMENT '内容',
	`is_load`            TINYINT(1)    DEFAULT 0            COMMENT '自动加载',

	PRIMARY KEY(`id`)
) ENGINE=InnoDb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '设置';


# 项目
DROP TABLE IF EXISTS `project`;
CREATE TABLE `project` (
	`id`         INT(10) NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME NOT NULL DEFAULT NOW(),
	`updated_at` DATETIME NOT NULL,
	`status`     TINYINT(1) NOT NULL DEFAULT 1,

	`img_cover`          VARCHAR(100)  DEFAULT ''           COMMENT '封面图',
	`description`        VARCHAR(1000) DEFAULT ''           COMMENT '项目描述',
	`name`               VARCHAR(100)  DEFAULT ''           COMMENT '项目名称',

	PRIMARY KEY(`id`)
) ENGINE=InnoDb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '项目';


# 模块
DROP TABLE IF EXISTS `module`;
CREATE TABLE `module` (
	`id`         INT(10) NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME NOT NULL DEFAULT NOW(),
	`updated_at` DATETIME NOT NULL,
	`status`     TINYINT(1) NOT NULL DEFAULT 1,

	`sort`               INT(10)       DEFAULT 0            COMMENT '排序',
	`project_id`         INT(10)       DEFAULT 0            COMMENT '@project@',
	`parent_id`          INT(10)       DEFAULT 0            COMMENT '@module@',
	`name`               VARCHAR(20)   DEFAULT ''           COMMENT '名称',

	PRIMARY KEY(`id`)
) ENGINE=InnoDb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '模块';


# 用户项目关联表
DROP TABLE IF EXISTS `user_project`;
CREATE TABLE `user_project` (
	`id`         INT(10) NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME NOT NULL DEFAULT NOW(),
	`updated_at` DATETIME NOT NULL,
	`status`     TINYINT(1) NOT NULL DEFAULT 1,

	`user_id`            INT(10)       DEFAULT 0            COMMENT '@user@',
	`project_id`         INT(10)       DEFAULT 0            COMMENT '@project@',
	`user_project_status` TINYINT(1)    DEFAULT 0            COMMENT '用户项目状态',

	PRIMARY KEY(`id`)
) ENGINE=InnoDb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '用户项目关联表';


# 问题
DROP TABLE IF EXISTS `issue`;
CREATE TABLE `issue` (
	`id`         INT(10) NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME NOT NULL DEFAULT NOW(),
	`updated_at` DATETIME NOT NULL,
	`status`     TINYINT(1) NOT NULL DEFAULT 1,

	`user_id`            INT(10)       DEFAULT 0            COMMENT '@user@',
	`cur_user_id`        INT(10)       DEFAULT 0            COMMENT '@user@',
	`project_id`         INT(10)       DEFAULT 0            COMMENT '@project@',
	`module_id`          INT(10)       DEFAULT 0            COMMENT '@module@',
	`sn`                 INT(10)       DEFAULT 0            COMMENT '序号',
	`type`               TINYINT(1)    DEFAULT 0            COMMENT '类型',
	`priority`           TINYINT(1)    DEFAULT 0            COMMENT '优先级',
	`bug_type`           VARCHAR(20)   DEFAULT ''           COMMENT 'Bug类型: bug/style/experience/design',
	`environment`        VARCHAR(20)   DEFAULT ''           COMMENT '开发环境: test/dev/prod',
	`title`              VARCHAR(255)  DEFAULT ''           COMMENT '标题',
	`content`            TEXT                               COMMENT '问题内容',

	PRIMARY KEY(`id`)
) ENGINE=InnoDb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '问题';


# 问题日志
DROP TABLE IF EXISTS `issue_log`;
CREATE TABLE `issue_log` (
	`id`         INT(10) NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME NOT NULL DEFAULT NOW(),
	`updated_at` DATETIME NOT NULL,
	`status`     TINYINT(1) NOT NULL DEFAULT 1,

	`issue_id`           INT(10)       DEFAULT 0            COMMENT '@issue@',
	`user_id`            INT(10)       DEFAULT 0            COMMENT '@user',
	`next_user_id`       INT(10)       DEFAULT 0            COMMENT '@user',
	`type`               TINYINT(1)    DEFAULT 0            COMMENT '类型',
	`content`            TEXT                               COMMENT '内容',

	PRIMARY KEY(`id`)
) ENGINE=InnoDb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '问题日志';


