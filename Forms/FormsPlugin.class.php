<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2015 http://www.5iymt.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 5iymt <1145769693@qq.com>
// +----------------------------------------------------------------------
// | Version: 2015-5-23 12:15:11
// +----------------------------------------------------------------------
namespace plugins\Forms;//Demo插件英文名，改成你的插件英文就行了
use Common\Lib\Plugin;

/**
 * Forms
 */
class FormsPlugin extends Plugin{

        public $info = array(
            'name'=>'Forms',
            'title'=>'万能表单（5iymt）',
            'description'=>'万能表单，客户可以用这个做活动报名的表单或者是留言,调查反馈,预约等等功能，具体的大家可以根据自己的需求做定制！',
            'status'=>1,
            'author'=>'5iymt<1145769693@qq.com>',
            'version'=>'1.0'
        );
        
        public $has_admin=1;//插件是否有后台管理界面

        public function install(){//安装方法必须实现
	        $install_sql = './plugins/Forms/install.sql';
			if (file_exists ( $install_sql )) {
				sp_execute_sql_file ( $install_sql );
			}
            return true;//安装成功返回true，失败false
        }

        public function uninstall(){//卸载方法必须实现
        	$install_sql = './plugins/Forms/uninstall.sql';
			if (file_exists ( $install_sql )) {
				sp_execute_sql_file ( $install_sql );
			}
            return true;//卸载成功返回true，失败false
        }
        
        //实现的Forms钩子方法
        public function Forms($param){
        	$config=$this->getConfig();
        	$this->assign($config);
        	$this->display('widget');
        }

    }