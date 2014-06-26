<?php 
require './system/common.php';
require './app/Lib/app_init.php';

//会员注册
if($_REQUEST['act'] == 'register')
{
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['USER_REGISTER']);
	
	$field_list = $GLOBALS['cache']->get("USER_FIELD_LIST");
	if($field_list === false)
	{
		$field_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_field order by sort desc");
		
		foreach($field_list as $k=>$v)
		{
			$field_list[$k]['value_scope'] = explode(",",$v['value_scope']);
		}
		$GLOBALS['cache']->set("USER_FIELD_LIST",$field_list);
	}

	$tmpl->assign("reg_name",$_SESSION['api_user_info']['name']);
	
	$tmpl->assign("field_list",$field_list);
		
	$tmpl->display("register.html");
}
//会员注册

//提交注册
if($_REQUEST['act'] == 'doregister')
{
	require_once APP_ROOT_PATH."system/libs/user.php";
	$user_data = $_POST;
	foreach($user_data as $k=>$v)
	{
		$user_data[$k] = htmlspecialchars(addslashes(trim($v)));
	}
	
	if(trim($user_data['user_pwd'])!=trim($user_data['user_pwd_confirm']))
	{
		showErr($GLOBALS['lang']['USER_PWD_CONFIRM_ERROR']);
	}
	if(trim($user_data['user_pwd'])=='')
	{
		showErr($GLOBALS['lang']['USER_PWD_ERROR']);
	}
	
	$user_data['pid'] = $GLOBALS['ref_uid'];
	
	
	$res = save_user($user_data);

	if($_REQUEST['subscribe']==1)
	{
		//订阅
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."mail_list where mail_address = '".$user_data['email']."'")==0)
		{
			$mail_item['city_id'] = intval($_REQUEST['city_id']);
			$mail_item['mail_address'] = $user_data['email'];
			$mail_item['is_effect'] = app_conf("USER_VERIFY");
			$GLOBALS['db']->autoExecute(DB_PREFIX."mail_list",$mail_item,'INSERT','','SILENT');
		}
		if($user_data['mobile']!=''&&$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."mobile_list where mobile = '".$user_data['mobile']."'")==0)
		{
			$mobile['city_id'] = intval($_REQUEST['city_id']);
			$mobile['mobile'] = $user_data['mobile'];
			$mobile['is_effect'] = app_conf("USER_VERIFY");
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_list",$mobile,'INSERT','','SILENT');
		}
	}
	if($res['status'] == 1)
	{
		$user_id = intval($res['data']);
		//更新来路
		$GLOBALS['db']->query("update ".DB_PREFIX."user set referer = '".$GLOBALS['referer']."' where id = ".$user_id);
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);
		if($user_info['is_effect']==1)
		{
			//在此自动登录
			do_login_user($user_data['email'],$user_data['user_pwd']);
			showSuccess($GLOBALS['lang']['REGISTER_SUCCESS'],0,APP_ROOT."/");
		}
		else
		{
			if(app_conf("MAIL_ON")==1)
			{
				//发邮件
				send_user_verify_mail($user_id);
				$user_email = $GLOBALS['db']->getOne("select email from ".DB_PREFIX."user where id =".$user_id);
				//开始关于跳转地址的解析
				$domain = explode("@",$user_email);
				$domain = $domain[1];
				$gocheck_url = '';
				switch($domain)
				{
					case '163.com':
						$gocheck_url = 'http://mail.163.com';
						break;
					case '126.com':
						$gocheck_url = 'http://www.126.com';
						break;
					case 'sina.com':
						$gocheck_url = 'http://mail.sina.com';
						break;
					case 'sina.com.cn':
						$gocheck_url = 'http://mail.sina.com.cn';
						break;
					case 'sina.cn':
						$gocheck_url = 'http://mail.sina.cn';
						break;
					case 'qq.com':
						$gocheck_url = 'http://mail.qq.com';
						break;
					case 'foxmail.com':
						$gocheck_url = 'http://mail.foxmail.com';
						break;
					case 'gmail.com':
						$gocheck_url = 'http://www.gmail.com';
						break;
					case 'yahoo.com':
						$gocheck_url = 'http://mail.yahoo.com';
						break;
					case 'yahoo.com.cn':
						$gocheck_url = 'http://mail.cn.yahoo.com';
						break;
					case 'hotmail.com':
						$gocheck_url = 'http://www.hotmail.com';
						break;
					default:
						$gocheck_url = "";
						break;					
				}
								
				$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['REGISTER_MAIL_SEND_SUCCESS']);
				$GLOBALS['tmpl']->assign("user_email",$user_email);
				$GLOBALS['tmpl']->assign("gocheck_url",$gocheck_url);
				//end 
				$GLOBALS['tmpl']->display("register_email.html");
			}
			else
			showSuccess($GLOBALS['lang']['WAIT_VERIFY_USER'],0,APP_ROOT."/");
		}
	}
	else
	{
		$error = $res['data'];		
		if(!$error['field_show_name'])
		{
				$error['field_show_name'] = $GLOBALS['lang']['USER_TITLE_'.strtoupper($error['field_name'])];
		}
		if($error['error']==EMPTY_ERROR)
		{
			$error_msg = sprintf($GLOBALS['lang']['EMPTY_ERROR_TIP'],$error['field_show_name']);
		}
		if($error['error']==FORMAT_ERROR)
		{
			$error_msg = sprintf($GLOBALS['lang']['FORMAT_ERROR_TIP'],$error['field_show_name']);
		}
		if($error['error']==EXIST_ERROR)
		{
			$error_msg = sprintf($GLOBALS['lang']['EXIST_ERROR_TIP'],$error['field_show_name']);
		}
		showErr($error_msg);
	}
	
}

