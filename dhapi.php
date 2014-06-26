<?php 
require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/deal.php';
require './app/Lib/message.php';
require './app/Lib/side.php';

		$directory = APP_ROOT_PATH."tuan/";
		
		$read_api = true;
		$dir = @opendir($directory);
	    $apis     = array();
	
	    while (false !== ($file = @readdir($dir)))
	    {
	        if (preg_match("/^.*?\.php$/", $file))
	        {
	            $tmp = require_once($directory .$file);
	            if($tmp)
	            {
	            	$apis[] = $tmp;
	            }
	        }
	    }
	    @closedir($dir);
	    unset($read_api);
	
	
	$contents_html = '<table>';
	foreach($apis as $k=>$v)
	{
		foreach($v['info'] as $kk=>$vv)
		{
			$contents_html.="<tr><td style='padding:10px 25px 10px 5px;'>";
			$contents_html.= $vv['name'].":</td><td style='padding:10px 5px 10px 5px;'><input type='text' style='width:350px;' class='f-input' value='".get_domain().APP_ROOT."/tuan/".$vv['url']."' /></td>";
			$contents_html.="</tr>";
		}
	}
	$contents_html .= '</table>';
	
	$GLOBALS['tmpl']->assign("page_title", $GLOBALS['lang']['API_LIST']);
	$GLOBALS['tmpl']->assign("page_keyword",$GLOBALS['lang']['API_LIST']);
	$GLOBALS['tmpl']->assign("page_description",$GLOBALS['lang']['API_LIST']);


	$article['title'] = $GLOBALS['lang']['API_LIST'];
	$article['content'] = $contents_html;
	$GLOBALS['tmpl']->assign("article",$article);
	$GLOBALS['tmpl']->display("article.html");
	
?>