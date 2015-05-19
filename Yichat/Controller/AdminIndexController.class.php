<?php

namespace plugins\Yichat\Controller; //Yichat插件英文名，改成你的插件英文就行了
use Api\Controller\PluginController;//插件控制器基类

class AdminIndexController extends PluginController{
	
	function _initialize(){
		$adminid=sp_get_current_admin_id();//获取后台管理员id，可判断是否登录
		if(!empty($adminid)){
			$this->assign("adminid",$adminid);
		}else{
			//TODO no login
		}
	}
	
	function index(){
		//$plugin_demo_model=D("plugins://Demo/PluginDemo");//实例化自定义模型PluginDemo ,需要创建plugin_demo表
		//$plugin_demo_model->test();//调用自定义模型PluginDemo里的test方法
	}
	//后台自定义图文暂未完善，等待下一版本的出现！
	
}
