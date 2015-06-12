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
		$this->forms_model=D("plugins://Forms/PluginForms");
	}
	
	function index(){
		$list=$this->forms_model->select();
		$this->assign('list',$list);		
		$this->display(":admin_index");
	}
	//新建表单
	public function add(){
		if(IS_POST){
			if ($this->forms_model->create()) {
				$content=htmlspecialchars_decode(I('post.content','','trim'));
				$this->forms_model->content->$content;
				if ($this->forms_model->add()!==false) {
					$this->success("添加成功！");
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
			$password=I('post.password','','trim');
			if(empty($password))
				unset($_POST['password']);
			
			if ($this->forms_model->create($_POST)) {		
				if ($this->forms_model->save()!==false) {
					$this->success("保存成功！");
				} else {					
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->forms_model->getError());
			}
		}else{			
			$id=I("get.id");
			$form=$this->forms_model->where("id=$id")->find();
			$this->assign($form);
			$this->display(":admin_edit");			
		}
	}
	//删除表单
	public function del(){
		$id = intval(I("get.id"));//删除表单会同时删除用户提交的和表单的字段		
		if ($this->forms_model->delete($id)!==false) {
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
