<?php
namespace plugins\Chat\Model;//Chat插件英文名，改成你的插件英文就行了
use Common\Model\CommonModel;//继承CommonModel
class PluginChatModel extends CommonModel{ //Chat插件英文名，改成你的插件英文就行了,插件数据表最好加个plugin前缀再加表名,这个类就是对应“表前缀+plugin_Chat”表
	
	
	//自定义方法
	function test(){
		return "Hello 5iymt";
	}
}