<?php 

require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/deal.php';
require './app/Lib/message.php';
require './app/Lib/page.php';

//用于处理团购搜索的处理程序
$se_name = trim(addslashes($_POST['se_name']));
$se_begin = trim(addslashes($_POST['se_begin']));
$se_end = trim(addslashes($_POST['se_end']));

$se_begin = to_timespan($se_begin,'Y-m-d');
$se_end = to_timespan($se_end,'Y-m-d');
$se_end = $se_end!=0?($se_end+24*3600-1):$se_end;

$search['se_name'] = $se_name;
$search['se_begin'] = $se_begin;
$search['se_end'] = $se_end;

$se_module =  trim(addslashes($_POST['se_module']));
$se_action = htmlspecialchars(strip_tags(trim($_POST['se_action'])));
$se_id =  intval(addslashes($_POST['se_id']));


$search_code = urlencode(base64_encode(serialize($search)));
$url = APP_ROOT."/".$se_module.".php?act=".$se_action."&id=".$se_id."&search=".$search_code;
app_redirect($url);
?>