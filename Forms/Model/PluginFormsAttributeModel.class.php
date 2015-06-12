<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\Forms\Model;
use Common\Model\CommonModel;
class PluginFormsAttributeModel extends CommonModel{ //Demo插件英文名，改成你的插件英文就行了,插件数据表最好加个plugin前缀再加表名,这个类就是对应“表前缀+plugin_demo”表
	//自动验证
	protected $_validate = array(
			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
			array('name', 'require', '名称不能为空！', 1, 'regex', 3),
			array('name', '/^[a-z][a-z_0-9]{3,30}$/', '名称只能是3~30位小写字母，数字，下划线(_）组合，且小写英文字母开头', 0, 'regex', 3),
			array('title', 'require', '名称不能为空！', 1, 'regex', 3),
	);	
	 /* 自动完成规则 */
    protected $_auto = array(
        array('mtime', NOW_TIME, self::MODEL_BOTH),
    );
	
}