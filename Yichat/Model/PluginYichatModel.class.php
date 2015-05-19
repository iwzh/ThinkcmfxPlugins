<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2015 http://www.5iymt.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 5iymt <1145769693@qq.com>
// +----------------------------------------------------------------------
// | Version: 2015-5-1 12:15:11
// +----------------------------------------------------------------------

namespace plugins\Yichat\Model;//Demo插件英文名，改成你的插件英文就行了
use Common\Model\CommonModel;//继承CommonModel
class PluginYichatModel extends CommonModel{
	public function __construct() {
		
    } 
	//自定义方法
	public function reply($openid,$content,$yxObj,$config){
        //根据content,查询关键字表,调用相应方法进行回复
        $lists = M('PluginYichatAutoreply')->where(array('status'=>1))->select();
        foreach($lists as $val){
        	if(preg_match($val['rule'],$content, $matchs)){
        		$this->$val['function']($openid,$yxObj,$config,$matchs);
        		exit();
        	}
        }
        unset($val);
        $this->Chat($yxObj,$content);//开通chat回复后，会自动设置无法回答上来的答案
        
       // $yxObj->text("很抱歉,'.$config['name'].'不知道你在说什么,回复'帮助'或者'bz'或者'help'查看可用指令")->reply();
    }
	/*
	*自动回复聊天【调用智能聊天接口插件】
	*@param unknown $openid $yxObj $config $content
	*author 5iymt<1145769693@qq.com>
	*version 2015-5-3 16:30:58
	*copyright www.5iymt.com
	*/
	public function Chat($yxObj,$keyword){		
		$content=sp_get_plugns_return('Chat://Index/reply',array('keyword'=>$keyword));
		switch($content['type']){
			case 'news':
				$yxObj->news($content['data'])->reply();	
				break;
			case 'text':
				$yxObj->text($content['data'])->reply();	
				break;
		}
	}
	/**
     * [replyWeather 回复天气预报 百度地图天气接口]
     * @param unknown $openid $yxObj $config $matchs
     * @access public
     * @author 5iymt 
     * @version 2015-3-7 下午1:31:55
     * @copyright www.5iymt.com
     */
    public function replyWeather($openid,$yxObj,$config,$matchs){
    	$json_array = file_get_contents('http://api.map.baidu.com/telematics/v3/weather?location='.$matchs[1].'&output=json&ak=' . $config['BaiduAk']);
    	$json_array = json_decode($json_array,true);
    	$array = $json_array['results'][0]['weather_data'];
    	date_default_timezone_set ('Asia/Shanghai');
    	$h=date('H');
    	if($json_array['error'] > -3){
    		foreach ($array as $key=>$val){
    			date_default_timezone_set(PRC);
    			$h=date('H');
    			if($h>=8 && $h<=19){
    				$articles [$key] = array (
    						'Title' => $val['date']."\n".$val['weather']." ".$val['wind']." ".$val['temperature'],
    						'Description' => '',
    						'PicUrl' => $val['dayPictureUrl'],
    						'Url' => ''
    				);
    			}else {
    				$articles [$key] = array (
    						'Title' => $val['date']."\n".$val['weather']." ".$val['wind']." ".$val['temperature'],
    						'Description' => '',
    						'PicUrl' => $val['nightPictureUrl'],
    						'Url' => ''
    				);
    			}
    		}
    		$tarray = array (
    				'Title' => $json_array['results'][0]['currentCity']."天气预报",
    				'Description' => '',
    				'PicUrl' => '',
    				'Url' => ''
    		);
    		array_unshift($articles,$tarray);
    		$yxObj->news($articles)->reply();
    	}else {
    		$yxObj->text("没找到耶！...〒_〒")->reply();
    	}
    }
   
    /**
     * 
     * [replyFind 回复找周边百度地图]
	 * @param unknown $openid $yxObj $config $matchs
     * @access public
     * @author 5iymt 
     * @version 2015-3-7 下午1:38:39
     * @copyright www.5iymt.com
     */
    public function replyFind($openid,$yxObj,$config,$matchs){
    	$judge = M('PluginYichatUser')->where(array('openid'=>$openid))->find();
		$name=empty($config['name'])?'玉米糖':$config['name'];
    	if($judge){
    		if($judge['latitude'] && $judge['longitude']){
    			$json_array = json_decode(file_get_contents('http://api.map.baidu.com/place/v2/search?query=' . urlencode($matchs[1]) . '&output=json&ak=' . $config['BaiduAk'] . '&page_size=10&page_num=0&scope=2&location=' . $judge['latitude'] . ',' . $judge['longitude'] . '&radius=2000'),true);
    			if($json_array['message'] == 'ok'){
    				foreach($json_array['results'] as $k => $v){
    					$img_array = json_decode(file_get_contents('http://map.baidu.com/detail?qt=img&uid=' . $v['uid']),true);
    					$articles[$k] = array (
    							'Title' => $v['name'] . "\n地址:" . $v['address'] . "\n距离:" . $v['detail_info']['distance'] . "米",
    							'Description' => '',
    							'PicUrl' => $img_array['images']['all'][0]['imgUrl'],
    							'Url' => $v['detail_info']['detail_url']
    					);
    				}
    				$yxObj->news($articles)->reply();
    			}else{
    				$yxObj->text('抱歉,查询不到')->reply();
    			}
    		}else{
    			$yxObj->text('请先发送位置哦(右下角的"+"号->位置->发送位置),不然'.$name.'不知道该从何找起')->reply();
    			exit();
    		}
    	}else{
    		if($config['IsAuth'] == 0){
    			$user_data = array(
    					'subscribe' => 1,
    					'openid' => $openid,
    					'subscribe_time' => time()
    			);
    		}else if($config['IsAuth'] == 1){
    			$user_data = $yxObj->getUserInfo($openid);
    		}
    		M('PluginYichatUser')->add($user_data);
    		$yxObj->text('请先发送位置哦,不然'.$name.'不知道该从何找起')->reply();
    		exit();
    	}
    }
   
}