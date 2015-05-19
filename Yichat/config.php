<?php
return array (
	'token' => array (// 在后台插件配置表单中的键名 ,会是config[token]
		'title' => '易信token:', // 表单的label标题
		'type' => 'text',// 表单的类型：text,password,textarea,checkbox,radio,select等
		'value' => 'Yichattoken',// 表单的默认值
		'tip' => '这是接口的token值' //表单的帮助提示
	),
	'url' => array (
		'title' => '易信对接url:',
		'type' => 'text',
		'value' => str_replace('./',$_SERVER['HTTP_HOST'],'http://'.SITE_PATH.U('Iymt/Yichat/index')),
		'tip' => '这是接口的url值'
	),
	'name' => array (
		'title' => '机器人称呼:',
		'type' => 'text',
		'value' => '玉米糖',
		'tip' => '回复机器人的称呼' 
	),
	'appid' => array (
		'title' => 'appid:',
		'type' => 'text',
		'value' => '',
		'tip' => '易信appid' 
	),	
	'appsecrect' => array (
		'title' => 'appsecrect:',
		'type' => 'text',
		'value' => '',
		'tip' => '易信appsecrect' 
	),
	'BaiduAk'=>array(
		'title'=>'百度地图密钥',
		'type'=>'text',
		'value'=>'',
		'tip'=>'百度地图开发者密钥，调用百度地图的时候需要'
	),
);
					