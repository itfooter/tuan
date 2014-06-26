<?php
$api_lang = array(
	'name'	=>	'新浪api登录接口',
	'app_key'	=>	'新浪API应用APP_KEY',
	'app_secret'	=>	'新浪API应用APP_SECRET',
        'app_url'	=>	'回调地址',
);

$config = array(
	'app_key'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //新浪API应用的KEY值
	'app_secret'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //新浪API应用的密码值
        'app_url'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //回调地址
);

/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{   
    	if(ACTION_NAME=='install')
	{
		//更新字段
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `sina_id`  varchar(255) NOT NULL",'SILENT');
		//$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `sina_app_key`  varchar(255) NOT NULL",'SILENT');
		//$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `sina_app_secret`  varchar(255) NOT NULL",'SILENT');
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `sina_token`  varchar(255) NOT NULL",'SILENT');
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `is_syn_sina`  tinyint(1) NOT NULL",'SILENT');
	}
        
                    $module['class_name']    = 'Sina';

                /* 名称 */
                    $module['name']    = $api_lang['name'];

                    $module['config'] = $config;

                    $module['lang'] = $api_lang;

                return $module;
}

// 新浪的api登录接口
require_once(APP_ROOT_PATH.'system/libs/api_login.php');
class Sina_api implements api_login {
	
	private $api;
	
	public function __construct($api)
	{
		$api['config'] = unserialize($api['config']);
		$this->api = $api;
	}
	
	public function get_api_url()
	{
		require_once APP_ROOT_PATH.'system/api_login/sina/saetv2.ex.class.php';
                $o = new SaeTOAuthV2($this->api['config']['app_key'],$this->api['config']['app_secret']);
		@session_start();
		//$keys = $o->getRequestToken();
		if($this->api['config']['app_url']=="")
		{
			$app_url = get_domain().APP_ROOT."/api_callback.php?c=Sina";
		}
		else
		{
			$app_url = $this->api['config']['app_url'];
		}
		$aurl = $o->getAuthorizeURL($app_url);

                $_SESSION['is_bind'] = 0;
                
		$str = "<a href='".$aurl."' title='".$this->api['name']."'><img src='".$this->api['icon']."' alt='".$this->api['name']."' /></a>&nbsp;";
		return $str;
	}
        
        public function get_big_api_url()
	{
		require_once APP_ROOT_PATH.'system/api_login/sina/saetv2.ex.class.php';
		$o = new SaeTOAuthV2($this->api['config']['app_key'],$this->api['config']['app_secret']);
		@session_start();
		//$keys = $o->getRequestToken();
		//$aurl = $o->getAuthorizeURL($keys['oauth_token'] ,false , get_domain().APP_ROOT."/api_callback.php?c=Sina");

		if($this->api['config']['app_url']=="")
		{
			$app_url = get_domain().APP_ROOT."/api_callback.php?c=Sina";
		}
		else
		{
			$app_url = $this->api['config']['app_url'];
		}
		$aurl = $o->getAuthorizeURL($app_url);
		$_SESSION['is_bind'] = 0;	
		
		$str = "<a href='".$aurl."' title='".$this->api['name']."'><img src='".$this->api['bicon']."' alt='".$this->api['name']."' /></a>&nbsp;";
		return $str;
	}	
        public function get_bind_api_url()
	{
		require_once APP_ROOT_PATH.'system/api_login/sina/saetv2.ex.class.php';
		$o = new SaeTOAuthV2($this->api['config']['app_key'],$this->api['config']['app_secret']);
		@session_start();
		//$keys = $o->getRequestToken();
		if($this->api['config']['app_url']=="")
		{
			$app_url = get_domain().APP_ROOT."/api_callback.php?c=Sina";
		}
		else
		{
			$app_url = $this->api['config']['app_url'];
		}
		$aurl = $o->getAuthorizeURL($app_url);	
		$_SESSION['is_bind'] = 1;
		$str = "<a href='".$aurl."' title='".$this->api['name']."'><img src='".$this->api['bicon']."' alt='".$this->api['name']."' /></a>&nbsp;";
		return $str;
	}	
	
	public function callback()
	{
		require_once APP_ROOT_PATH.'system/api_login/sina/saetv2.ex.class.php';
		@session_start();
                $o = new SaeTOAuthV2($this->api['config']['app_key'],$this->api['config']['app_secret']);
		if (isset($_REQUEST['code'])) {
			$keys = array();
			$keys['code'] = $_REQUEST['code'];
			if($this->api['config']['app_url']=="")
			{
				$app_url = get_domain().APP_ROOT."/api_callback.php?c=Sina";
			}
			else
			{
				$app_url = $this->api['config']['app_url'];
			}
			$keys['redirect_uri'] = $app_url;
			try {
				$token = $o->getAccessToken( 'code', $keys ); 
			} catch (OAuthException $e) {
				print_r($e);exit;
			}
		}
		
		
		$c = new SaeTClientV2($this->api['config']['app_key'],$this->api['config']['app_secret'] ,$token['access_token'] );
		$ms  = $c->home_timeline(); // done
		$uid_get = $c->get_uid();
		$uid = $uid_get['uid'];
		$msg = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息
		
		
		$msg['field'] = 'sina_id';
		$msg['sina_token'] = $token['access_token'];
                $_SESSION['api_user_info'] = $msg;
		$user_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where sina_id = ".trim($msg['id'])." and sina_id <> 0");	
                if($user_data)
		{
				$user_current_group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_group where id = ".intval($user_data['group_id']));
				$user_group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_group where score <=".intval($user_data['score'])." order by score desc");
				if($user_current_group['score']<$user_group['score'])
				{
					$user_data['group_id'] = intval($user_group['id']);
				}
				$_SESSION['user_info'] = $user_data;
				$GLOBALS['db']->query("update ".DB_PREFIX."user set login_ip = '".get_client_ip()."',login_time= ".get_gmtime().",group_id=".intval($user_data['group_id'])." where id =".$user_data['id']);				
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_cart set user_id = ".intval($_SESSION['user_info']['id'])." where session_id = '".session_id()."'");
				unset($_SESSION['api_user_info']);
				app_redirect(url_pack("index"));
		}
		else
                        $this->create_user();
			app_redirect(url_pack("index#api_login"));
		
	}

	
	public function get_title()
	{
		return '新浪api登录接口，需要php_curl扩展的支持';
	}
	public function create_user()
	{
                $s_api_user_info=$_SESSION['api_user_info'] ;
		$user_data['user_name'] = $_SESSION['api_user_info']['name'];
		$user_data['user_pwd'] = md5(rand(100000,999999));
		$user_data['create_time'] = get_gmtime();
		$user_data['update_time'] = get_gmtime();
		$user_data['login_ip'] = get_client_ip();
		$user_data['group_id'] = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_group order by score asc limit 1");
		$user_data['is_effect'] = 1;
		$user_data['sina_id'] = $_SESSION['api_user_info']['id'];
                $user_data['sina_token'] = $s_api_user_info['sina_token'];
		
		$count = 0;
		do{
			if($count>0)
			$user_data['user_name'] = $user_data['user_name'].$count;
			if(!empty($user_data['sina_id']))
			$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_data,"INSERT",'','SILENT');
			$rs = $GLOBALS['db']->insert_id();
			$count++;
		}while(intval($rs)==0&&!empty($user_data['sina_id']));
		
		$_SESSION['user_info'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($rs));
		unset($_SESSION['api_user_info']);
	}	
}
?>