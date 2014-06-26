<?php 
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/uc.php';

if($_REQUEST['act']=='index')
{
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_ACCOUNT']);
	
	//扩展字段
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
	
	foreach($field_list as $k=>$v)
	{
		$field_list[$k]['value'] = $GLOBALS['db']->getOne("select value from ".DB_PREFIX."user_extend where user_id=".$user_info['id']." and field_id=".$v['id']);
	}
	
	$GLOBALS['tmpl']->assign("field_list",$field_list);
	
	$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_account_index.html");
	$GLOBALS['tmpl']->display("uc.html");
}
elseif($_REQUEST['act']=='save')
{
		require_once APP_ROOT_PATH.'system/libs/user.php';
		foreach($_REQUEST as $k=>$v)
		{
			$_REQUEST[$k] = htmlspecialchars(addslashes(trim($v)));
		}
                $_REQUEST['id'] = $_SESSION['user_info']["id"];
		$res = save_user($_REQUEST,'UPDATE');
		if($res['status'] == 1)
		{
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = '".$_SESSION['user_info']['id']."'");
			$_SESSION['user_info'] = $user_info;
			showSuccess($GLOBALS['lang']['SAVE_USER_SUCCESS']);			
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
elseif($_REQUEST['act']=='consignee')
{
	//输出配送方式
	$consignee_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_consignee where user_id = ".$user_info['id']);
	
	if($consignee_id>0)
	{
		$consignee_info = $GLOBALS['cache']->get("CONSIGNEE_INFO_".$consignee_id);
		if($consignee_info === false)
		{
			$consignee_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_consignee where id = ".$consignee_id);
			$GLOBALS['cache']->set("CONSIGNEE_INFO_".$consignee_id,$consignee_info);
		}
		
		
		$region_lv1 = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_region where pid = 0");  //一级地址
		foreach($region_lv1 as $k=>$v)
		{
			if($v['id'] == $consignee_info['region_lv1'])
			{
				$region_lv1[$k]['selected'] = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv1",$region_lv1);
		
		$region_lv2 = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_region where pid = ".$consignee_info['region_lv1']);  //二级地址
		foreach($region_lv2 as $k=>$v)
		{
			if($v['id'] == $consignee_info['region_lv2'])
			{
				$region_lv2[$k]['selected'] = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);
		
		$region_lv3 = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_region where pid = ".$consignee_info['region_lv2']);  //三级地址
		foreach($region_lv3 as $k=>$v)
		{
			if($v['id'] == $consignee_info['region_lv3'])
			{
				$region_lv3[$k]['selected'] = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv3",$region_lv3);
		
		$region_lv4 = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_region where pid = ".$consignee_info['region_lv3']);  //四级地址
		foreach($region_lv4 as $k=>$v)
		{
			if($v['id'] == $consignee_info['region_lv4'])
			{
				$region_lv4[$k]['selected'] = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv4",$region_lv4);
		
		$GLOBALS['tmpl']->assign("consignee_info",$consignee_info);
	}
	else
	{
		$region_lv1 = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_region where pid = 0");  //一级地址
		$GLOBALS['tmpl']->assign("region_lv1",$region_lv1);
	}
	
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_CONSIGNEE']);
	$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_account_consignee.html");
	$GLOBALS['tmpl']->display("uc.html");	
}
elseif($_REQUEST['act'] == 'save_consignee')
{
	if(trim($_REQUEST['consignee'])=='')
	{
		showErr($GLOBALS['lang']['FILL_CORRECT_CONSIGNEE']);
	}
	if(trim($_REQUEST['address'])=='')
	{
		showErr($GLOBALS['lang']['FILL_CORRECT_ADDRESS']);
	}
	if(trim($_REQUEST['zip'])=='')
	{
		showErr($GLOBALS['lang']['FILL_CORRECT_ZIP']);
	}
	if(trim($_REQUEST['mobile'])=='')
	{
		showErr($GLOBALS['lang']['FILL_MOBILE_PHONE']);
	}
	if(!check_mobile($_REQUEST['mobile']))
	{
		showErr($GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE']);
	}
	
	$consignee_data['user_id'] = intval($user_info['id']);
	$consignee_data['region_lv1'] = intval($_REQUEST['region_lv1']);
	$consignee_data['region_lv2'] = intval($_REQUEST['region_lv2']);
	$consignee_data['region_lv3'] = intval($_REQUEST['region_lv3']);
	$consignee_data['region_lv4'] = intval($_REQUEST['region_lv4']);
	$consignee_data['address'] = addslashes(trim(htmlspecialchars($_REQUEST['address'])));
	$consignee_data['mobile'] = addslashes(trim(htmlspecialchars($_REQUEST['mobile'])));
	$consignee_data['consignee'] = addslashes(trim(htmlspecialchars($_REQUEST['consignee'])));
	$consignee_data['zip'] = addslashes(trim(htmlspecialchars($_REQUEST['zip'])));
	
	$consignee_id = intval($_REQUEST['id']);
	if($consignee_id == 0)
	{
		$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee",$consignee_data);
	}
	else
	{
		$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee",$consignee_data,"UPDATE","id=".$consignee_id);
	}
	$GLOBALS['cache']->rm("CONSIGNEE_INFO_".intval($consignee_id));
	showSuccess($GLOBALS['lang']['UPDATE_SUCCESS']);
}

?>