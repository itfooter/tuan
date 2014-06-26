<?php
$api_lang = array(
	'name'	=>	'腾讯微博登录插件',
	'app_key'	=>	'腾讯API应用APP_KEY',
	'app_secret'	=>	'腾讯API应用APP_SECRET',
	'app_url'	=>	'回调地址',
);

$config = array(
	'app_key'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //腾讯API应用的KEY值
	'app_secret'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //腾讯API应用的密码值
	'app_url'	=>	array(
		'INPUT_TYPE'	=>	'0'
	),
);

/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
	if(ACTION_NAME=='install')
	{
		//更新字段
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `tencent_id`  varchar(255) NOT NULL",'SILENT');
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `t_access_token`  varchar(255) NOT NULL",'SILENT');
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `t_openkey`  varchar(255) NOT NULL",'SILENT');
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `t_openid`  varchar(255) NOT NULL",'SILENT');
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `is_syn_tencent`  tinyint(1) NOT NULL",'SILENT');
	}
    $module['class_name']    = 'Tencent';

    /* 名称 */
    $module['name']    = $api_lang['name'];

	$module['config'] = $config;
	$module['is_weibo'] = 1;  //可以同步发送微博
	
	$module['lang'] = $api_lang;
    
    return $module;
}

// 腾讯的api登录接口
require_once(APP_ROOT_PATH.'system/libs/api_login.php');
class Tencent_api implements api_login {
	
	private $api;
	
	public function __construct($api)
	{		
		$api['config'] = unserialize($api['config']);
		$this->api = $api;		
	}
	
	public function get_api_url()
	{
		@session_start();
		require_once APP_ROOT_PATH.'system/api_login/Tencent/Tencent.php';

		OAuth::init($this->api['config']['app_key'], $this->api['config']['app_secret']);
		if($this->api['config']['app_url']=="")
		{
			$app_url = get_domain().APP_ROOT."/api_callback.php?c=Tencent";
		}
		else
		{
			$app_url = $this->api['config']['app_url'];
		}
		$aurl = OAuth::getAuthorizeURL($app_url);
                $_SESSION['is_bind']= 0;
		
		$str = "<a href='".$aurl."' title='".$this->api['name']."'><img src='".$this->api['icon']."' alt='".$this->api['name']."' /></a>&nbsp;";
		return $str;
	}
	
	public function get_big_api_url()
	{
		@session_start();
		require_once APP_ROOT_PATH.'system/api_login/Tencent/Tencent.php';

		OAuth::init($this->api['config']['app_key'], $this->api['config']['app_secret']);
		if($this->api['config']['app_url']=="")
		{
			$app_url = get_domain().APP_ROOT."/api_callback.php?c=Tencent";
		}
		else
		{
			$app_url = $this->api['config']['app_url'];
		}
		$aurl = OAuth::getAuthorizeURL($app_url);
		 $_SESSION['is_bind']= 0;
		$str = "<a href='".$aurl."' title='".$this->api['name']."'><img src='".$this->api['bicon']."' alt='".$this->api['name']."' /></a>&nbsp;";
		return $str;
	}	
	
