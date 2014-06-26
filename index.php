<?php 

require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/deal.php';
require './app/Lib/message.php';
require './app/Lib/page.php';


//初始化风格
if($_REQUEST['style']=='multi')
{
	 es_cookie::set("ES_SHOW_TYPE",'multi');
}
if($_REQUEST['style']=='single')
{
	 es_cookie::set("ES_SHOW_TYPE",'single');	 
}

if(app_conf("STYLE_OPEN")==1)
{
	$default_style = intval(app_conf("STYLE_DEFAULT"))==0?'single':'multi';
	$style = es_cookie::get("ES_SHOW_TYPE")?es_cookie::get("ES_SHOW_TYPE"):$default_style;
}
else
{
	$style = intval(app_conf("STYLE_DEFAULT"))==0?'single':'multi';
}
if($style=='single')
{
	$GLOBALS['tmpl']->assign("style_url",APP_ROOT."/?style=multi"); 
	$GLOBALS['tmpl']->assign("switch_style_tip",$GLOBALS['lang']['MULTI_STYLE_TIP']); 	
}
if($style=='multi')
{
	$GLOBALS['tmpl']->assign("style_url",APP_ROOT."/?style=single");
	$GLOBALS['tmpl']->assign("switch_style_tip",$GLOBALS['lang']['SINGLE_STYLE_TIP']); 	
}
if($_REQUEST['act'] == 'set_price')
{
	$s_price = intval($_REQUEST['s_p']);
	$m_price = intval($_REQUEST['m_p']);
	if($m_price == 100)
	{
		 $condition = "and current_price  between 0 and 100";
		 es_cookie::set("current_price_bt",$condition);
		 es_cookie::set("current_positon",2);
	}
	elseif($s_price == 100 && $m_price == 300)
	{
		 $condition = "and current_price  between 100 and 300";
		 es_cookie::set("current_price_bt",$condition);
		 es_cookie::set("current_positon",3);
	}
	elseif($s_price == 300)
	{
		 $condition = "and current_price>300";
		 es_cookie::set("current_price_bt",$condition);
		 es_cookie::set("current_positon",4);
	}
	else
	{
		es_cookie::set("current_price_bt",'');
		es_cookie::set("current_positon",1); 
	}
}
//图片轮播

if(app_conf("BROADCAST")){

$broadcast_all=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."broadcast where code='index'  and is_effect = 1 order by id desc ");

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


