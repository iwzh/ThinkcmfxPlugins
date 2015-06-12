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

class FormsAttributeController extends PluginController{
	
	function _initialize(){
		$adminid=sp_get_current_admin_id();//获取后台管理员id，可判断是否登录
		if(!empty($adminid)){
			$this->assign("adminid",$adminid);
		}else{
			//TODO no login
		}
		$forms_id=I('forms_id',0,'trim');
		$forms_data=D("plugins://Forms/PluginForms")->field('id,title')->find($forms_id);
		$this->assign('forms_data',$forms_data);
		$this->assign('forms_url',sp_plugin_url('Forms://FormsAttribute/index',array('forms_id'=>$forms_id)));
		$this->model=D("plugins://Forms/PluginFormsAttribute");
	}
	//表单属性
	function index(){
		$forms_id = I("get.forms_id",0,'intval');
		$list=$this->model->where(array('forms_id'=>$forms_id))->order('sort')->select();
		$this->assign('list',$list);
		$this->display(":Attribute");
	}
	//新建表单属性
	public function add(){
		if(IS_POST){
			if ($this->model->create()) {				
				if ($this->model->add()!==false) {
					$this->success("添加成功");
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->model->getError());
			}		
		}else{
			$this->display(":Attribute_add");	
		}		
	}
	//修改表单属性
	public function edit(){
		if (IS_POST) {
			if ($this->model->create()) {
				if ($this->model->save()!==false) {
					$this->success("保存成功！");
				} else {
					$str=$this->model->getLastSql();
					$this->error("保存失败！$str");
				}
			} else {
				$this->error($this->model->getError());
			}
		}else{
			$id=I("get.id");
			$form=$this->model->where("id=$id")->find();
			$this->assign($form);
			$this->display(":Attribute_edit");			
		}
	}
	//删除表单
	public function del(){
		$id = intval(I("get.id"));//删除表单的字段		
		if ($this->model->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}			
	}	
}
