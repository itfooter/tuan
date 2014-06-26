<?php 
require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/message.php';
require './app/Lib/deal.php';
require './app/Lib/page.php';

//图片轮播
if(app_conf("BROADCAST")){
$broadcast_all=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."broadcast where code='message'  and is_effect = 1 order by id desc ");
//设置多个城市
$broadcast=array();
foreach ($broadcast_all as $bk=>$bv)
{
   $city_ids=explode(',', $bv['city_ids']);
   if(in_array(intval($GLOBALS['deal_city']['id']), $city_ids))
   {
   	  $broadcast[]=$bv;
   }
}

$broadcast_nums=count($broadcast);
$GLOBALS['tmpl']->assign("broadcast_nums",$broadcast_nums); 
$GLOBALS['tmpl']->assign("broadcast_list",$broadcast); 
}
if($_REQUEST['act'] == 'add')
{
	if(!$user_info)
	{
		showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST']);
	}
	if($_REQUEST['content']=='')
	{
		showErr($GLOBALS['lang']['MESSAGE_CONTENT_EMPTY']);
	}
	if(!check_ipop_limit(get_client_ip(),"message",intval(app_conf("SUBMIT_DELAY")),0))
	{
		showErr($GLOBALS['lang']['MESSAGE_SUBMIT_FAST']);
	}
	
	$rel_table = trim(addslashes($_REQUEST['rel_table']));
	$message_type = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."' and type_name <> 'supplier'");
	if(!$message_type)
	{
		showErr($GLOBALS['lang']['INVALID_MESSAGE_TYPE']);
	}
	
	$message_group = trim(addslashes($_REQUEST['message_group']));

	//添加留言
	$message['title'] = $_REQUEST['title']?htmlspecialchars(addslashes($_REQUEST['title'])):htmlspecialchars(addslashes($_REQUEST['content']));
	$message['content'] = htmlspecialchars(addslashes($_REQUEST['content']));
	if($message_group)
	{
		$message['title']="[".$message_group."]:".$message['title'];
		$message['content']="[".$message_group."]:".$message['content'];
	}
	
	$message['create_time'] = get_gmtime();
	$message['rel_table'] = $rel_table;
	$message['rel_id'] = trim(addslashes($_REQUEST['rel_id']));
	$message['user_id'] = intval($GLOBALS['user_info']['id']);
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
	
	$message['is_buy'] = intval($_REQUEST['is_buy']);
	$message['contact'] = $_REQUEST['contact']?htmlspecialchars(addslashes($_REQUEST['contact'])):'';
	$message['contact_name'] = $_REQUEST['contact_name']?htmlspecialchars(addslashes($_REQUEST['contact_name'])):'';
	if($message['is_buy']==1)
	{
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.deal_id = ".intval($message['rel_id'])." and do.user_id = ".intval($message['user_id'])." and do.pay_status = 2")==0)
		{
			showErr($GLOBALS['lang']['AFTER_BUY_MESSAGE_TIP']);
		}
		//不允许重复评价
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."message where is_buy=1 and rel_id=".$message['rel_id']." and user_id=".$message['user_id']) != 0)
			showErr($GLOBALS['lang']['DO_NOT_REPEAT_COMMENT']);
		//插入星级评价
		$point_group = $_REQUEST['dp_point_group'];
		foreach($point_group as $k=>$v)
		{
			$items['pid']= $message['user_id'];
			$items['type']= $k;
			$items['point']=$v;
			$items['rel_id']=$message['rel_id'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."comment",$items,'INSERT','','SILENT');
		}
	}
	//退款申请
	if(!empty($message_group) && $message_group == $GLOBALS['lang']['ORDER_TYPE_2'])
	{
		if(isset($_REQUEST['deal_id']))
		{
			$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id=".$message['rel_id']);
			if($order['pay_status'] == 0)
			{
				showErr($GLOBALS['lang']['DEAL_NO_PAY']);
			}
			else
			{
				$daal_array = $_REQUEST['deal_id'];
				foreach($daal_array as $d_id)
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set buy_status = 1 where buy_status = 0 and order_id = ".$message['rel_id']." and deal_id = ".$d_id);
			}
		}
		else
			showErr($GLOBALS['lang']['PLEASE_SELECT_ONE_DEAL']);
	}
	$message['point'] = intval($_REQUEST['point']);
	$GLOBALS['db']->autoExecute(DB_PREFIX."message",$message);

	showSuccess($GLOBALS['lang']['MESSAGE_POST_SUCCESS']);
	
}
else
{
	$rel_table = addslashes(trim($_REQUEST['act']));
	$message_type = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."' and type_name <> 'supplier'");
	if(!$message_type||$message_type['is_fix']==0)
	{
		$message_type_list = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."message_type where is_fix = 0 order by sort desc");
		if(!$message_type_list)
		{
			showErr($GLOBALS['lang']['INVALID_MESSAGE_TYPE']);
		}
		else
		{
			if(!$message_type)
			$message_type = $message_type_list[0];
			foreach($message_type_list as $k=>$v)
			{
				if($v['type_name'] == $message_type['type_name'])
				{
					$message_type_list[$k]['current'] = 1;
				}
				else
				{
					$message_type_list[$k]['current'] = 0;
				}
			}
			$GLOBALS['tmpl']->assign("message_type_list",$message_type_list);
		}
	}
	$rel_table = $message_type['type_name'];
	$condition = '';	
	$id = intval($_REQUEST['id']);
	if($rel_table == 'deal')
	{
		$deal = get_deal($id);
		if($deal['buy_type']!=1)
		$GLOBALS['tmpl']->assign("deal",$deal);
		$id = $deal['id'];
		//根据商品分类获取评价分组
		$types = $GLOBALS['db']->getAll("select ectl.comment_type_id from ".DB_PREFIX."deal edl,".DB_PREFIX."comment_type_link ectl where edl.cate_id=ectl.category_id and edl.id = ".$id);
		$commentstype="";
		foreach($types as $k=>$v)
		{
			$temp = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."comment_type where is_effect = 1 and id=".$v['comment_type_id']);
			if($temp)
			{
				$commentstype[$k]['id'] = $v['comment_type_id'];
				$commentstype[$k]['name'] = $temp;
			}
		
		}
		//总评字段
		$maintype = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."comment_type where is_effect = 1 and is_main=1");

		$GLOBALS['tmpl']->assign("commentstype",$commentstype);
		$GLOBALS['tmpl']->assign("maintype",$maintype);
	}
	require './app/Lib/side.php'; 
	if($id>0)
	$condition = "rel_table = '".$rel_table."' and rel_id = ".$id;
	else
	$condition = "rel_table = '".$rel_table."'";

	if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
	{
		$condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
	}
	else 
	{
		if($message_type['is_effect']==0)
		{
			$condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
		}
	}
	
	$condition.=" and is_buy = ".intval($_REQUEST['is_buy']);
	//message_form 变量输出
	$GLOBALS['tmpl']->assign("post_title",$message_type['show_name']);
	$GLOBALS['tmpl']->assign("page_title",$message_type['show_name']);
	$GLOBALS['tmpl']->assign('rel_id',$id);
	$GLOBALS['tmpl']->assign('rel_table',$rel_table);
	$GLOBALS['tmpl']->assign('is_buy',intval($_REQUEST['is_buy']));
	
	if(intval($_REQUEST['is_buy'])==1)
	{
		$GLOBALS['tmpl']->assign("post_title",$GLOBALS['lang']['AFTER_BUY']);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['AFTER_BUY']);		
	}
	
	if(!$GLOBALS['user_info'])
	{
		$GLOBALS['tmpl']->assign("message_login_tip",sprintf($GLOBALS['lang']['MESSAGE_LOGIN_TIP'],url_pack("user#login"),url_pack("user#register")));
	}
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
	$GLOBALS['tmpl']->display("message.html");
}
?>