//图片轮播
if(app_conf("BROADCAST")){
$broadcast_all=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."broadcast where code='index'  and is_effect = 1 order by id desc ");
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
if($style=='single')
{	
	if(app_conf("SHOW_DEAL_CATE")==1)
	{
		$deal_cate_id = intval($_REQUEST['id']);
		$uname = addslashes(trim($_REQUEST['id']));
		if($deal_cate_id==0&&$uname!='')
		{
			$deal_cate_id = $GLOBALS['db']->getOneCached("select id from ".DB_PREFIX."deal_cate where uname = '".$uname."'"); 
		}
		
		
		//输出分类
		$deal_cates_db = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 order by sort desc");
		$deal_cates = array();	
		
		$url = url_pack("index");
		$deal_cates[] = array('id'=>0,'name'=>$GLOBALS['lang']['ALL'],'current'=>$deal_cate_id==0?1:0,'url'=>$url);
			
		foreach($deal_cates_db as $k=>$v)
		{		
			if($deal_cate_id==$v['id'])
			$v['current'] = 1;
			
			if($v['uname']!='')
			$v['url'] = APP_ROOT."/?id=".$v['uname'];
			else
			$v['url'] = APP_ROOT."/?id=".$v['id'];
			$deal_cates[] = $v;
		}
	
		$GLOBALS['tmpl']->assign("deal_cate_list",$deal_cates);
	}

	//获取当前页的团购商品
	$deal = get_deal(0,$deal_cate_id);
	if($city_name)
	$GLOBALS['tmpl']->assign("page_title", app_conf("SHOP_TITLE")." - ".$deal_city['name'].$GLOBALS['lang']['SITE']);
	else
	$GLOBALS['tmpl']->assign("page_title", app_conf("SHOP_TITLE"));
	
	$GLOBALS['tmpl']->assign("hide_end_title",true);
	
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
	$tmpl->display("deal.html");
	else
	$tmpl->display("no_deal.html");
}// 单团展示
else //多团风格
{
	
	$city_id = intval($GLOBALS['deal_city']['id']); 
	$GLOBALS['tmpl']->assign("is_index",1);
	$area_id=intval($_REQUEST['area_id']);//区域id
	$area_uname = addslashes(trim($_REQUEST['area_id']));
	if($area_id==0&&$area_uname!='')
	{
		$area_id = $GLOBALS['db']->getOneCached("select id from ".DB_PREFIX."deal_city_area where uname = '".$area_uname."'"); 
	}
	
	if($area_id){
		$area_pid = $GLOBALS['db']->getOne("select pid from ".DB_PREFIX."deal_city_area where id = ".$area_id);
		$barea_id = $area_pid == 0?$area_id:$area_pid; //大区域ID
	}
	$GLOBALS['tmpl']->assign("barea_id",$barea_id);
	$GLOBALS['tmpl']->assign("area_id",$area_id);
	
	$deal_cate_id = intval($_REQUEST['id']);//分类
	$uname = addslashes(trim($_REQUEST['id']));	
	if($deal_cate_id==0&&$uname!='')
	{
		$deal_cate_id = $GLOBALS['db']->getOneCached("select id from ".DB_PREFIX."deal_cate where uname = '".$uname."'"); 
	}
	if($deal_cate_id)
	{
		es_cookie::set("deal_cate_id",$deal_cate_id);
	}
	else 
	{
		es_cookie::set("deal_cate_id",0);
	}
	if($deal_cate_id)
	{
		$pid = $GLOBALS['db']->getOne("select pid from ".DB_PREFIX."deal_cate where id = ".$deal_cate_id);
		$bdeal_cate_id = $pid == 0?$deal_cate_id:$pid; //大分类ID
	}
	$GLOBALS['tmpl']->assign("deal_cate_id",$deal_cate_id);
	$GLOBALS['tmpl']->assign("bdeal_cate_id",$bdeal_cate_id);
	//开始输出分类
	$bcate_list = $GLOBALS['cache']->get("bcate_list_index".$city_id.$bdeal_cate_id.$area_id);
	
	if($bcate_list===false)
	{
		$bcate_list = $GLOBALS['db']->getAll("select id,name,uname from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 and pid = 0 order by sort desc");
		
		foreach($bcate_list as $k=>$v)
		{			
				$cate_deal_list_rs = get_deal_list(1,$v['id'],$city_id, $type=array(DEAL_ONLINE,DEAL_NOTICE), $where='buy_type<>1',$orderby = '',$area_id);	
				$bcate_list[$k]['count'] = $cate_deal_list_rs['count'];
				
				if($v['uname']!='')
				$bcate_list[$k]['url'] = APP_ROOT."/index.php?id=".$v['uname']."&area_id=".$area_id;
				else
				$bcate_list[$k]['url'] = APP_ROOT."/index.php?id=".$v['id']."&area_id=".$area_id;
				$scate_list = $GLOBALS['db']->getAll("select id,name,uname from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 and pid = ".$v['id']." order by sort desc");
				
				$bcate_list[$k]['scate'] = $scate_list;
				if(!empty($scate_list))
				{
					foreach($scate_list as $kk=>$vv)
					{
						if($vv['uname']!='')
							$scate_list[$kk]['url'] = APP_ROOT."/index.php?id=".$vv['uname']."&area_id=".$area_id;
						else
							$scate_list[$kk]['url'] = APP_ROOT."/index.php?id=".$vv['id']."&area_id=".$area_id;
							
						$scate_deal_list_rs = get_deal_list(1,$vv['id'],$city_id, $type=array(DEAL_ONLINE,DEAL_NOTICE), $where='buy_type<>1',$orderby = '',$area_id);	
						$scate_list[$kk]['count'] = $scate_deal_list_rs['count'];
					}
					$bcate_list[$k]['scate'] = $scate_list;
				}		
		}
		$GLOBALS['cache']->set("bcate_list_index".$city_id.$bdeal_cate_id.$area_id,$bcate_list);
	}
	$GLOBALS['tmpl']->assign("bcate_list",$bcate_list);
	
  	//大区域
	$barea_list = get_deal_area("barea_list_index",$city_id,$deal_cate_id,$show_count = true,$exe=" and pid=0 ");
	$GLOBALS['tmpl']->assign("barea_list",$barea_list);
	//小区域
	if(($barea_id != $area_pid&&$area_pid == 0) || ($barea_id == $area_pid&&$area_pid!=0))
	{
		$subarea_list = get_deal_area("sarea_list_index",$city_id,$deal_cate_id,$show_count = true,$exe=" and pid=".$barea_id." ");	
		$GLOBALS['tmpl']->assign("subarea_list",$subarea_list);
	}
	//end区域
	
	
		
	$sort_field = es_cookie::get("sort_field_idx")?es_cookie::get("sort_field_idx"):"sort";
	$sort_type = es_cookie::get("sort_type_idx")?es_cookie::get("sort_type_idx"):"desc";
	$GLOBALS['tmpl']->assign('sort_field',$sort_field);
	$GLOBALS['tmpl']->assign('sort_type',$sort_type);
		
	if($city_name)
	$GLOBALS['tmpl']->assign("page_title", app_conf("SHOP_TITLE")." - ".$deal_city['name'].$GLOBALS['lang']['SITE']);
	else
	$GLOBALS['tmpl']->assign("page_title", app_conf("SHOP_TITLE"));
	
	$GLOBALS['tmpl']->assign("hide_end_title",true);
	
	require_once './app/Lib/side.php';
	
	$page = intval($_REQUEST['p']);
	if($page==0)
	$page = 1;
	$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
	$where = " buy_type<>1 ";
	if(es_cookie::get('current_price_bt'))
	{
		$where.= es_cookie::get('current_price_bt');
	}
	
	$result = get_deal_list($limit,es_cookie::get("deal_cate_id"),$city_id=0,$type=array(DEAL_ONLINE,DEAL_NOTICE),$where,$sort_field." ".$sort_type,$area_id);
	$deal_list = $result['list'];

	$page = new Page($result['count'],app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象 		
	$p  =  $page->show();
	$GLOBALS['tmpl']->assign('pages',$p);
	$GLOBALS['tmpl']->assign("deal_list",$deal_list);
	
	$tmpl->display("deal_multi.html");
}
?>