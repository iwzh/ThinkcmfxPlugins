<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2015 http://www.5iymt.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 5iymt <1145769693@qq.com>
// +----------------------------------------------------------------------
// | Version: 2015-5-1 12:15:11
// +----------------------------------------------------------------------
namespace plugins\Yichat;
use Common\Lib\Plugin;
use plugins\Yichat\Api\YixinChat;
class YichatPlugin extends Plugin{

	public $info = array(
		'name'=>'Yichat',
		'title'=>'易信插件',
		'description'=>'易信插件（5iymt）',
		'status'=>1,
		'author'=>'5iymt<1145769693@qq.com>',
		'version'=>'1.0',
	);
	
	public $has_admin=0;//插件是否有后台管理界面1:有 0:没有;
    public function install(){//安装方法必须实现
		$install_sql = './plugins/Yichat/install.sql';
		if (file_exists ( $install_sql )) {
			sp_execute_sql_file ( $install_sql );
		}
		return true;//安装成功返回true，失败false
		
	}

	public function uninstall(){//卸载方法必须实现
		$install_sql = './plugins/Yichat/uninstall.sql';
		if (file_exists ( $install_sql )) {
			sp_execute_sql_file ( $install_sql );
		}
		return true;//卸载成功返回true，失败false
	}
	
	//实现的Yichat钩子方法
	public function Yichat($param){
		$config=$this->getConfig();
		$options=array(
			'token'=>$config['token'], //填写你设定的key
			'appid'=>$config['appid'], //填写高级调用功能的app id
			'appsecret'=>$config['appsecret'], //填写高级调用功能的密钥
			//'debug'=>$config['debug'],//dubug开关 2.0暂时占位 2.0版本时候，开启
			//'logcallback'=>$config['appsecret'] //写日志方法名string类型
		);
		$yxObj = new YixinChat($options);
        $yxObj->valid();
		//用户openid:
		$openid = $yxObj->getRev()->getRevFrom();
		$type = $yxObj->getRev()->getRevType();
		switch($type){
			/**事件**/
			case YixinChat::MSGTYPE_EVENT:
			/***Please TO DO ...*/
				$rev_event = $yxObj->getRevEvent();
				/* 检测事件类型 */
				switch ($rev_event['event']){
					case 'subscribe':
					/*关注事件*/
					break;
					case 'unsubscribe':
					/*取消关注事件*/
					break;
					case 'scan':
					/*扫描二维码*/
					break;
				}
			break;
			/**信息**/
			case YixinChat::MSGTYPE_TEXT:
				/* 收到用户主动回复消息处理 */
				$content = $yxObj->getRev()->getRevContent(); //获取消息内容
				/* 将消息内容与已有关键字进行匹配,对相应关键字进行相关响应 */
				if($content){
					D('plugins://Yichat/PluginYichat')->reply($openid,$content,$yxObj,$config);
				}
            	exit;
			break;
			case YixinChat::MSGTYPE_LOCATION:
				/* 收到用户主动回复地理位置 */
				$location = $yxObj->getRev()->getRevGeo();
				$judge = M('PluginYichatUser')->where(array('openid'=>$openid))->find();
				if($judge){
					M('PluginYichatUser')->where(array('id' => $judge['id']))->setField(array('latitude'=>$location['x'],'longitude'=>$location['y'],'labelname'=>$location['label']));
				}else{
					if($config['IsAuth'] == 0){
						$user_data = array(
							'subscribe' => 1,
							'openid' => $openid,
							'subscribe_time' => time(),
							'latitude' => $location['x'],
							'longitude' => $location['y'],
							'labelname' => $location['label']
						);
					}else if($config['IsAuth'] == 1){
						$user_data = $yxObj->getUserInfo($openid);
						$user_data['latitude'] = $location['x'];
						$user_data['longitude'] = $location['y'];
						$user_data['labelname'] = $location['label'];
					}
					M('PluginYichatUser')->add($user_data);
				}
				break;			
		}
	}

}