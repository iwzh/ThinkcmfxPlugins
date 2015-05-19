<?php
namespace plugins\Yichat\Api;
class YixinChat {
    /* 配置参数  */ 

    /* 静态常量 */
    const MSGTYPE_TEXT = 'text';
    const MSGTYPE_IMAGE = 'image';
    const MSGTYPE_LOCATION = 'location';
    const MSGTYPE_LINK = 'link';
    const MSGTYPE_EVENT = 'event';
    const MSGTYPE_MUSIC = 'music';
    const MSGTYPE_NEWS = 'news';
    const MSGTYPE_VOICE = 'voice';
    const MSGTYPE_VIDEO = 'video';
    const MSGTYPE_GOODS = 'goods';
    const MSGTYPE_CARD = 'card';
	const API_URL_PREFIX = 'https://api.yixin.im/cgi-bin';
	const AUTH_URL = '/token?grant_type=client_credential&';
	const MENU_CREATE_URL = '/menu/create?';
	const MENU_GET_URL = '/menu/get?';
	const MENU_DELETE_URL = '/menu/delete?';

    /* 私有参数 */
    private $_msg;
    private $_funcflag = false;
    public $_receive;
    private $_logcallback = null;
	private $token;
	private $appid;
	private $appsecret;
	private $access_token;
	public $debug =  false;  //调试开关
	
    /**
     * 初始化工作
     * @param array $options  array('token'=>'易信接口密钥');
     */
    public function __construct($options=array())
    {
        if (!empty($options))
        {
            $this->token = isset($options['token'])?$options['token']:'';
			$this->appid = isset($options['appid'])?$options['appid']:'';
			$this->appsecret = isset($options['appsecret'])?$options['appsecret']:'';
			$this->debug = isset($options['debug'])?$options['debug']:false;
			$this->_logcallback = isset($options['logcallback'])?$options['logcallback']:false;
        }
    }


    /**
     * 验证请求签名操作
     * @return boolean
     */
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
		
