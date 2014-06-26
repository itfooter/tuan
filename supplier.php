<?php 
require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/supplier.php';
require './app/Lib/message.php';
require './app/Lib/page.php';

if($_REQUEST['act']=='show')
{
	$id = intval($_REQUEST['id']);
	$supplier_info = get_supplier_info($id);
	
	if($supplier_info)	
	{
		$GLOBALS['tmpl']->assign("page_title", $supplier_info['name']);
		$GLOBALS['tmpl']->assign("page_keyword",$supplier_info['name']);
		$GLOBALS['tmpl']->assign("page_description",$supplier_info['name']);
		$GLOBALS['tmpl']->assign("supplier",$supplier_info);
		$GLOBALS['tmpl']->assign("message_login_tip",sprintf($GLOBALS['lang']['MESSAGE_LOGIN_TIP'],url_pack("user#login"),url_pack("user#register")));
		$GLOBALS['tmpl']->display("supplier.html");
	}
	else
	showErr($GLOBALS['lang']['NO_SUPPLIER']);
}
elseif($_REQUEST['act']=='load_deal')
{
	$id = intval($_REQUEST['id']);
	$page = intval($_REQUEST['p']);
	if($page==0)
	$page = 1;
	$limit = (($page-1)*4).",4";
	
	require './app/Lib/deal.php';
	
	$condition = " supplier_id = ".$id." and buy_type <> 1";
	$deals = get_deal_list($limit,0,0,array(DEAL_ONLINE,DEAL_HISTORY),$condition);

	$GLOBALS['tmpl']->assign("deals",$deals['list']);	
	
	$page = new Page($deals['count'],4);   //初始化分页对象 		
	$p  =  $page->show();
	$GLOBALS['tmpl']->assign('pages',$p);
	
	$GLOBALS['tmpl']->display("inc/supplier_deals.html");	
}
elseif($_REQUEST['act']=='load_message')
{
	//分页
	$id = intval($_REQUEST['id']);
	$page = intval($_REQUEST['p']);
	$point = intval($_REQUEST['point']);
	if($page==0)
	$page = 1;
	$limit = (($page-1)*4).",4";

	if($point>0)
	$condition = " rel_table = 'supplier' and rel_id = ".$id." and point = ".$point;
	else
	$condition = " rel_table = 'supplier' and rel_id = ".$id;
	$message = get_message_list($limit,$condition);
	
	$page = new Page($message['count'],4);   //初始化分页对象 		
	$p  =  $page->show();
	$GLOBALS['tmpl']->assign('pages',$p);
	$messages = $message['list'];
	foreach($messages as $k=>$msg)
	{
		$messages[$k]['comment'] = $GLOBALS['lang']['COMMENT'.$msg['point']];
		$messages[$k]['user_name'] = $GLOBALS['db']->getOneCached("select user_name from ".DB_PREFIX."user where id = ".intval($msg['user_id']));
		
		$time_span_lang = pass_date($msg['create_time']);	
		$messages[$k]['post_time'] = sprintf($GLOBALS['lang']['SUPPLIER_COMMENT_SAY'],$time_span_lang);
		
	}
	
	$GLOBALS['tmpl']->assign("message_list",$messages);
	$GLOBALS['tmpl']->assign("point",$point);
	$GLOBALS['tmpl']->assign("supplier_id",$id);
	$GLOBALS['tmpl']->display("inc/supplier_messages.html");
}
elseif($_REQUEST['act']=='add_comment')
{
	if(!$user_info)
	{
		showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],1);
	}
	if($_REQUEST['content']=='')
	{
		showErr($GLOBALS['lang']['MESSAGE_CONTENT_EMPTY'],1);
	}
	if(!check_ipop_limit(get_client_ip(),"supplier_comment",intval(app_conf("SUBMIT_DELAY")),0))
	{
		showErr($GLOBALS['lang']['MESSAGE_SUBMIT_FAST'],1);
	}
	
	$rel_table = "supplier";
	$message_type = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."'");
	if(!$message_type)
	{
		showErr($GLOBALS['lang']['INVALID_MESSAGE_TYPE'],1);
	}
	

	//添加留言
	$message['title'] = $_REQUEST['title']?htmlspecialchars(addslashes($_REQUEST['title'])):htmlspecialchars(addslashes($_REQUEST['content']));
	$message['content'] = htmlspecialchars(addslashes($_REQUEST['content']));
	
	$message['create_time'] = get_gmtime();
	$message['rel_table'] = $rel_table;
	$message['rel_id'] = $_REQUEST['rel_id'];
	$message['user_id'] = intval($GLOBALS['user_info']['id']);
	$message['point'] = intval($_REQUEST['point']);
	if(intval($_REQUEST['city_id'])==0)
	$message['city_id'] = $deal_city['id'];
	else
	$message['city_id'] = intval($_REQUEST['city_id']);
	if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
	{
		$message_effect = 0;
	}
	else
	{
		$message_effect = $message_type['is_effect'];
	}
	$message['is_effect'] = $message_effect;
	
	$GLOBALS['db']->autoExecute(DB_PREFIX."message",$message);
	showSuccess($GLOBALS['lang']['MESSAGE_POST_SUCCESS'],1);
}
else
{
	//获取当前页的团购商品列表
	//分页
	$page = intval($_REQUEST['p']);
	if($page==0)
	$page = 1;
	$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
	//分类
	$cate_id = intval($_REQUEST['id']);
	
	$GLOBALS['tmpl']->assign("page_title", $GLOBALS['lang']['SUPPLIER_LIST']);
	$GLOBALS['tmpl']->assign("page_keyword",$GLOBALS['lang']['SUPPLIER_LIST']);
	$GLOBALS['tmpl']->assign("page_description",$GLOBALS['lang']['SUPPLIER_LIST']);
		
	if(app_conf("SHOW_DEAL_CATE")==1)
	{
		//输出分类
		$deal_cates_db = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 order by sort desc");
		$deal_cates = array();
		$url = url_pack("supplier");
		$deal_cates[] = array('id'=>0,'name'=>$GLOBALS['lang']['ALL'],'current'=>$cate_id==0?1:0,'url'=>$url);	
		foreach($deal_cates_db as $k=>$v)
		{		
			if($cate_id==$v['id'])
			$v['current'] = 1;
			$v['url'] = url_pack("supplier",$v['id']);
			$deal_cates[] = $v;
		}
	
		$GLOBALS['tmpl']->assign("deal_cate_list",$deal_cates);
	}
	
	//获取搜索字段	
	$suppliers = get_supplier_list($limit,$cate_id,0);
	
	$GLOBALS['tmpl']->assign("suppliers",$suppliers['list']);	
	
	$page = new Page($suppliers['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
	$p  =  $page->show();
	$GLOBALS['tmpl']->assign('pages',$p);
	
	require './app/Lib/side.php';  //读取边栏信息,需放在deal数据的分配之后
	
	$GLOBALS['tmpl']->display("suppliers.html");
}
?>