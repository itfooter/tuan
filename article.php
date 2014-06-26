<?php 
require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/deal.php';
require './app/Lib/message.php';
require './app/Lib/side.php';

$id = intval($_REQUEST['id']);
$uname = addslashes(trim($_REQUEST['id']));
if($id==0&&$uname!='')
$article = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."article where is_effect = 1 and is_delete = 0 and uname='".$uname."'");
else
$article = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."article where is_effect = 1 and is_delete = 0 and id=".$id);

//图片轮播
if(app_conf("BROADCAST")){
$broadcast_all=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."broadcast where code='article'  and is_effect = 1 order by id desc ");
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

if($article)
{
	if(check_ipop_limit(get_client_ip(),"article",60,$article['id']))
	{
		//每一分钟访问更新一次点击数
		$GLOBALS['db']->query("update ".DB_PREFIX."article set click_count = click_count + 1 where id =".$article['id']);
	}
	if($article['rel_url']!='')
	{
		if(!preg_match ("/http:\/\//i", $article['rel_url']))
		{
			if(substr($article['rel_url'],0,2)=='u:')
			{
				app_redirect(url_pack(substr($article['rel_url'],2)));
			}
			else
			app_redirect(APP_ROOT."/".$article['rel_url']);
		}
		else
		app_redirect($article['rel_url']);
	}
	
	$relate_articles = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."article where cate_id = ".intval($article['cate_id'])." and is_delete = 0 and is_effect = 1 order by sort desc limit 5");
	foreach($relate_articles as $k=>$v)
	{
		if($article['id']==$v['id'])
		{
			$relate_articles[$k]['current'] = 1;
		}
		$relate_articles[$k]['url'] = url_pack("article",$v['id'],$v['uname']);
	}
	$GLOBALS['tmpl']->assign("articles",$relate_articles);
	
	
	$GLOBALS['tmpl']->assign("page_title", $article['seo_title']!=''?$article['seo_title']:$article['title']);
	$GLOBALS['tmpl']->assign("page_keyword",$article['seo_keyword']!=''?$article['seo_keyword']:$article['title']);
	$GLOBALS['tmpl']->assign("page_description",$article['seo_description']!=''?$article['seo_description']:$article['title']);


	$GLOBALS['tmpl']->assign("article",$article);
	$GLOBALS['tmpl']->display("article.html");
}
else
{
	showErr($GLOBALS['lang']['NO_ARTICLE']);
}
?>