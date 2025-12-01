-- =============================================
-- 为 issue 表添加新字段
-- 执行日期: 2025-12-01
-- 说明: 添加 bug_type 和 environment 字段
-- =============================================

USE mybug;

-- 添加 bug_type 字段（Bug类型）
ALTER TABLE `issue` 
ADD COLUMN `bug_type` VARCHAR(20) NULL DEFAULT '' COMMENT 'Bug类型: bug/style/experience/design' 
AFTER `priority`;

-- 添加 environment 字段（开发环境）
ALTER TABLE `issue` 
ADD COLUMN `environment` VARCHAR(20) NULL DEFAULT '' COMMENT '开发环境: test/dev/prod' 
AFTER `bug_type`;

-- 验证字段是否添加成功
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    COLUMN_DEFAULT, 
    COLUMN_COMMENT 
FROM 
    INFORMATION_SCHEMA.COLUMNS 
WHERE 
    TABLE_SCHEMA = 'mybug' 
    AND TABLE_NAME = 'issue' 
    AND COLUMN_NAME IN ('bug_type', 'environment');
