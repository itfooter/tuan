<?php 
require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/deal.php';
require './app/Lib/message.php';
require './app/Lib/page.php';

//图片轮播
if(app_conf("BROADCAST")){
$broadcast_all=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."broadcast where code='score'  and is_effect = 1 order by id desc ");
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
if(trim($_REQUEST['act'])=='show')
{
	//获取当前页的团购商品
	$id = intval($_REQUEST['id']);
	$uname = addslashes(trim($_REQUEST['id']));
	
	if($id==0&&$uname=='')
	{
		app_redirect(APP_ROOT."/");
	}
	elseif($id==0&&$uname!='')
	{
		$id = $GLOBALS['db']->getOneCached("select id from ".DB_PREFIX."deal where uname = '".$uname."'"); 
	}
	//获取当前页的团购商品
	$deal = get_deal($id);
	
	if($deal['buy_type']!=1) 
	{
		app_redirect(APP_ROOT."/");
	}
	
	$GLOBALS['tmpl']->assign("page_title", $deal['name']);
		
	
	$GLOBALS['tmpl']->assign("page_keyword",$deal['name']);
	$GLOBALS['tmpl']->assign("page_description",$deal['name']);
	$GLOBALS['tmpl']->assign("deal",$deal);
	
	//供应商的地址列表
	$locations = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."supplier_location where supplier_id = ".intval($deal['supplier_id'])." order by is_main desc");
	$GLOBALS['tmpl']->assign("locations",$locations);
	
	require_once './app/Lib/side.php';  //读取边栏信息,需放在deal数据的分配之后
	
	$coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where deal_id = ".intval($deal['id'])." and is_new = 0 and is_valid = 1 and user_id = ".intval($user_info['id']));
	$tmpl->assign("coupon_data",$coupon_data);
	
	if($deal)
	$tmpl->display("score.html");
	else
	$tmpl->display("no_deal.html");
}
else
{
	//获取当前页的团购商品列表
	//分页
	$page = intval($_REQUEST['p']);
	if($page==0)
	$page = 1;
	$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
	//分类
	$cate_id = intval($_REQUEST['id']);
	$uname = addslashes(trim($_REQUEST['id']));
	if($cate_id==0&&$uname!='')
	{
		$cate_id = $GLOBALS['db']->getOneCached("select id from ".DB_PREFIX."deal_cate where uname = '".$uname."'"); 
	}
	
	
	$act = trim($_REQUEST['act']);
	//显示的类型
	
	$GLOBALS['tmpl']->assign("page_title", $GLOBALS['lang']['SCORE_LIST']);
	$GLOBALS['tmpl']->assign("page_keyword",$GLOBALS['lang']['SCORE_LIST']);
	$GLOBALS['tmpl']->assign("page_description",$GLOBALS['lang']['SCORE_LIST']);
	$type = array(DEAL_ONLINE,DEAL_HISTORY);
	
	
	if(app_conf("SHOW_DEAL_CATE")==1)
	{
		//输出分类
		$deal_cates_db = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 order by sort desc");
		$deal_cates = array();
		if($act=='history'||$act=='notice')
			$url = url_pack("score#".$act);
			else 
			$url = url_pack("score");
		$deal_cates[] = array('id'=>0,'name'=>$GLOBALS['lang']['ALL'],'current'=>$cate_id==0?1:0,'url'=>$url);	
		foreach($deal_cates_db as $k=>$v)
		{		
			if($cate_id==$v['id'])
			$v['current'] = 1;
			if($act=='history'||$act=='notice')
			$v['url'] = url_pack("score#".$act,$v['id'],$v['uname']);
			else 
			$v['url'] = url_pack("score",$v['id'],$v['uname']);
			$deal_cates[] = $v;
		}
	
		$GLOBALS['tmpl']->assign("deal_cate_list",$deal_cates);
	}
	
	$deals = get_deal_list($limit,$cate_id,0,$type,' buy_type = 1 ');
	
	
	
	$GLOBALS['tmpl']->assign("deals",$deals['list']);
	
	
	$page = new Page($deals['count'],app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象 		
	$p  =  $page->show();
	$GLOBALS['tmpl']->assign('pages',$p);
	
	require './app/Lib/side.php';  //读取边栏信息,需放在deal数据的分配之后
	
	if($deals['list'])
	$GLOBALS['tmpl']->display("scores.html");
	else
	$GLOBALS['tmpl']->display("no_deal.html");
}
?>