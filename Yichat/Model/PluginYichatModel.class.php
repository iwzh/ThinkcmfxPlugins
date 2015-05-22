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
	//回复方法
	/*@用户id @回复关键词 @yx @配置*/
	public function reply($openid,$key,$yxObj,$config){
		/*$key = $content;//客户的回复
		$keyArr = array ();//保存的信息数组。
		$user_status = S ( 'user_status_' . $openid );//保存用户当前状态。array()
		$accept = $user_status ['keyArr'] ['accept'];//只认指定类型的可以增加此参数。
		if (($accept ['type'] == 'regex' && ! preg_match ( $accept ['data'], $key )) || ($accept ['type'] == 'array' && ! in_array ( $key, $accept ['data'] ))) {
			$user_status = false;
			S ( 'user_status_' . $openid, null ); // 可设置规定只能接收某些值，如果用户输入的内容不是规定的值，则放弃当前状态,支持正则和数组两种规定方式
		}
		if (! isset (  $func[$key] ) && $user_status) {
			 $func[$key] = $user_status ['addon'];
			$keyArr = $user_status ['keyArr'];
			S ( 'user_status_' . $openid, null );
		}
		*/
		//全词匹配 优先级最高
		if (! isset ( $func[$key] )) {
			$like ['key'] = $key;
			$like ['key_type'] = 0;//0是全词匹配 其它为包含匹配
			$like ['status'] = 1;//规则状态
			$keyArr = M ( 'PluginYichatKeyword' )->where ( $like )->order ( 'id desc' )->find ();
	
			if (! empty ( $keyArr ['func'] )) {
				//存在处理方法
				$func[$key]=$keyArr ['func'];	
				$this->request_count ( $keyArr );		
			}
		}	
		// 通过模糊关键词来定位处理
		if (! isset ( $func[$key] )) {
			unset ( $like ['key'] );
			$like ['key_type'] = array ('gt',0);
			$list = M ( 'PluginYichatKeyword' )->where ( $like )->order ( 'key_len desc, id desc' )->select ();
			
			foreach ( $list as $keyInfo ) {
				$this->_contain_keyword ( $keyInfo, $key, $func, $keyArr );
			}
		}
		//无法匹配时使用通配符
		if (! isset (  $func[$key] )) {
			unset ( $like ['key_type'] );
			$like ['key'] = '*';
			$keywordArr = M ( 'keyword' )->where ( $like )->order ( 'id desc' )->find ();
			
			if (! empty ( $keyArr ['func'] )) {
				$func[$key]=$keyArr ['func'];	
				$this->request_count ( $keyArr );
			}
		}
		if(isset($func[$key])){
			$this->$func[$key]($yxObj,$keyArr);//根据规则调用方法
		}else{
			//最后执行智能聊天插件
        	$this->Chat($yxObj,$key);//开通chat回复后，会自动设置无法回答上来的答案	
		}    
        
       // $yxObj->text("很抱歉,'.$config['name'].'不知道你在说什么,回复'帮助'或者'bz'或者'help'查看可用指令")->reply();
    }
/*	function mult($yxObj,$keyArr){
		$map ['id']=$keyArr['info_id'];
		// 多图文回复
		$mult = M ( 'plugin_keyword_multnews' )->where ( $map )->find ();
		$map_news ['id'] = array (
				'in',
				$mult ['mult_ids'] 
		);
		$list = M ( 'plugin_keyword_news' )->where ( $map_news )->select ();
		
		foreach ( $list as $k => $info ) {
			if ($k > 8)
				continue;
			
			$articles [] = array (
					'Title' => $info ['title'],
					'Description' => $info ['intro'],
					'PicUrl' => get_cover_url ( $info ['cover'] ),
					'Url' => ''
			);
		}
			
	}*/
	//图文消息
	function news($yxObj,$keyArr){
		$map ['id']=$keyArr['info_id'];
		// 单条图文回复
		$info = M ( 'plugin_keyword_news' )->where ( $map )->find ();
			
		// 组装需要的图文数据，格式是固定的
		$data [0] = array (
				'Title' => $info ['title'],
				'Description' => $info ['intro'],
				'PicUrl' => $info ['img'] ,
				'Url' => '' 
		);
		$yxObj->news($data)->reply();	
	}
	function text($yxObj,$keyArr){
		$map ['id']=$keyArr['info_id'];
		// 单条图文回复
		$info = M ( 'plugin_keyword_text' )->where ( $map )->find ();
		empty($info['content'])&&$this->msgerr($yxObj,$keyArr['key']);
		$yxObj->text($info['content'])->reply();	
	}
	//未查询到信息
	function msgerr($yxObj,$key){
		$yxObj->text('未找到关键字：《'.$key.'》的内容')->reply();		
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
   
   	//处理模糊匹配或者正则匹配
	private function _contain_keyword($keyInfo, $key, &$func, &$keyArr) {			
		// 支持正则匹配
		if ($keyInfo ['key_type'] == 4) {
			if (preg_match ( $keyInfo ['key'], $key )) {
				$func [$key] = $keyInfo ['func'];
				$keyArr = $keyInfo;
				$this->request_count ( $keyArr);
			}
			return false;
		}
		
		$arr = explode ( $keyInfo ['key'], $key );
		if (count ( $arr ) > 1) {
			// 在关键词不相等的情况下进行左右匹配判断，否则相等的情况肯定都匹配
			if ($keyInfo ['key'] != $key) {
				// 左边匹配
				if ($keyInfo ['key_type'] == 1 && ! empty ( $arr [0] ))
					return false;
					
					// 右边 匹配
				if ($keyInfo ['key_type'] == 2 && ! empty ( $arr [1] ))
					return false;
			}
			
			$func [$key] = $keyInfo ['func'];
			
			$keyArr = $keyInfo;
			$keyArr ['prefix'] = trim ( $arr [0] ); // 关键词前缀，即包含关键词的前面部分
			$keyArr ['suffix'] = trim ( $arr [1] ); // 关键词后缀，即包含关键词的后面部分
			
			$this->request_count ( $keyArr );
		}
	}
   	//增加关键词响应次数，用来统计分析用户行为
	private function request_count($key,$keyInfo ['key']){
		empty($keyInfo)&& (return false);
		$data['time']=NOW_TIME;
		$data['replyinfo']=$key;
		$data['requestinfo']=$keyInfo ['key'];
		$data['requestid']=$keyInfo ['id'];
		$data['replytype']='Yichat';
		M ( 'PluginYichatKeywordLog')->add ($data);
	}
}