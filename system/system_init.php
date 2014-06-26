<?php 
define("SHOW_DEBUG",False);
@session_start();
@set_magic_quotes_runtime (0);
define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc()?True:False);
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
if(!defined('IS_CGI'))
define('IS_CGI',substr(PHP_SAPI, 0,3)=='cgi' ? 1 : 0 );
if(!defined('_PHP_FILE_')) {
        if(IS_CGI) {
            //CGI/FASTCGI模式下
            $_temp  = explode('.php',$_SERVER["PHP_SELF"]);
            define('_PHP_FILE_',  rtrim(str_replace($_SERVER["HTTP_HOST"],'',$_temp[0].'.php'),'/'));
        }else {
            define('_PHP_FILE_',  rtrim($_SERVER["SCRIPT_NAME"],'/'));
        }
}
if(!defined('APP_ROOT')) {
        // 网站URL根目录
        $_root = dirname(_PHP_FILE_);
        $_root = (($_root=='/' || $_root=='\\')?'':$_root);
        $_root = str_replace("/system","",$_root);
        define('APP_ROOT', $_root  );
}
if(!defined('APP_ROOT_PATH')) 
define('APP_ROOT_PATH', str_replace('system/system_init.php', '', str_replace('\\', '/', __FILE__)));

//定义$_SERVER['REQUEST_URI']兼容性
if (!isset($_SERVER['REQUEST_URI']))
{
		if (isset($_SERVER['argv']))
		{
			$uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];
		}
		else
		{
			$uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
		}
		$_SERVER['REQUEST_URI'] = $uri;
}
filter_request($_GET);
filter_request($_POST);
//关于安装的检测
if(!file_exists(APP_ROOT_PATH."public/install.lock"))
{
	app_redirect(APP_ROOT."/install/");
}

if(file_exists(APP_ROOT_PATH."app/Runtime/~core.php"))
{
	require APP_ROOT_PATH."app/Runtime/~core.php";
}
else
{
	$key_file = APP_ROOT_PATH."license";
	if( !file_exists($key_file) )
	{
		die("domain not authorized");
	}
	$str = @file_get_contents($key_file);
	require_once APP_ROOT_PATH."system/utils/es_key.php";
	$es_key = new es_key(64);
	$str =  $es_key->decrypt($str,"EASETHINK");
	@file_put_contents(APP_ROOT_PATH."app/Runtime/~core.php",$str);
	require APP_ROOT_PATH."app/Runtime/~core.php";
}
?>