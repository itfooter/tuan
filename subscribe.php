<?php 
require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/deal.php';
require './app/Lib/message.php';
require './app/Lib/side.php'; 

if($_REQUEST['act']=='mail')
{
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['MAIL_SUBSCRIBE']);
	$GLOBALS['tmpl']->display("subscribe_mail.html");
}
elseif($_REQUEST['act']=='addmail')
{
	
	$ajax = intval($_REQUEST['ajax']);
	if(!check_ipop_limit(get_client_ip(),"subscribe#addmail",intval(app_conf("SUBMIT_DELAY")),0))
	{
		showErr($GLOBALS['lang']['SUBMIT_TOO_FAST'],$ajax);
	}
	if(trim($_REQUEST['email'])=='')
	{
		showErr($GLOBALS['lang']['EMAIL_EMPTY_TIP'],$ajax);
	}
	
	if(!check_email($_REQUEST['email']))
	{
		showErr($GLOBALS['lang']['EMAIL_FORMAT_ERROR_TIP'],$ajax);
	}
	
	if($_REQUEST['othercity']&&trim($_REQUEST['othercity'])!='')	
	{
		//提交其他城市		
		$other_city = htmlspecialchars(addslashes($_REQUEST['othercity']));
		$other_city_item = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_city where name = '".$other_city."'");
		if($other_city_item)
		{
			$city_id = $other_city_item['id'];
		}
		else
		{
			$new_city['name'] =  $other_city;
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_city",$new_city);
			$city_id = $GLOBALS['db']->insert_id();
		}
	}
	elseif(intval($_REQUEST['cityid'])!=0)
	{
		$city_id = intval($_REQUEST['cityid']);
	}
	else
	{
		$city_item = get_current_deal_city();
		$city_id = $city_item['id'];
	}
	
	$mail_item['mail_address'] = addslashes(trim(htmlspecialchars($_REQUEST['email'])));
	$mail_item['city_id'] = $city_id;
	$mail_item['is_effect'] = 1;

	if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."mail_list where mail_address='".$mail_item['mail_address']."'")==0)
	{
		//没有订阅过
		$GLOBALS['db']->autoExecute(DB_PREFIX."mail_list",$mail_item);
	}
	showSuccess($GLOBALS['lang']['SUBSCRIBE_SUCCESS'],$ajax);
}
elseif($_REQUEST['act']=='unsubscribe')
{
	$email_code = trim($_REQUEST['code']);
	$email = base64_decode($email_code);
	if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."mail_list where mail_address='".$email."'")==0)
	{
		showSuccess($GLOBALS['lang']['MAIL_UNSUBSCRIBE_SUCCESS'],0,APP_ROOT."/");
	}
	else
	{
		send_unsubscribe_mail($email);
		showSuccess($GLOBALS['lang']['MAIL_UNSUBSCRIBE_VERIFY'],0,APP_ROOT."/");
	}
	
}
elseif($_REQUEST['act']=='dounsubscribe')
{
	$email_code = trim($_REQUEST['code']);
	$email_code =  base64_decode($email_code);
	$arr = explode("|",$email_code);
	$GLOBALS['db']->query("delete from ".DB_PREFIX."mail_list where code = '".$arr[0]."' and mail_address = '".$arr[1]."'");
	$rs = $GLOBALS['db']->affected_rows();
	if($rs)
	{
		showSuccess($GLOBALS['lang']['MAIL_UNSUBSCRIBE_SUCCESS'],0,APP_ROOT."/");
	}
	else
	{
		showErr($GLOBALS['lang']['MAIL_UNSUBSCRIBE_FAILED'],0,APP_ROOT."/");
	}
}
?>