	public function get_bind_api_url()
	{
		@session_start();
		require_once APP_ROOT_PATH.'system/api_login/Tencent/Tencent.php';

		OAuth::init($this->api['config']['app_key'], $this->api['config']['app_secret']);
		if($this->api['config']['app_url']=="")
		{
			$app_url = get_domain().APP_ROOT."/api_callback.php?c=Tencent";
		}
		else
		{
			$app_url = $this->api['config']['app_url'];
		}
		$aurl = OAuth::getAuthorizeURL($app_url);
                $_SESSION['is_bind'] = 1;
		
		$str = "<a href='".$aurl."' title='".$this->api['name']."'><img src='".$this->api['bicon']."' alt='".$this->api['name']."' /></a>&nbsp;";
		return $str;
	}		
	public function callback()
	{
		@session_start();		
		require_once APP_ROOT_PATH.'system/api_login/Tencent/Tencent.php';
		OAuth::init($this->api['config']['app_key'], $this->api['config']['app_secret']);
		
		$code = trim(addslashes($_REQUEST['code']));
		$openid = trim(addslashes($_REQUEST['openid']));
		$openkey = trim(addslashes($_REQUEST['openkey']));
		
		if($this->api['config']['app_url']=="")
		{
			$app_url = get_domain().APP_ROOT."/api_callback.php?c=Tencent";
		}
		else
		{
			$app_url = $this->api['config']['app_url'];
		}
		
		$token_url = OAuth::getAccessToken($code,$app_url);
		$result = Http::request($token_url);
		$result = preg_replace('/[^\x20-\xff]*/', "", $result); //清除不可见字符
                $result = iconv("utf-8", "utf-8//ignore", $result); //UTF-8转码

                parse_str($result,$result_arr);

                $access_token = $result_arr['access_token'];
                $refresh_token = $result_arr['refresh_token'];
                $name = $result_arr['name'];
                $nick = $result_arr['nick'];
		
		$is_bind = intval($_SESSION['is_bind']);
		
                $_SESSION['t_access_token'] = $access_token;
                $_SESSION['t_openid'] = $openid;
                 $_SESSION['t_openkey'] = $openkey;
		
		if ($_SESSION['t_access_token']|| ($_SESSION['t_openid']&&$_SESSION['t_openkey'])) 
		{
  			
   			$msg['field'] = 'tencent_id';
			$msg['id'] = $name;
			$msg['name'] = $name;
			$msg['t_access_token'] = $access_token;
			$msg['t_openid'] = $access_token;
			$msg['t_openkey'] = $openkey;
                        $_SESSION['api_user_info'] = $msg;
			//if(!$msg['name'])app_redirect(url("index"));
			$user_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where tencent_id = '".$name."' and tencent_id <> ''");	
			if($user_data)
			{
					$user_current_group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_group where id = ".intval($user_data['group_id']));
					$user_group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_group where score <=".intval($user_data['score'])." order by score desc");
					if($user_current_group['score']<$user_group['score'])
					{
						$user_data['group_id'] = intval($user_group['id']);
					}
                                        $_SESSION['user_info'] = $user_data;	
					$GLOBALS['db']->query("update ".DB_PREFIX."user set t_access_token ='".$access_token."',t_openkey = '".$openkey."',t_openid = '".$openid."', login_ip = '".get_client_ip()."',login_time= ".get_gmtime().",group_id=".intval($user_data['group_id'])." where id =".$user_data['id']);				
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_cart set user_id = ".intval($user_data['id'])." where session_id = '".session_id()."'");
					unset($_SESSION['api_user_info']);
                                        app_redirect(url_pack("index"));
			}
			else{
				$this->create_user();
                                app_redirect(url_pack("index#api_login"));
			}
		}
		

		
		
	}
	
	public function get_title()
	{
		return '腾讯api登录接口，需要php_curl扩展的支持';
	}
	public function create_user()
	{
		$s_api_user_info = $_SESSION['api_user_info'];
                $user_data['user_name'] = $s_api_user_info['name'];
		$user_data['user_pwd'] = md5(rand(100000,999999));
		$user_data['create_time'] = get_gmtime();
		$user_data['update_time'] = get_gmtime();
		$user_data['login_ip'] = get_client_ip();
		$user_data['group_id'] = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_group order by score asc limit 1");
		$user_data['is_effect'] = 1;
		$user_data['tencent_id'] = $s_api_user_info['id'];
		$user_data['t_access_token'] = $s_api_user_info['t_access_token'];
		$user_data['t_openkey'] = $s_api_user_info['t_openkey'];
		$user_data['t_openid'] = $s_api_user_info['t_openid'];
		
		$count = 0;
		do{
			if($count>0)
			$user_data['user_name'] = $user_data['user_name'].$count;
			if($user_data['tencent_id'])
			$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_data,"INSERT",'','SILENT');
			$rs = $GLOBALS['db']->insert_id();
			$count++;
		}while(intval($rs)==0&&$user_data['tencent_id']);
		
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($rs));
                  $_SESSION['user_info'] = $user_info;
                    unset($_SESSION['api_user_info']);
	}
	
   
}
?>