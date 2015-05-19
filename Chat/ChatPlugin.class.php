<?php
// +----------------------------------------------------------------------
// | 5 [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\Chat;//Chat插件英文名，改成你的插件英文就行了
use Common\Lib\Plugin;

/**
 * Chat
 */
class ChatPlugin extends Plugin{//Chat插件英文名，改成你的插件英文就行了

        public $info = array(
            'name'=>'Chat',//Chat插件英文名，改成你的插件英文就行了
            'title'=>'聊天插件',
            'description'=>'只能聊天插件支持微信，易信等',
            'status'=>1,
            'author'=>'5iymt<1145769693@qq.com>',
            'version'=>'1.0'
        );
        
       	public $has_admin=0;//插件是否有后台管理界面1:有 0:没有;
        public function install(){//安装方法必须实现
            return true;//安装成功返回true，失败false
        }

        public function uninstall(){//卸载方法必须实现
            return true;//卸载成功返回true，失败false
        }
        
        //实现Chat钩子返回数据
        public function Chat($param){
        	$config=$this->getConfig();
			$data=sp_get_plugns_return('Chat://Index/index',array('keywords'=>'你好'));
        	//D("plugins://Chat/PluginChat")->test();exit;
        }

    }