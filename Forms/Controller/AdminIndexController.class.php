<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2015 http://www.5iymt.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 5iymt <1145769693@qq.com>
// +----------------------------------------------------------------------
// | Version: 2015-5-23 12:15:11
// +----------------------------------------------------------------------

namespace plugins\Forms\Controller; 
use Api\Controller\PluginController;

class AdminIndexController extends PluginController{
	
	function _initialize(){
		$adminid=sp_get_current_admin_id();//获取后台管理员id，可判断是否登录
		if(!empty($adminid)){
			$this->assign("adminid",$adminid);
		}else{
			//TODO no login
		}
		$this->assign('forms_url',sp_plugin_url('Forms://AdminIndex/index'));
		$this->model=D("plugins://Forms/PluginForms");
	}
	
	function index(){
		//$plugin_demo_model=D("plugins://Demo/PluginDemo");//实例化自定义模型PluginDemo ,需要创建plugin_demo表
		//$plugin_demo_model->test();//调用自定义模型PluginDemo里的test方法
		
		/*$users_model=D("Users");//实例化Common模块下的Users模型
		//$users_model=D("Common/Users");//也可以这样实例化Common模块下的Users模型
		$users=$users_model->limit(0,5)->select();
		
		
		
		$this->assign("users",$users);
		
		$this->display(":admin_index");*/
		$list=$this->model->select();
		$this->assign('list',$list);		
		$this->display(":admin_index");
	}
	//新建表单
	public function add(){
		if(IS_POST){
			if ($this->model->create()) {
				if ($this->model->add()!==false) {
					$this->success("添加成功！", sp_plugin_url('Forms://AdminIndex/index'));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->model->getError());
			}
		
		}else{
			$this->display(":admin_add");	
		}	
		
	}
	//修改表单
	public function edit(){
		if (IS_POST) {
			dump($_POST);exit;
			$password=I('post.password','','trim');
			
			if ($this->model->create()) {
				if ($this->model->save()!==false) {
					$this->success("保存成功！");
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->model->getError());
			}
		}else{
			$id=I("get.id");
			$form=$this->model->where("id=$id")->find();
			$this->assign($form);
			$this->display(":admin_edit");			
		}
	}
	//删除表单
	public function del(){
		$id = intval(I("get.id"));//删除表单会同时删除用户提交的和表单的字段		
		if ($this->model->delete($id)!==false) {
			D("plugins://Forms/PluginFormsAttribute")->where(array("forms_id=$id"))->delete();//删除字段
			D("plugins://Forms/PluginFormsValue")->where(array("forms_id=$id"))->delete();//删除提交信息
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}			
	}
	//预览
	public function show(){
		$id = intval(I("get.id"));
		empty($id)&&$this->error('不存在的表，无法预览！');
		$url=sp_plugin_url('Forms://Index/add',array('id',$id),true);
		redirect($url);
	}
}
