<?php 
require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/message.php';
require './app/Lib/side.php';

if($_REQUEST['act']=='dovote')
{
	$ok = false;
      foreach($_REQUEST['name'] as $k=>$v){
          $_REQUEST['name'][$k]= array_map('mysql_real_escape_string',$v);
      }
     
	foreach($_REQUEST['name'] as $vote_ask_id=>$names)
	{			
			foreach($names as $kk=>$name)
			{
				if($name!='')
				{
					$ok = true;
				}
			}
	}
	if(!$ok)
	{
		showErr($GLOBALS['lang']['YOU_DONT_CHOICE']);
	}
	$vote_id = intval($_REQUEST['vote_id']);
	if(check_ipop_limit(get_client_ip(),"vote",3600,$vote_id))
	{
		foreach($_REQUEST['name'] as $vote_ask_id=>$names)
		{
			
			foreach($names as $kk=>$name)
			{
				$name = mysql_real_escape_string(addslashes(trim($name)));
                                $vote_ask_id=intval($vote_ask_id);
				
				$result = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."vote_result where name = '".$name."' and vote_id = ".$vote_id." and vote_ask_id = ".$vote_ask_id);
				$is_add = true;
				if($result)
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."vote_result set count = count + 1 where name = '".$name."' and vote_id = ".$vote_id." and vote_ask_id = ".$vote_ask_id);
					if(intval($GLOBALS['db']->affected_rows())!=0)
					{
						$is_add = false;
					}
				}
				
				if($is_add)
				{
					$result = array();
					$result['name'] = $name;
					$result['vote_id'] = $vote_id;
					$result['vote_ask_id'] = $vote_ask_id;
					$result['count'] = 1;
					$GLOBALS['db']->autoExecute(DB_PREFIX."vote_result",$result);
				}
			}
		}
	
		showSuccess($GLOBALS['lang']['VOTE_SUCCESS']);
	}
	else
	{
		showErr($GLOBALS['lang']['YOU_VOTED']);
	}
}
else
{
	if($vote)
	{
		$vote_ask = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."vote_ask where vote_id = ".intval($vote['id'])." order by sort asc");
		
		foreach($vote_ask as $k=>$v)
		{
			$vote_ask[$k]['val_scope'] = explode(",",$v['val_scope']);
		}
		
		
		$tmpl->assign("vote_ask",$vote_ask);
		$tmpl->assign("page_title",$GLOBALS['lang']['VOTE']);
		$tmpl->display("vote.html");
	}
	else
	{
		showErr($GLOBALS['lang']['NO_VOTE'],0,APP_ROOT);
	}
}
?>