//会员登录
if($_REQUEST['act'] == 'login')
{
	$login_info = $_SESSION['user_info'];
	if($login_info)
	{
		app_redirect(url_pack("deal"));		
	}
	$url = $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:url_pack("deal");
	if(strpos($url,"dologin")){
		$_SESSION['before_login'] = $_SESSION['before_login'] ;
	}
	else{
		$_SESSION['before_login'] = $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:url_pack("deal");
	}
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['USER_LOGIN']);
	$GLOBALS['tmpl']->assign("CREATE_TIP",$GLOBALS['lang']['REGISTER']);
	$tmpl->display("login.html");
}

//会员登录
if($_REQUEST['act'] == 'api_login')
{
	if($_SESSION['api_user_info'])
	{
		$GLOBALS['tmpl']->assign("page_title",$_SESSION['api_user_info']['name'].$GLOBALS['lang']['HELLO'].",".$GLOBALS['lang']['USER_LOGIN_BIND']);
		$GLOBALS['tmpl']->assign("CREATE_TIP",$GLOBALS['lang']['REGISTER_BIND']);
		$GLOBALS['tmpl']->assign("api_callback",true);
		$tmpl->display("login.html");
	}
	else
	{
		showErr($GLOBALS['lang']['INVALID_VISIT']);
	}
}

//处理会员登录
if($_REQUEST['act'] == 'dologin')
{
	foreach($_POST as $k=>$v)
	{
		$_POST[$k] = htmlspecialchars(addslashes(trim($v)));
	}
	require_once APP_ROOT_PATH."system/libs/user.php";
	if(check_ipop_limit(get_client_ip(),"user_dologin",intval(app_conf("SUBMIT_DELAY"))))

	$result = do_login_user($_POST['email'],$_POST['user_pwd']);

	else

	showErr($GLOBALS['lang']['SUBMIT_TOO_FAST'],0,url_pack("user#login"));
	if($result['status'])
	{	
		//更新购物车
		$GLOBALS['db']->query("update ".DB_PREFIX."deal_cart set user_id = ".intval($_SESSION['user_info']['id'])." where session_id = '".session_id()."'");
		if(intval($_POST['auto_login'])==1)
		{
			//自动登录，保存cookie
			$user_data = $_SESSION['user_info'];
			es_cookie::set("user_name",$user_data['email'],3600*24*30);			
			es_cookie::set("user_pwd",md5($user_data['user_pwd']."_EASE_COOKIE"),3600*24*30);
		}
		
		if(intval($_REQUEST['ajax'])==1&&trim(app_conf("INTEGRATE_CODE"))=='')
		{
			$redirect = $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:url_pack("deal");
			app_redirect($redirect);
		}
		else
		{
			
			if(trim(app_conf("INTEGRATE_CODE"))=='')
			{
				app_redirect($_SESSION['before_login']);
			}
			else
			{
				$GLOBALS['tmpl']->assign('integrate_result',$result['msg']);
				showSuccess($GLOBALS['lang']['LOGIN_SUCCESS'],0,$_SESSION['before_login']);
			}
		}
	}
	else
	{
		if($result['data'] == ACCOUNT_NO_EXIST_ERROR)
		{
			$err = $GLOBALS['lang']['USER_NOT_EXIST'];
		}
		if($result['data'] == ACCOUNT_PASSWORD_ERROR)
		{
			$err = $GLOBALS['lang']['PASSWORD_ERROR'];
		}
		if($result['data'] == ACCOUNT_NO_VERIFY_ERROR)
		{
			$err = $GLOBALS['lang']['USER_NOT_VERIFY'];
			if(app_conf("MAIL_ON")==1)
			{
				$GLOBALS['tmpl']->assign("page_title",$err);
				$GLOBALS['tmpl']->assign("user_info",$result['user']);
				$GLOBALS['tmpl']->display("verify_user.html");
				exit;
			}			
		}
		showErr($err);
	}
}

