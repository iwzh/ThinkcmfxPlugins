CREATE TABLE IF NOT EXISTS `sp_plugin_yichat_user` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `uid` int(20) NOT NULL COMMENT '绑定本站uid',
  `subscribe` tinyint(2) NOT NULL COMMENT '用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息',
  `openid` varchar(40) NOT NULL COMMENT '用户的标识，对当前公众号唯一',
  `nickname` varchar(255) NOT NULL COMMENT '用户的昵称',
  `sex` tinyint(2) NOT NULL COMMENT '用户的性别(0:未知，1：男，2：女，3：无效值)',
  `city` varchar(50) NOT NULL COMMENT '城市',  
  `language` varchar(50) NOT NULL COMMENT '用户的语言，简体中文为zh_CN',
  `headimgurl` varchar(255) NOT NULL COMMENT '用户头像，用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。',  
  `subscribe_time` int(10) NOT NULL COMMENT '用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间',
  `latitude` varchar(20) NOT NULL COMMENT '地理位置纬度',
  `longitude` varchar(20) NOT NULL COMMENT '地理位置经度',
  `labelname` varchar(255) NOT NULL COMMENT '微信反馈的地理位置信息',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sp_plugin_yichat_autoreply` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '关键字回复功能名称',
  `rule` varchar(255) NOT NULL COMMENT '正则规则',
  `function` varchar(50) NOT NULL COMMENT '调用方法',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '功能是否启用,0为不启用,1为启用,默认为1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `sp_plugin_yichat_autoreply` VALUES
('1', '使用帮助说明', '/^(帮助|bangzhu|bz|help)$/i', 'replyHelp', '1'),
('2', '天气预报', '/^(.+)天气$/i', 'replyWeather', '1');