<?php 
require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/message.php';
require './app/Lib/page.php';
require './app/Lib/side.php';


$id = intval($_REQUEST['id']);
if($id>0)
{
	$cate_item = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."article_cate where id = ".$id." and is_effect = 1 and is_delete = 0");
	if(!$cate_item)
	{
		app_redirect(APP_ROOT."/");
	}
}
$cate_id = intval($cate_item['id']);


	//分页
	$page = intval($_REQUEST['p']);
	if($page==0)
	$page = 1;
	$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");		
	$result = get_article_list($limit,$cate_id,'','');
	
			
	$GLOBALS['tmpl']->assign("list",$result['list']);
	$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
	$p  =  $page->show();
	$GLOBALS['tmpl']->assign('pages',$p);
			
	if(!$cate_item)
	$cate_item['title'] = $GLOBALS['lang']['SITE_NOTICE_LIST'];
	
	$GLOBALS['tmpl']->assign("page_title", $cate_item['seo_title']!=''?$cate_item['seo_title']:$cate_item['title']);
	$GLOBALS['tmpl']->assign("page_keyword",$cate_item['seo_keyword']!=''?$cate_item['seo_keyword']:$cate_item['title']);
	$GLOBALS['tmpl']->assign("page_description",$cate_item['seo_description']!=''?$cate_item['seo_description']:$cate_item['title']);
	
	$GLOBALS['tmpl']->display("article_list.html");
?>