//会员登出
if($_REQUEST['act'] == 'loginout')
{
	require_once APP_ROOT_PATH."system/libs/user.php";
	$result = loginout_user();
	if($result['status'])
	{
		//更新购物车
		$GLOBALS['db']->query("update ".DB_PREFIX."deal_cart set user_id = ".intval($_SESSION['user_info']['id'])." where session_id = '".session_id()."'");
		es_cookie::delete("user_name");
		es_cookie::delete("user_pwd");
		$GLOBALS['tmpl']->assign('integrate_result',$result['msg']);
		$before_loginout = $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:url_pack("deal");
		if(trim(app_conf("INTEGRATE_CODE"))=='')
		{
			app_redirect($before_loginout);
		}
		else
		showSuccess($GLOBALS['lang']['LOGINOUT_SUCCESS'],0,$before_loginout);
	}
	else
	{
		app_redirect(url_pack("deal"));		
	}
}

if($_REQUEST['act'] == 'verify')
{
	$id = intval($_REQUEST['id']);
	$user_info  = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$id);
	if(!$user_info)
	{
		showErr($GLOBALS['lang']['NO_THIS_USER']);
	}
	$verify = addslashes(trim($_REQUEST['code']));
	if($user_info['verify']!=''&&$user_info['verify'] == $verify)
	{
		//成功
		$_SESSION['user_info'] = $user_info;
		$GLOBALS['db']->query("update ".DB_PREFIX."user set login_ip = '".get_client_ip()."',login_time= ".get_gmtime().",verify = '',is_effect = 1 where id =".$user_info['id']);
		$GLOBALS['db']->query("update ".DB_PREFIX."mail_list set is_effect = 1 where mail_address ='".$user_info['email']."'");	
		$GLOBALS['db']->query("update ".DB_PREFIX."mobile_list set is_effect = 1 where mobile ='".$user_info['mobile']."'");								
		showSuccess($GLOBALS['lang']['VERIFY_SUCCESS'],0,APP_ROOT."/");
	}
	elseif($user_info['verify']=='')
	{
		showErr($GLOBALS['lang']['HAS_VERIFIED'],0,APP_ROOT."/");
	}
	else
	{
		showErr($GLOBALS['lang']['VERIFY_FAILED'],0,APP_ROOT."/");
	}
	
	
}


if($_REQUEST['act'] == 'send')
{
	$id = intval($_REQUEST['id']);
	$user_info  = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$id);
	if(!$user_info)
	{
		showErr($GLOBALS['lang']['NO_THIS_USER']);
	}
	if($user_info['is_effect']==1)
	{
		$showErr($GLOBALS['lang']['HAS_VERIFIED']);
	}
	send_user_verify_mail($user_info['id']);
	showSuccess($GLOBALS['lang']['SEND_HAS_SUCCESS'],0,APP_ROOT."/");	
}

if($_REQUEST['act'] == 'getpassword')
{
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['GET_PASSWORD_BACK']);
	$GLOBALS['tmpl']->display("get_password.html");
}

