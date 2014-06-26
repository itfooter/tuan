<?php 
require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/deal.php';
require './app/Lib/message.php';
require './app/Lib/side.php';

if($_REQUEST['act']=='go')
{
	$url = trim(addslashes($_REQUEST['url']));
	$link_item = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."link where (url = '".$url."' or url = 'http://".$url."') and is_effect = 1");
	if($link_item)
	{
		if(check_ipop_limit(get_client_ip(),"Link",10,$link_item['id']))
		$GLOBALS['db']->query("update ".DB_PREFIX."link set count = count + 1 where id = ".$link_item['id']);
	}
}
else
{

	//开始输出友情链接
	$p_link_group = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."link_group where is_effect = 1 order by sort desc");
	foreach($p_link_group as $k=>$v)
	{
		$g_links = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."link where is_effect = 1 and group_id = ".$v['id']." order by sort desc");
		if($g_links)
		{
			foreach($g_links as $kk=>$vv)
			{
				if(substr($vv['url'],0,7)=='http://')
				{
					$g_links[$kk]['url'] = str_replace("http://","",$vv['url']);
				}
			}
			$p_link_group[$k]['links'] = $g_links;
		}
		else
		unset($p_link_group[$k]);
	}
	$GLOBALS['tmpl']->assign("p_link_data",$p_link_group);
	
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['FRIEND_LINK']);

	
	$GLOBALS['tmpl']->display("link.html");
}

?>