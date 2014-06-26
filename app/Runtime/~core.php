<?php
//用于授权的初始化源码
function init_checker()
{	
		$domain_array = array(
 			base64_encode(base64_encode('*.tuan.com')),
			base64_encode(base64_encode('localhost')),
			base64_encode(base64_encode('127.0.0.1')),
		);
		$str = base64_encode(base64_encode(serialize($domain_array))."|".serialize($domain_array));

		$arr = explode("|",base64_decode($str));		
		$arr = unserialize($arr[1]);
		foreach($arr as $k=>$v)
		{
			$arr[$k] = base64_decode(base64_decode($v));
		}	
		$host = $_SERVER['HTTP_HOST'];
		$host = explode(":",$host);
		$host = $host[0];
		$passed = false;
		foreach($arr as $k=>$v)
		{
			if(substr($v,0,2)=='*.')
			{
				$preg_str = substr($v,2);
				if(preg_match("/".$preg_str."$/",$host)>0)
				{
					$passed = true;
					break;
				}
			}
		}
		if(!$passed)
		{
			if(!in_array($host,$arr))
		    {
		    	return false;
		    }
		}
		
		return true;
}
$checker = init_checker();
if(!$checker)die("domain not authorized");



//引入数据库的系统配置及定义配置函数
$sys_config = require APP_ROOT_PATH.'system/config.php';
function app_conf($name)
{
	return stripslashes($GLOBALS['sys_config'][$name]);
}
//end 引入数据库的系统配置及定义配置函数

//引入时区配置及定义时间函数
if(function_exists('date_default_timezone_set'))
	date_default_timezone_set(app_conf('DEFAULT_TIMEZONE'));
//end 引入时区配置及定义时间函数

//定义缓存
require APP_ROOT_PATH.'system/cache/Cache.php';
$cache = CacheService::getInstance("File");
//end 定义缓存

//定义DB
require APP_ROOT_PATH.'system/db/db.php';
define('DB_PREFIX', app_conf('DB_PREFIX')); 
if(!file_exists(APP_ROOT_PATH.'app/Runtime/db_caches/'))
	mkdir(APP_ROOT_PATH.'app/Runtime/db_caches/',0777);
$pconnect = false;
$db = new mysql_db(app_conf('DB_HOST').":".app_conf('DB_PORT'), app_conf('DB_USER'),app_conf('DB_PWD'),app_conf('DB_NAME'),'utf8',$pconnect);
//end 定义DB


//定义模板引擎
require  APP_ROOT_PATH.'system/template/template.php';
if(!file_exists(APP_ROOT_PATH.'app/Runtime/tpl_caches/'))
	mkdir(APP_ROOT_PATH.'app/Runtime/tpl_caches/',0777);
	
if(!file_exists(APP_ROOT_PATH.'app/Runtime/tpl_compiled/'))
	mkdir(APP_ROOT_PATH.'app/Runtime/tpl_compiled/',0777);
$tmpl = new AppTemplate;
$tmpl->template_dir   = APP_ROOT_PATH . 'app/Tpl/' . app_conf("TEMPLATE");
$tmpl->cache_dir      = APP_ROOT_PATH . 'app/Runtime/tpl_caches';
$tmpl->compile_dir    = APP_ROOT_PATH . 'app/Runtime/tpl_compiled';
//end 定义模板引擎

$_REQUEST = array_merge($_GET,$_POST);
filter_request($_REQUEST);
require APP_ROOT_PATH.'system/utils/es_cookie.php';

$lang = require APP_ROOT_PATH.'/app/Lang/'.app_conf("SHOP_LANG").'/lang.php';
?>