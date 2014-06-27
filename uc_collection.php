<?php

require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/uc.php';
require './app/Lib/message.php';
if($_REQUEST['act']=='index')
{
    $page = intval($_REQUEST['p']);
    if($page==0)
        $page = 1;
    $limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");

    $result = get_collection_list($limit,$user_info['id']);
    //print_r($result['list']);exit;
    $GLOBALS['tmpl']->assign("list",$result['list']);
    $page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象
    $p  =  $page->show();
    $GLOBALS['tmpl']->assign('pages',$p);


    $GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_COLLECTION']);
    $GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_collection_index.html");
    $GLOBALS['tmpl']->display("uc.html");
}

elseif($_REQUEST['act']=='del')
{
    $id = intval($_REQUEST['id']);
    if($GLOBALS['db']->query("delete from ".DB_PREFIX."collection where id = ".$id." and user_id = ".$user_info['id']))
    {
        showSuccess($GLOBALS['lang']['CANCEL_COLLECTION_SUCCESS']);
    }
    else
    {
        showErr($GLOBALS['lang']['CANCEL_COLLECTION_FAILED']);
    }

}

?>