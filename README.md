# ThinkcmfxPlugins
This's plugins of thinkcmfx 
从ThinkCMFX1.5引入插件机制以来，都有要做插件的冲动，怎奈因个人原因，总是没能得偿夙愿，这段时间，把易信插件整理出来了。水平有限，欢迎大家指正、批评。 
目前初步完成插件如下：
  1.易信插件
    对接易信，实现易信公众号开发。目前初版仅基本回复功能，可通过调用智能聊天插件实现更多功能
    使用说明： 我的易信插件对接url地址默认为http://5iymt.sinaapp.com/index.php?g=Iymt&m=Yichat&a=index 
    需要在application文件夹下新建目录Iymt
    新建hook.php 内容如下
```
<?php
return array(
		'Yichat',
		'Wechat',
		'AliWindows',
		'Chat'
);//用于加载钩子
```
新建Controller文件夹，新建YichatController.class.php文件，内容如下：
```
<?php
namespace Iymt\Controller;
use Think\Controller; 
/**
 * 插件对接地址
 * Author:5iymt Contact:www.5iymt.com 1145769693@qq.com
 */
class YichatController extends Controller {
	public function index() {
		hook('Yichat');//暴露易信插件钩子
    }   

}

```
  2.智能聊天插件
    为其它插件提供聊天服务，通过调用图灵，小黄鸡和随机回复，扩展插件趣味性
  3.微信插件
    功能和使用方式同易信类似暴露Wechat插件
  