

-- ----------------------------
-- Table structure for sp_plugin_forms
-- ----------------------------
DROP TABLE IF EXISTS `sp_plugin_forms`;
CREATE TABLE `sp_plugin_forms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `finish_tip` text NOT NULL COMMENT '用户提交后提示内容',
  `isuser` tinyint(2) NOT NULL DEFAULT '1' COMMENT '是否用户提交',
  `password` varchar(255) COMMENT '表单密码',
  `intro` text NOT NULL COMMENT '封面简介',
  `cover_img` varchar(255) NOT NULL COMMENT '封面图片',
  `can_edit` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否允许编辑',
  `content` text NOT NULL COMMENT '详细介绍',
  `jump_url` varchar(255) COMMENT '提交后跳转的地址',
  `ctime` int(10) unsigned NOT NULL COMMENT '发布时间，前台一般不显示，管理员才能查看',
  `mtime` int(10) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sp_plugin_forms_attribute
-- ----------------------------
DROP TABLE IF EXISTS `sp_plugin_forms_attribute`;
CREATE TABLE `sp_plugin_forms_attribute` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `type` char(50) NOT NULL DEFAULT 'string' COMMENT '字段类型',
  `title` varchar(255) NOT NULL COMMENT '字段标题',
  `mtime` int(10) NOT NULL COMMENT '修改时间',
  `extra` text NOT NULL COMMENT '参数',
  `value` varchar(255) NOT NULL COMMENT '默认值',
  `name` varchar(100) NOT NULL COMMENT '字段名',
  `remark` varchar(255) NOT NULL COMMENT '字段备注',
  `is_must` tinyint(2) NOT NULL COMMENT '是否必填',
  `validate_rule` varchar(255) NOT NULL COMMENT '正则验证',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序号',
  `error_info` varchar(255) NOT NULL COMMENT '出错提示',
  `forms_id` int(10) unsigned NOT NULL COMMENT '表单ID',
  `is_show` tinyint(2) NOT NULL DEFAULT '1' COMMENT '是否显示',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sp_plugin_forms_value
-- ----------------------------
DROP TABLE IF EXISTS `sp_plugin_forms_value`;
CREATE TABLE `sp_plugin_forms_value` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(10) NOT NULL COMMENT '用户ID',
  `forms_id` int(10) unsigned NOT NULL COMMENT '表单ID',
  `value` text NOT NULL COMMENT '表单值',
  `ctime` int(10) NOT NULL COMMENT '增加时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