        $token = $this->token;
        $tmpArray = array($token, $timestamp, $nonce);
        sort($tmpArray);
        if(sha1(implode($tmpArray)) == $signature)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 验证当前请求是否有效(可选)
     * @param bool $return 是否返回
     * @return bool|string
     */
    public function valid($return=false)
    {
        $echoStr = isset($_GET["echostr"])?$_GET["echostr"]: '';
        if ($return)
        {
            if ($echoStr)
            {
                if ($this->checkSignature())
                {
                    return $echoStr;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return $this->checkSignature();
            }
        }
        else
        {
            if ($echoStr)
            {
                if ($this->checkSignature())
                {
                    die($echoStr);
                }
                else
                {
                   // die('No Access');
                }
            }
            else
            {
                if ($this->checkSignature())
                {
                    return true;
                }
                else
                {
                    //die('No Access');
                }
            }
        }
    }


    /**
     * 设置发送消息
     * @param array|string $msg 消息数组
     * @param bool $append 是否在原消息数组追加
     * @return array
     */
    public function Message($msg = array(),$append = false){
        if(is_array($msg))
        {
            if ($append){
                $this->_msg = array_merge($this->_msg,$msg);
            }
            else{
                $this->_msg = $msg;
            }
            return $this->_msg;
        }
        else
        {
            return $this->_msg;
        }
    }

    /**
     * 设置星标
     * @param $flag
     * @return $this
     */
    public function setFuncFlag($flag) {
        $this->_funcflag = $flag;
        return $this;
    }

    /**
     * 调试信息日志日记录
     * @param $log
     * @return mixed|null
     */
    private function log($log){
        if ($this->debug && function_exists($this->_logcallback)) {
            if (is_array($log)) $log = print_r($log,true);
            return call_user_func($this->_logcallback,$log);
        }
        return null;
    }

    /**
     * @name 获取易信服务器发来的信息
     * @return mixed
     */
    public function getRev()
    {
        $postStr = file_get_contents("php://input");
        $this->log($postStr);
        if (!empty($postStr))
        {
            $this->_receive = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        return $this;
    }

    /**
     * 获取消息发送者
     * @return string or boolean
     */
    public function getRevFrom()
    {
        if ($this->_receive)
        {
            return $this->_receive['FromUserName'];
        }
        else
        {
            return false;
        }
    }

    /**
     * 获取消息接受者
     * @return string or boolean
     */
    public function getRevTo()
    {
        if ($this->_receive)
        {
            return $this->_receive['ToUserName'];
        }
        else
        {
            return false;
        }
    }

    /**
     * 获取接收消息的类型
     */
    public function getRevType()
    {
        if (isset($this->_receive['MsgType']))
        {
            return $this->_receive['MsgType'];
        }
        else
        {
            return false;
        }
    }

    /**
     * 获取消息ID
     */
    public function getRevID() {
        if (isset($this->_receive['MsgId']))
            return $this->_receive['MsgId'];
        else
            return false;
    }

    /**
     * 获取消息发送时间
     */
    public function getRevCtime() {
        if (isset($this->_receive['CreateTime']))
        {
            return $this->_receive['CreateTime'];
        }
        else{
            return false;
        }
    }

    /**
     * 获取接收消息内容正文
     */
    public function getRevContent(){
        if (isset($this->_receive['Content']))
        {
            return $this->_receive['Content'];
        }
		else if (isset($this->_receive['Url'])) //获取语音地址，需申请开通
		{	
			return array(
				'Url'=>$this->_receive['Url'],
				'name'=>$this->_receive['name'],
				'mimeType'=>$this->_receive['mimeType']
			);
		}
		else if (isset($this->_receive['url'])) //获取视频地址，需申请开通
		{	return array(
				'url'=>$this->_receive['url'],
				'name'=>$this->_receive['name'],
				'mimeType'=>$this->_receive['mimeType']
			);
		}
        else{
            return false;
        }
    }

    /**
     * 获取接收消息图片
     */
    public function getRevPic(){
        if (isset($this->_receive['PicUrl'])){
            return $this->_receive['PicUrl'];
        }
        else{
            return false;
        }
    }
    /**
     * 获取接收消息链接
     */
	/**
    public function getRevLink(){
        if (isset($this->_receive['Url']))
        {
            return array(
                'url'=>$this->_receive['Url'],
                'title'=>$this->_receive['Title'],
                'description'=>$this->_receive['Description']
            );
        }
        else{
            return false;
        }
    }
	/**
     * 获取接收语音推送
     * @return array|bool
     */
	
    public function getRevVoice()
    {
        if ($this->_receive['MsgType']=='audio')
        {
			
            return array(
                'Url'=>$this->_receive['Url'],
                'name'=>$this->_receive['name'],
				'mimeType'=>$this->_receive['mimeType']
            );
        }
        else
        {
            return false;
        }
    }
    /**
     * 获取接收地理位置
     * @return array('x'=>'','y'=>'','scale'=>'','label'=>'')
     */
    public function getRevGeo(){
        if (isset($this->_receive['Location_X'])){
            return array(
                'x'=>$this->_receive['Location_X'],
                'y'=>$this->_receive['Location_Y'],
                'scale'=>$this->_receive['Scale'],
                'label'=>$this->_receive['Label']
            );
        }
        else{
            return false;
        }
    }
	/**
	 * 获取上报地理位置事件
	 */
	public function getRevEventGeo(){
        	if (isset($this->_receive['Latitude'])){
        		 return array(
				'x'=>$this->_receive['Latitude'],
				'y'=>$this->_receive['Longitude'],
				'precision'=>$this->_receive['Precision'],
			);
		} else
			return false;
	}
    /**
     * 获取接收事件推送
     * @return array 成功返回事件数组，失败返回false
     */
    public function getRevEvent(){
        if (isset($this->_receive['Event'])){
			$array['Event'] = $this->_receive['Event'];
		}
		if (isset($this->_receive['EventKey'])){
			$array['EventKey'] = $this->_receive['EventKey'];
		}
		if (isset($array) && count($array) > 0) {
			return $array;
		} else {
			return false;
		}
    }
	/**
	 * 获取扫描带参数二维码事件
	 * 
	 * 事件类型为以下两种时则调用此方法有效
	 * Event	 事件类型，subscribe
	 * Event	 事件类型，scan
	 * 
	 * @return: array | false
	 * array (
	 *     'EventKey'=>'',  事件类型subscribe时候，qrscene_为前缀，后面为二维码的参数值；事件类型scan时候，是一个32位无符号整数
	 *     'Ticket'=>''
	 * )
	 */
	public function getRevScanInfo(){
		if(isset($this->_receive['Event']))
		{			
			return array(
				'EventKey'=>$this->_receive['EventKey'],
				'Ticket'=>$this->_receive['Ticket'],
			);
		}	
	}
    /**
	 * 获取接收TICKET
	 */
	public function getRevTicket(){
		if (isset($this->_receive['Ticket'])){
			return $this->_receive['Ticket'];
		} else
			return false;
	}
	
	/**
	* 获取二维码的场景值
	*/
	public function getRevSceneId (){
		if (isset($this->_receive['EventKey'])){
			return str_replace('qrscene_','',$this->_receive['EventKey']);
		} else{
			return false;
		}
	}
    /**
     * XML特殊字符过滤
     * @param $str
     * @return string
     */
    private static function xmlSafeStr($str)
    {
        return '<![CDATA['.preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$str).']]>';
    }

    /**
     * 数据XML编码
     * @param mixed $data 数据
     * @return string
     */
	private static function data_to_xml($data)
    {
        $xml = '';
        foreach ($data as $key => $val)
        {
            is_numeric($key) && $key = "item";
            $xml    .=  "<$key>";
            $xml    .=  ( is_array($val) || is_object($val)) ? self::data_to_xml($val)  : self::xmlSafeStr($val);
            list($key, ) = explode(' ', $key);
            $xml    .=  "</$key>";
        }
        return $xml;
    }
	
    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id   数字索引子节点key转换的属性名
     * @return string
     */
    private function xml_encode($data, $root='xml', $item='item', $attr='', $id='id')
    {
        if(is_array($attr))
        {
            $_attr = array();
            foreach ($attr as $key => $value)
            {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr = trim($attr);
        $attr = empty($attr) ? '' : " {$attr}";
        $xml = null;
        $xml .= "<{$root}{$attr}>";
        $xml   .= self::data_to_xml($data);
        $xml   .= "</{$root}>";
        return $xml;
    }
	/**
	 * 过滤文字回复\r\n换行符
	 * @param string $text
	 * @return string|mixed
	 */
	private function _auto_text_filter($text) {
		if (!$this->_text_filter) return $text;
		return str_replace("\r\n", "\n", $text);
	}
	
	
/***********************************************************************************************************************************************************/	
    /**
     * 设置回复消息
     * Examle: $obj->text('hello')->reply();
     * @param string $text
     * @return $this
     */
    public function text($text='')
    {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_TEXT,
            'Content'=>$text,
            'CreateTime'=>time()
        );
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置回复音乐
     * @param string $title
     * @param string $desc
     * @param string $musicurl
     * @param string $hgmusicurl
     * @return $this
     */
    public function music($title,$desc,$musicurl,$hgmusicurl='') {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'CreateTime'=>time(),
            'MsgType'=>self::MSGTYPE_MUSIC,
            'Music'=>array(
                'Title'=>$title,
                'Description'=>$desc,
                'MusicUrl'=>$musicurl,
                'HQMusicUrl'=>$hgmusicurl
            )
        );
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置回复图文
     * @param array $newsData
     * @return $this
     * @example 数组结构:
     *  array(
     *  	0=>array(
     *  		'Title'=>'msg title',
     *  		'Description'=>'summary text',
     *  		'PicUrl'=>'http://www.domain.com/1.jpg',
     *  		'Url'=>'http://www.domain.com/1.html'
     *  	),
     *  	1=>....
     *  )
     */
    public function news($newsData=array())
    {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $count = count($newsData);

        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
			'CreateTime'=>time(),
            'MsgType'=>self::MSGTYPE_NEWS,            
            'ArticleCount'=>$count,
            'Articles'=>$newsData
        );
        $this->Message($msg);
        return $this;
    }
	
    /**
     *
     * 向易信服务器回复消息
     * Example: $this->text('msg tips')->reply();
     * @param array|string $msg 要发送的信息, 默认取$this->_msg
     * @param bool $return 是否返回信息而输出  默认：false
     * @return string
     */
    public function reply($msg=array(),$return = false)
    {
        if (empty($msg))
        {
            $msg = $this->_msg;
        }
        $xmldata=  $this->xml_encode($msg);
        $this->log($xmldata);
        if ($return)
        {
            return $xmldata;
        }
        else
        {
            echo $xmldata;
        }
    }

    private static function getTextArea($text,$str_start,$str_end){
        if(empty($text)||empty($str_start))
        {
            return false;
        }
        $start_pos=@strpos($text,$str_start);
        if($start_pos===false){
            return false;
        }
        $end_pos=strpos($text,$str_end, $start_pos);
        if($end_pos>$start_pos && $end_pos!==false)
        {
            $begin_pos=$start_pos+strlen($str_start);
            return substr($text, $begin_pos,$end_pos-$begin_pos);
        }
        else
        {
            return false;
        }
    }
	/**
	 * GET 请求
	 * @param string $url
	 */
	private function http_get($url){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			//curl_setopt($oCurl, CURLOPT_PROXY, $proxy_url);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}
	
	/**
	 * POST 请求
	 * @param string $url
	 * @param array $param
	 * @param boolean $post_file 是否文件上传
	 * @return string content
	 */
	private function http_post($url,$param,$post_file=false){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			//curl_setopt($oCurl, CURLOPT_PROXY, $proxy_url);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		if (is_string($param) || $post_file) {
			$strPOST = $param;
		} else {
			$aPOST = array();
			foreach($param as $key=>$val){
				$aPOST[] = $key."=".urlencode($val);
			}
			$strPOST =  join("&", $aPOST);
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($oCurl, CURLOPT_POST,true);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}	
	/**
	 * 通用auth验证方法，暂时仅用于菜单更新操作
	 * @param string $appid
	 * @param string $appsecret
	 */
	public function checkAuth($appid='',$appsecret=''){
		if (!$appid || !$appsecret) {
			$appid = $this->appid;
			$appsecret = $this->appsecret;
		}		
		$result = $this->http_get(self::API_URL_PREFIX.self::AUTH_URL.'appid='.$appid.'&secret='.$appsecret);
		if ($result)
		{
			$json = json_decode($result,true);			
			if (!$json || isset($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			$this->access_token = $json['access_token'];
			$expire = $json['expires_in'] ? intval($json['expires_in'])-100 : 3600;
			S('authname',$this->access_token,$expire);
			return $this->access_token;
		}
		return false;
	}
	/**
	 * 删除验证数据
	 * @param string $appid
	 */
	public function resetAuth($appid=''){
		if (!$appid) $appid = $this->appid;
		$this->access_token = '';
		$authname = 'wechat_access_token'.$appid;
		S($authname,null);
		return true;
	}
	/**
	 * 微信api不支持中文转义的json结构 /JSON编码
	 * @param array $arr
	 */
	/**
	static function json_encode($arr) 
	{
		$parts = array ();
		$is_list = false;
		//Find out if the given array is a numerical array
		$keys = array_keys ( $arr );
		$max_length = count ( $arr ) - 1;
		if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
			$is_list = true;
			for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
				if ($i != $keys [$i]) { //A key fails at position check.
					$is_list = false; //It is an associative array.
					break;
				}
			}
		}
		foreach ( $arr as $key => $value ) {
			if (is_array ( $value )) { //Custom handling for arrays
				if ($is_list)
					$parts [] = self::json_encode ( $value ); 
				else
					$parts [] = '"' . $key . '":' . self::json_encode ( $value ); 
			} else {
				$str = '';
				if (! $is_list)
					$str = '"' . $key . '":';
				//Custom handling for multiple data types
				if (is_numeric ( $value ) && $value<2000000000)
					$str .= $value; //Numbers
				elseif ($value === false)
				$str .= 'false'; //The booleans
				elseif ($value === true)
				$str .= 'true';
				else
					$str .= '"' . addslashes ( $value ) . '"'; //All other things
				// :TODO: Is there any more datatype we should be in the lookout for? (Object?)
				$parts [] = $str;
			}
		}
		$json = implode ( ',', $parts );
		if ($is_list)
			return '[' . $json . ']'; //Return numerical JSON
		return '{' . $json . '}'; //Return associative JSON
	}
	**/
	/**
	 * 创建菜单
	 * @param array $data 菜单数组数据
	 * example:
     * 	array (
     * 	    'button' => array (
     * 	      0 => array (
     * 	        'name' => '扫码',
     * 	        'sub_button' => array (
     * 	            0 => array (
	 * 				  'key' => 'rselfmenu_0_0',   	             
     * 	              'name' => '扫码带提示',
					  'type' => 'scancode_waitmsg'
     * 	            ),
     * 	            1 => array (
					  'key' => 'rselfmenu_0_1',
					  'name' => '扫码推事件',
     * 	              'type' => 'scancode_push'     * 	             
     * 	            )
     * 	        ),
     * 	      ),
     * 	      1 => array (
     * 	        'name' => '发图',
     * 	        'sub_button' => array (
     * 	             0 => array (
	 * 				  'key' => 'rselfmenu_0_0',   	             
     * 	              'name' => '扫码带提示',
						'type' => 'scancode_waitmsg'
     * 	            ),
     * 	            1 => array (
					  'key' => 'rselfmenu_0_1',
					  'name' => '扫码推事件',
     * 	              'type' => 'scancode_push'     * 	             
     * 	            )
     * 	        ),
     * 	      ),
     * 	      2 => array (
     * 	        'key' => 'rselfmenu_0_1',
					  'name' => '扫码推事件',
     * 	              'type' => 'scancode_push' 
     * 	      ),
     * 	    ),
     * 	);
     * type可以选择为以下2种
     * 1、click：点击推事件
     * 2、view：跳转URL
	 */
	public function createMenu($data){
		header("Content-type:text/html;charset=utf-8");
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::API_URL_PREFIX.self::MENU_CREATE_URL.'access_token='.$this->access_token,json_encode($data));
		if ($result)
		{
			$json = json_decode($result,true);			
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return true;
		}
		return false;
	}
	
	/**
	 * 获取菜单
	 * @return array('menu'=>array(....s))
	 */
	public function getMenu(){
		header("Content-type:text/html;charset=utf-8");
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_get(self::API_URL_PREFIX.self::MENU_GET_URL.'access_token='.$this->access_token);
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || isset($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
	
	/**
	 * 删除菜单
	 * @return boolean
	 */
	public function deleteMenu(){
		header("Content-type:text/html;charset=utf-8");
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_get(self::API_URL_PREFIX.self::MENU_DELETE_URL.'access_token='.$this->access_token);
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return true;
		}
		return false;
	}
}


/**
 * Rolling Curl Request Class
 * @author Ligboy (ligboy@gamil.com)
 * @copyright
 * @example
 *
 *
 */
class CurlHttp {
    /* 单线程请求设置项 */

    /* 并发请求设置项 */
    private $limitCount = 10; //并发请求数量
    public $returninfoswitch = false;  //是否返回请求信息，开启后单项请求返回结果为:array('info'=>请求信息, 'result'=>返回内容, 'error'=>错误信息)

    //私有属性
    private $singlequeue = null;
    private $rollqueue = null;
    private $_requstItems = null;
    private $_callback = null;
    private $_result;
    private $_referer = null;
    private $_cookies = array();
    private $_resheader;
    private $_reqheader = array();
    private $_resurl;
    private $_redirect_url;
    private $referer;

    private $_singleoptions = array(
        CURLOPT_RETURNTRANSFER => true,         // return web page
        CURLOPT_HEADER         => true,        // don't return headers
// 		CURLOPT_FOLLOWLOCATION => true,         // follow redirects
        CURLOPT_NOSIGNAL      =>true,
        CURLOPT_ENCODING       => "",           // handle all encodings
        CURLOPT_USERAGENT      => "",           // who am i
        CURLOPT_AUTOREFERER    => true,         // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,          // timeout on connect
        CURLOPT_TIMEOUT        => 120,          // timeout on response
        CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirects
        CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
        CURLOPT_SSL_VERIFYPEER => false,        //
    );
    private $_rolloptions = array(
        CURLOPT_RETURNTRANSFER => true,         // return web page
        CURLOPT_HEADER         => false,        // don't return headers
// 		CURLOPT_FOLLOWLOCATION => true,         // follow redirects
        CURLOPT_NOSIGNAL      =>true,
        CURLOPT_ENCODING       => "",           // handle all encodings
        CURLOPT_USERAGENT      => "",           // who am i
        CURLOPT_AUTOREFERER    => true,         // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,          // timeout on connect
        CURLOPT_TIMEOUT        => 120,          // timeout on response
        CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirects
        CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
        CURLOPT_SSL_VERIFYPEER => false,        //
    );


    function singleInit($options = array()) {
        if (!$this->singlequeue) {
            $this->singlequeue = curl_init();
        }
        if ($options) {
            $this->_singleoptions = array_merge($this->_singleoptions, $options);
        }
    }
    function rollInit($options = array()) {
        if(!$this->rollqueue){
            $this->rollqueue = curl_multi_init();
        }
        if ($options) {
            $this->_rolloptions = array_merge($this->_rolloptions, $options);
        }
    }

    /**
     * @name 返回Header数组
     * @param resource $ch
     * @param $result
     * @return string
     */
    private function getResRawHeader($ch, $result) {
        $ch_info = curl_getinfo($ch);
        $header_size = $ch_info["header_size"];
        $rawheader = substr($result, 0, $ch_info['header_size']);
        return $rawheader;
    }

    /**
     * @name 返回Header数组
     * @param resource $ch
     * @param $result
     * @return string
     */
    private function getResHeader($ch, $result) {
        $header = array();
        $rawheader = $this->getResRawHeader($ch, $result);
        if(preg_match_all('/([^:\s]+): (.*)/i', $rawheader, $header_match)){
            for($i=0;$i<count($header_match[0]);$i++){
                $header[$header_match[1][$i]] = $header_match[2][$i];
            }
        }
        return $header;
    }

    /**
     * @name 返回网页主体内容
     * @param resource $ch
     * @param $result
     * @return string 网页主体内容
     */
    private function getResBody($ch, $result) {
        $ch_info = curl_getinfo($ch);
        $body = substr($result, -$ch_info['download_content_length']);
        return $body;
    }

    /**
     * @name 返回网页主体内容
     * @param resource $ch
     * @param $result
     * @return array 网页主体内容
     */
    private function getResCookies($ch, $result) {
        $rawheader = $this->getResRawHeader($ch, $result);
        $cookies = array();
        if(preg_match_all('/Set-Cookie:(?:\s*)([^=]*?)=([^\;]*?);/i', $rawheader, $cookie_match)){
            for($i=0;$i<count($cookie_match[0]);$i++){
                $cookies[$cookie_match[1][$i]] = $cookie_match[2][$i];
            }
        }
        return $cookies;
    }

    private function setReqCookies($ch, $reqcookies = array()) {
        $reqCookiesString = "";
        if(!empty($reqcookies)){
            if(is_array($reqcookies)){
                foreach ($reqcookies as $key => $val){
                    $reqCookiesString .=  $key."=".$val."; ";
                }
                curl_setopt($ch, CURLOPT_COOKIE, $reqCookiesString);
            }
        }elseif(!empty($this->_cookies)) {
            foreach ($this->_cookies as $key => $val){
                $reqCookiesString .=  $key."=".$val."; ";
            }
            curl_setopt($ch, CURLOPT_COOKIE, $reqCookiesString);
        }
    }
    private function setResCookies($ch) {
        if(!empty($reqcookies)&&is_array($reqcookies)){
            $this->_cookies = array_merge($this->_cookies, $reqcookies);
        }
    }

    /**
     * @param unknown $url
     * @param mixed $postfields
     * @param string $referer
     * @param array $reqcookies
     * @param array $reqheader
     * @return unknown
     */
    function post($url, $postfields=null, $referer=null, $reqcookies=null, $reqheader=array())
    {
        $this->singlequeue = curl_init($url);
        $options = array(
            CURLOPT_RETURNTRANSFER => true,         // return web page
            CURLOPT_HEADER         => true,        // don't return headers
// 			CURLOPT_FOLLOWLOCATION => true,         // follow redirects
            CURLOPT_ENCODING       => "",           // handle all encodings
            CURLOPT_USERAGENT      => "",     // who am i
            CURLOPT_AUTOREFERER    => true,         // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,          // timeout on connect
            CURLOPT_TIMEOUT        => 120,          // timeout on response
            CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirects
            CURLOPT_POST            => true,            // i am sending post data
            CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
            CURLOPT_SSL_VERIFYPEER => false,        //
        );
        curl_setopt_array($this->singlequeue, $options);
        curl_setopt($this->singlequeue, CURLOPT_POSTFIELDS, $postfields);   // this are my post vars
        if($referer){
            curl_setopt($this->singlequeue, CURLOPT_REFERER, $referer);
        }
        elseif ($this->referer){
            curl_setopt($this->singlequeue, CURLOPT_REFERER, $this->referer);
        }

        $this->setReqheader($this->singlequeue, $reqheader);
        $this->setReqCookies($this->singlequeue, $reqcookies);

        $result = curl_exec($this->singlequeue);
        $resCookies = $this->getResCookies($this->singlequeue, $result);;
        if (is_array($resCookies)&&!empty($resCookies)) {
            $this->_cookies = array_merge($this->_cookies ,$resCookies);
        }
        $resHeader = $this->getResHeader($this->singlequeue, $result);
        if (is_array($resHeader)&&!empty($resHeader)) {
            $this->_resheader = $resHeader;
        }
        $this->_result = $this->getResBody($this->singlequeue, $result);
        curl_close($this->singlequeue);
        $this->singlequeue = null;
        return $this->_result;

    }

    /**
     * @param unknown $url
     * @param unknown $referer
     * @param null $reqcookies
     * @param array $reqheader
     * @return unknown
     */
    function get($url, $referer=null, $reqcookies=null, $reqheader=array())
    {
        $this->singlequeue = curl_init($url);
        $options = array(
            CURLOPT_RETURNTRANSFER => true,         // return web page
            CURLOPT_HEADER         => true,        // don't return headers
// 			CURLOPT_FOLLOWLOCATION => true,         // follow redirects
            CURLOPT_ENCODING       => "",           // handle all encodings
            CURLOPT_USERAGENT      => "",     // who am i
            CURLOPT_AUTOREFERER    => true,         // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,          // timeout on connect
            CURLOPT_TIMEOUT        => 120,          // timeout on response
            CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirects
            CURLOPT_POST            => false,            // i am sending post data
            CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
            CURLOPT_SSL_VERIFYPEER => false,        //
            CURLOPT_REFERER        =>$referer,
        );
        curl_setopt_array($this->singlequeue, $options);
        if($referer){
            curl_setopt($this->singlequeue, CURLOPT_REFERER, $referer);
        }
        elseif ($this->referer){
            curl_setopt($this->singlequeue, CURLOPT_REFERER, $this->referer);
        }
        $this->setReqheader($this->singlequeue, $reqheader);
        $this->setReqCookies($this->singlequeue, $reqcookies);

        $result = curl_exec($this->singlequeue);
        $resCookies = $this->getResCookies($this->singlequeue, $result);
        if (is_array($resCookies)&&!empty($resCookies)) {
            $this->_cookies = array_merge($this->_cookies ,$resCookies);
        }
        $resHeader = $this->getResHeader($this->singlequeue, $result);
        if (is_array($resHeader)) {
            $this->_resheader = $resHeader;
        }
        $this->_result = $this->getResBody($this->singlequeue, $result);
        curl_close($this->singlequeue);
        $this->singlequeue = null;
        return $this->_result;
    }
    /**
     * 并发行的curl方法
     * @param unknown $requestArray
     * @param string $callback
     * @return multitype:multitype:
     */
    function rollRequest($requestArray, $callback="")
    {
        $this->_requstItems = $requestArray;
        $requestArrayKeys = array_keys($requestArray);
        $this->rollqueue = curl_multi_init();
        $map = array();
        for ($i=0;$i<$this->limitCount && !empty($requestArrayKeys);$i++)
        {
            $keyvalue = array_shift($requestArrayKeys);
            $this->addToRollQueue( $requestArray, $keyvalue, $map );

        }

        $responses = array();
        do {
            while (($code = curl_multi_exec($this->rollqueue, $active)) == CURLM_CALL_MULTI_PERFORM) ;

            if ($code != CURLM_OK) { break; }

            // 找到刚刚完成的任务句柄
            while ($done = curl_multi_info_read($this->rollqueue)) {
                // 处理当前句柄的信息、错误、和返回内容
                $info = curl_getinfo($done['handle']);
                $error = curl_error($done['handle']);
                if ($this->_callback)
                {
                    //调用callback函数处理当前句柄的返回内容，callback函数参数有：（返回内容, 队列id）
                    $result = call_user_func($this->_callback, curl_multi_getcontent($done['handle']), $map[(string) $done['handle']]);
                }
                else
                {
                    //如果callback为空，直接返回内容
                    $result = curl_multi_getcontent($done['handle']);
                }
                if ($this->returninfoswitch) {
                    $responses[$map[(string) $done['handle']]] = compact('info', 'error', 'result');
                }
                else
                {
                    $responses[$map[(string) $done['handle']]] = $result;
                }

                // 从队列里移除上面完成处理的句柄
                curl_multi_remove_handle($this->rollqueue, $done['handle']);
                curl_close($done['handle']);
                if (!empty($requestArrayKeys))
                {
                    $addkey = array_shift($requestArrayKeys);
                    $this->addToRollQueue ( $requestArray, $addkey, $map );
                }
            }

            // Block for data in / output; error handling is done by curl_multi_exec
            if ($active > 0) {
                curl_multi_select($this->rollqueue, 0.5);
            }

        } while ($active);

        curl_multi_close($this->rollqueue);
        $this->rollqueue = null;
        return $responses;
    }
    /**
     * @param requestArray
     * @param map
     * @param keyvalue
     */
    private function addToRollQueue($requestArray, $keyvalue, &$map) {
        $ch = curl_init();
        curl_setopt_array($ch, $this->_rolloptions);
        //检查提交方式，并设置对应的设置，为空的话默认采用get方式
        if ("post" === $requestArray[$keyvalue]['method'])
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestArray[$keyvalue]['postfields']);
        }
        else
        {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }


        if($requestArray[$keyvalue]['referer']){
            curl_setopt($ch, CURLOPT_REFERER, $requestArray[$keyvalue]['referer']);
        }
        elseif ($this->referer){
            curl_setopt($ch, CURLOPT_REFERER, $this->referer);
        }
        $this->setReqheader($ch, $requestArray[$keyvalue]['header']);
        //cookies设置
        $this->setReqCookies($ch, $requestArray[$keyvalue]['cookies']);

        curl_setopt($ch, CURLOPT_URL, $requestArray[$keyvalue]['url']);
        curl_setopt($ch, CURLOPT_REFERER, $requestArray[$keyvalue]['referer']);
        curl_multi_add_handle($this->rollqueue, $ch);
        $map[(string) $ch] = $keyvalue;
    }

    /**
     * 返回当前并行数
     * @return the $limitCount
     */
    public function getRollLimitCount() {
        return $this->limitCount;
    }

    /**
     * 设置并发性请求数量
     * @param number $limitCount
     * @return $this
     */
    public function setRollLimitCount($limitCount) {
        $this->limitCount = $limitCount;
        return $this;
    }

    /**
     * 设置回调函数
     * @param field_type $_callback
     * @return $this
     */
    public function setCallback($_callback) {
        $this->_callback = $_callback;
        return $this;
    }

    public function getResult() {
        return $this->_result;
    }

    public function getRawHeader() {
        return $this->_resheader;
    }

    public function getCookies() {
        return $this->_cookies;
    }

    public function setCookies($_cookies) {
        $this->_cookies = $_cookies;
        return $this;
    }

    /**
     * @param $header
     * @return $this
     */
    public function setHeader($header) {
        $this->_reqheader = array_merge($this->_reqheader, $header);
        return $this;
    }

    /**
     * @param resource $ch
     * @param array $reqheader
     * @return $this
     */
    private function setReqheader($ch, $reqheader) {
        $reqheader = array_merge($this->_reqheader, $reqheader);
        if (is_array($reqheader)) {
            $rawReqHeader = array();
            foreach ($reqheader as $key => $value){
                $rawReqHeader[] = "$key: $value";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $rawReqHeader);
            $this->_reqheader = array();
        }
        return $this;
    }
}


/**
 * error code
 * 仅用作类内部使用，不用于官方API接口的errCode码
 */
class ErrorCode
{
    public static $OK = 0;
    public static $ValidateSignatureError = 40001;
    public static $ParseXmlError = 40002;
    public static $ComputeSignatureError = 40003;
    public static $IllegalAesKey = 40004;
    public static $ValidateAppidError = 40005;
    public static $EncryptAESError = 40006;
    public static $DecryptAESError = 40007;
    public static $IllegalBuffer = 40008;
    public static $EncodeBase64Error = 40009;
    public static $DecodeBase64Error = 40010;
    public static $GenReturnXmlError = 40011;
    public static $errCode=array(
            '0'=>'请求成功',
			'40001'=>'验证失败',
			'40002'=>'不合法的凭证类型',
			'40003'=>'不合法的OpenID',
			'40004'=>'不合法的媒体文件类型',
			'40005'=>'不合法的文件类型',
			'40006'=>'不合法的文件大小',
			'40007'=>'不合法的媒体文件id',
			'40008'=>'不合法的消息类型',
			'40009'=>'不合法的图片文件大小',
			'40010'=>'不合法的语音文件大小',
			'40011'=>'不合法的视频文件大小',
			'40012'=>'不合法的缩略图文件大小',
			'40013'=>'不合法的APPID',
			'40014'=>'不合法的access_token',
			'40015'=>'不合法的菜单类型',
			'40016'=>'不合法的按钮个数',
			'40018'=>'不合法的按钮名字长度',
			'40019'=>'不合法的按钮KEY长度',
			'40020'=>'不合法的按钮URL长度',
			'40021'=>'不合法的菜单版本号',
			'40022'=>'不合法的子菜单级数',
			'40023'=>'不合法的子菜单按钮个数',
			'40024'=>'不合法的子菜单按钮类型',
			'40025'=>'不合法的子菜单按钮名字长度',
			'40026'=>'不合法的子菜单按钮KEY长度',
			'40027'=>'不合法的子菜单按钮URL长度',
			'40028'=>'不合法的自定义菜单使用用户',
			'40029'=>'access_token超时',
			'40030'=>'refresh_token超时',
			'40035'=>'不合法的参数',
			'40038'=>'不合法的请求格式',
			'40039'=>'不合法的URL长度',
			'40050'=>'不合法的分组id',
			'40051'=>'分组名字不合法',
			'40052'=>'已有重复的分组名称',
			'40053'=>'不存在的分组名',
			'40054'=>'不允许对黑名单分组群发',
			'40998'=>'未授权的API',
			'40999'=>'非法IP访问',
			'41001'=>'缺少access_token参数',
			'41002'=>'缺少appid参数',
			'41003'=>'缺少refresh_token参数',
			'41004'=>'缺少secret参数',
			'41005'=>'缺少多媒体文件数据',
			'41006'=>'缺少media_id参数',
			'41007'=>'缺少子菜单数据',
			'41009'=>'缺少openid',
			'42001'=>'access_token超时',
			'42008'=>'用户ID不一致',
			'42009'=>'APPID不一致',
			'43001'=>'需要GET请求',
			'43002'=>'需要POST请求',
			'43003'=>'需要HTTPS请求',
			'43004'=>'需要接收者关注',
			'44001'=>'多媒体文件为空',
			'44002'=>'POST的数据包为空',
			'44003'=>'图文消息内容为空',
			'44004'=>'文本消息内容为空',
			'45001'=>'多媒体文件大小超过限制',
			'45002'=>'消息内容超过限制',
			'45003'=>'标题字段超过限制',
			'45004'=>'描述字段超过限制',
			'45005'=>'链接字段超过限制',
			'45006'=>'图片链接字段超过限制',
			'45007'=>'语音播放时间超过限制',
			'45008'=>'图文消息超过限制',
			'45009'=>'接口调用超过限制',
			'45010'=>'创建菜单个数超过限制',
			'45016'=>'系统分组，不允许修改',
			'45017'=>'分组名字过长',
			'45018'=>'分组数量超过上限',
			'45019'=>'分组名称为空',
			'45020'=>'不存在的分组',
			'45021'=>'群发消息量超过限制',
			'45999'=>'接口调用超过每秒限制',
			'46001'=>'不存在媒体数据',
			'46002'=>'不存在的菜单版本',
			'46003'=>'不存在的菜单数据',
			'46004'=>'不存在的用户',
			'47001'=>'解析JSON/XML内容错误',
			'48001'=>'api功能未授权',
			'49000'=>'创建投票活动失败（不合法的请求消息）',
			'49001'=>'不合法的投票标题',
			'49002'=>'不合法的投票说明',
			'49003'=>'不合法的投票结果类型',
			'49004'=>'不合法的投票选项个数',
			'49005'=>'不合法的投票选项',
			'49006'=>'不合法的投票选项类型',
			'49007'=>'不合法的投票选择范围',
			'49008'=>'不合法的投票截止时间',
			'49009'=>'不合法的投票限制类型',
			'49010'=>'不存在的投票活动',
			'49011'=>'投票结果正在处理',
			'49012'=>'投票已结束',
			'49013'=>'投票未结束',
			'50001'=>'该用户没有关注此公众号',
			'50002'=>'该用户不允许公众号获取他的好友关系',
			'50003'=>'识别条形码失败',
			'50005'=>'无法识别该条形码',
			'50006'=>'无法打开所提供的图片链接地址',
			'50007'=>'next_openid未关注此公众号',
			'50008'=>'生成二维码失败（错误的请求内容）',
			'50009'=>'生成二维码失败',
			'50010'=>'不合法的ticket',
			'51001'=>'不合法的mobile',
			'52001'=>'无权访问该动态或该动态不存在',
			'52029'=>'发送的内容涉及敏感词',
			'60001'=>'请求参数中缺少key',
			'60002'=>'公众号不存在',
			'60003'=>'公众号状态错误',
			'60004'=>'用户在黑名单中'
    );
    public static function getErrText($err) {
        if (isset(self::$errCode[$err])) {
            return self::$errCode[$err];
        }else {
            return false;
        };
    }
}