if($_REQUEST['act'] == 'send_password')
{
	$email = addslashes(trim($_REQUEST['email']));
	if(!check_email($email))
	{
		$GLOBALS['tmpl']->assign("error",$GLOBALS['lang']['MAIL_FORMAT_ERROR']);
	}
	elseif($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where email ='".$email."'") == 0)
	{
		$GLOBALS['tmpl']->assign("error",$GLOBALS['lang']['NO_THIS_MAIL']);
	}
	else 
	{
		$user_info = $GLOBALS['db']->getRowCached('select * from '.DB_PREFIX."user where email='".$email."'");
		send_user_password_mail($user_info['id']);
		$GLOBALS['tmpl']->assign("success",$GLOBALS['lang']['SEND_HAS_SUCCESS']);
	}
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['GET_PASSWORD_BACK']);
	$GLOBALS['tmpl']->display("get_password.html");
}

if($_REQUEST['act'] == 'modify_password')
{
	$id = intval($_REQUEST['id']);
	$user_info  = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$id);
	if(!$user_info)
	{
		showErr($GLOBALS['lang']['NO_THIS_USER']);
	}
	$verify = addslashes(trim($_REQUEST['code']));
	if($user_info['password_verify'] == $verify&&$user_info['password_verify']!='')
	{
		//成功	
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SET_NEW_PASSWORD']);				
		$GLOBALS['tmpl']->assign("user_info",$user_info);
		$GLOBALS['tmpl']->display("modify_password.html");
	}
	else
	{
		showErr($GLOBALS['lang']['VERIFY_FAILED'],0,APP_ROOT."/");
	}	
}

if($_REQUEST['act'] == 'do_modify_password')
{
	$id = intval($_REQUEST['id']);
	$user_info  = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$id);
	if(!$user_info)
	{
		showErr($GLOBALS['lang']['NO_THIS_USER']);
	}
	$verify = $_REQUEST['code'];
	if($user_info['password_verify'] == $verify&&$user_info['password_verify']!='')
	{
		if(trim($_REQUEST['user_pwd'])!=trim($_REQUEST['user_pwd_confirm']))
		{
			$GLOBALS['tmpl']->assign("error",$GLOBALS['lang']['PASSWORD_VERIFY_FAILED']);
		}
		else
		{			
			$password = trim($_REQUEST['user_pwd']);
			$user_info['user_pwd'] = $password;
			$password = md5($password.$user_info['code']);
			$result = 1;  //初始为1
			//载入会员整合
			$integrate_code = trim(app_conf("INTEGRATE_CODE"));
			if($integrate_code!='')
			{
				$integrate_file = APP_ROOT_PATH."system/integrate/".$integrate_code."_integrate.php";
				if(file_exists($integrate_file))
				{
					require_once $integrate_file;
					$integrate_class = $integrate_code."_integrate";
					$integrate_obj = new $integrate_class;
				}	
			}
			
			if($integrate_obj)
			{
				$result = $integrate_obj->edit_user($user_info,$user_info['user_pwd']);				
			}
			if($result>0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."user set user_pwd = '".$password."',password_verify='' where id = ".$user_info['id'] );
				$GLOBALS['tmpl']->assign("success",$GLOBALS['lang']['NEW_PWD_SET_SUCCESS']);
			}
			else
			{
				$GLOBALS['tmpl']->assign("error",$GLOBALS['lang']['NEW_PWD_SET_FAILED']);
			}
		}
		//成功	
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SET_NEW_PASSWORD']);				
		$GLOBALS['tmpl']->assign("user_info",$user_info);
		$GLOBALS['tmpl']->display("modify_password.html");
	}
	else
	{
		showErr($GLOBALS['lang']['VERIFY_FAILED'],0,APP_ROOT."/");
	}	
}


if($_REQUEST['act'] == 'api_create')
{
	if($_SESSION['api_user_info'])
		{
			if($_SESSION['api_user_info']['field'])
			{
				$module = str_replace("_id","",$_SESSION['api_user_info']['field']);
				$module = strtoupper(substr($module,0,1)).substr($module,1);
				require_once APP_ROOT_PATH."system/api_login/".$module."_api.php";
				$class = $module."_api";
				$obj = new $class();
				$obj->create_user();
				app_redirect(APP_ROOT."/");
				exit;
			}			
			showErr($GLOBALS['lang']['INVALID_VISIT']);
		}
		else
		{
			showErr($GLOBALS['lang']['INVALID_VISIT']);
		}
}

?>