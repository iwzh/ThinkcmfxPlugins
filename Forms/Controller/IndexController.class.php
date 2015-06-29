<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\Forms\Controller; 
use Api\Controller\PluginController;

class IndexController extends PluginController{
	function _initialize(){
		$this->forms_model=D("plugins://Forms/PluginForms");
		$this->Attribute_model=D("plugins://Forms/PluginFormsAttribute");	
		$this->value_model=D("plugins://Forms/PluginFormsValue");
		$this->forms_id=I('forms_id',0,'trim');
		$forms_data=D("plugins://Forms/PluginForms")->find($forms_id);
		$this->assign('form',$forms_data);
	}
	
	//前台显示表单首页
	public function index(){
		//$plugin_demo_model=D("plugins://Demo/PluginDemo");//实例化自定义模型PluginDemo ,需要创建plugin_demo表
		//$plugin_demo_model->test();//调用自定义模型PluginDemo里的test方法
		
		/*$users_model=D("Users");//实例化Common模块下的Users模型
		//$users_model=D("Common/Users");//也可以这样实例化Common模块下的Users模型
		$users=$users_model->limit(0,5)->select();
		
		$this->assign("users",$users);
		
		$this->display(":index");*/
		$this->Attribute_model->where(array('forms_id'=>$forms_id,'is_show'=>1))->select();
		$id=I('get.id','','trim');
		$form=$this->forms_model->where("id=$id")->find();
		$this->assign('attr',$form);
		dump($form);
		$this->display();
	}
	//显示表单属性字段
	public function detail(){
		$id=I('get.id','','trim');
		$form=$this->forms_model->where("id=$id")->find();
		$form_attribute=$this->Attribute_model->where("forms_id=$id")->find();
		$this->assign($form);
		$this->assign('attribute',$form_attribute);
		$this->display();
	}
	//如果表单允许修改,前提条件，必须用户才行
	public function value_edit(){
		
	}
	
	//客户提交ajax
	public function forms_ajax(){
		
	}
	//设置限制条件
	function set_is_can(){
		
	}
	
}
