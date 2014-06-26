<?php 
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/message.php';
require './app/Lib/uc.php';

if($_REQUEST['act']=='index')
{
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_MESSAGE']);
	$GLOBALS['tmpl']->assign("post_title",$GLOBALS['lang']['UC_MESSAGE']);
	//以下关于订单留言的输出
	$condition = " user_id = ".intval($GLOBALS['user_info']['id']);

	
	//message_form 变量输出
	$GLOBALS['tmpl']->assign('rel_table','feedback');

	
	//分页
	$page = intval($_REQUEST['p']);
	if($page==0)
	$page = 1;
	$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
	$message = get_message_list($limit,$condition);
	
	$page = new Page($message['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
	$p  =  $page->show();
	$GLOBALS['tmpl']->assign('pages',$p);
	
	$GLOBALS['tmpl']->assign("message_list",$message['list']);

	//end订单留言
	
	$GLOBALS['tmpl']->assign("inc_file","inc/message_form.html");
	$GLOBALS['tmpl']->display("uc.html");	
}
?>