<?php
namespace plugins\Chat\Controller; //Chat插件英文名，改成你的插件英文就行了
use Api\Controller\PluginController;//插件控制器基类

class IndexController extends PluginController{
	public $config;
	function __construct(){
		$this->config=sp_get_plugin_config('Chat');
	}
	public function index(){
		return array('data'=>'我是5iymt的demo','type'=>'text');
	}
	/**
	 * reply 供其它插件调用
	 * 返回json格式数据 格式请参考微信，易信要求回复的消息格式
	 * author 
	 **/
	public function reply($keyword=''){
		if(empty($keyword)){
			$keyword=I('keyword');
		}
		//5iymt
		$content = $this->_tuling ($keyword);//优先使用图灵接口	
		// 再尝试小黄鸡
		if (empty ( $content )&& $this->config ['simsim_url'] && $this->config ['simsim_key'] ) {
			$content =$this->_simsim ( $keyword );			
		}
		// TODO 此处可继续增加其它API接口
		$is_rand=$this->config['is_rand'];
		// 最后只能随机回复了需要开启随机回复		
		if (empty ($content) && $is_rand ) {
			$content = $this->_rand ();
		}	
		if(is_array($content)){			
			return $content;
		}else{
			return array('data'=>$content,'type'=>'text');
		}
	}
		// 随机回复
	private function _rand() {
		$this->config ['rand_reply'] = array_map ( 'trim', explode ( "\n", $this->config ['rand_reply'] ) );
		$key = array_rand ( $this->config ['rand_reply'] );
		
		return $this->config ['rand_reply'] [$key];
	}
	
	// 小黄鸡
	private function _simsim($keyword) {
		$api_url = $this->config ['simsim_url'] . "?key=" . $this->config ['simsim_key'] . "&lc=ch&ft=0.0&text=" . $keyword;
		
		$result = file_get_contents ( $api_url );
		$result = json_decode ( $result, true );
		
		return $result ['response'];
	}
	// 图灵机器人 返回array||false
	private function _tuling($keyword) {
		$api_url = $this->config ['tuling_url'] . "?key=" . $this->config ['tuling_key'] . "&info=" . $keyword;
		//$result = file_get_contents ( $api_url );//这个方法不知道是不是sae有bug，所以替换curl了！
		$result=$this->httpGetRequest( $api_url);		
		$result = json_decode ( $result, true );
		if ($result ['code'] < 40008) {
			if ($result ['code'] > 40000 && ! empty ( $result ['text'] )) {
				$Text= $this->config ['name'].'请你注意：' . $result ['text'] ;
			} 
		}
		switch ($result ['code']) {
			case '200000' :
				$Text = $result ['text'] . ',<a href="' . $result ['url'] . '">点击进入</a>';
				break;
			case '301000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['name'],
							'Description' => $info ['author'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$News = $articles ;
				break;
			case '302000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['article'],
							'Description' => $info ['source'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$News = $articles ;
				break;
			case '304000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['name'],
							'Description' => $info ['count'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$News = $articles ;
				break;
			case '305000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['start'] . '--' . $info ['terminal'].'('.$info ['trainnum'].')',
							'Description' => $info ['starttime'] . '--' . $info ['endtime'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$News = $articles ;
				break;
			case '306000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['flight'] . '--' . $info ['route'],
							'Description' => $info ['starttime'] . '--' . $info ['endtime'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$News = $articles ;
				break;
			case '307000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['name'],
							'Description' => $info ['info'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$News = $articles ;
				break;
			case '308000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['name'],
							'Description' => $info ['info'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$News = $articles ;
				break;
			case '309000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['name'],
							'Description' => '价格 : ' . $info ['price'] . ' 满意度 : ' . $info ['satisfaction'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$News = $articles ;
				break;
			case '310000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['number'],
							'Description' => $info ['info'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$News = $articles ;
				break;
			case '311000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['name'],
							'Description' => '价格 : ' . $info ['price'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$News = $articles ;
				break;
			case '312000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['name'],
							'Description' => '价格 : ' . $info ['price'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$News = $articles ;
				break;
			default :
				if (!empty ( $result ['text'] )) {
					$Text=$result ['text'];
				}
		}
		$data=false;
		if(isset($Text)){
			return $Text;
		}
		if(isset($News)){
			return array('data'=>$News,'type'=>'news');
		}
	}
	function httpGetRequest($url)
    {
        $headers = array(
            "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:14.0) Gecko/20100101 Firefox/14.0.1",
            "Content-type: text/html; charset=utf-8;"
        );
        $ch      = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        curl_close($ch);
        if ($output === FALSE) {
            return "cURL Error: " . curl_error($ch);
        }
        return $output;
    }
}
