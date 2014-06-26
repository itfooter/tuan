<?php
function get_side_deal($deal_id)
{	
	$city = get_current_deal_city();
	$city_id = $city['id'];
	$side_deal_list = get_deal_list(app_conf("SIDE_DEAL_COUNT"),intval($GLOBALS['deal_cate_id']),$city_id,array(DEAL_ONLINE),"id<>".$deal_id." and buy_type <> 1 ");
	return $side_deal_list['list'];
}
function get_side_deal_message($deal_id)
{	

	$where = "rel_table = 'deal' and rel_id=".$deal_id;
	if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
	{
		$where.= " and user_id = ".intval($GLOBALS['user_info']['id']);
	}
	else 
	{
		$message_type = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."message_type where type_name = 'deal'"); 
		if($message_type['is_effect']==0)
		{
			$where.= " and user_id = ".intval($GLOBALS['user_info']['id']);
		}
	}
	$side_message = get_message_list(app_conf("SIDE_MESSAGE_COUNT"),$where);
	foreach($side_message['list'] as $k=>$v)
	{
		$side_message['list'][$k]['url'] = url_pack("message#deal",$v['rel_id'])."&is_buy=".$v['is_buy']."#consult-entry-".$v['id'];
 		if($v['is_buy']==0)
 		$side_message['list'][$k]['content'] = "[".$GLOBALS['lang']['BEFORE_BUY']."]".$v['content'];
	}
	return $side_message;
}

function get_side_message()
{
	
	$where = "rel_table = 'faq'";
	
	if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
	{
		$where.= " and user_id = ".intval($GLOBALS['user_info']['id']);
	}
	else 
	{
		$message_type = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."message_type where type_name = 'faq'"); 
		if($message_type['is_effect']==0)
		{
			$where.= " and user_id = ".intval($GLOBALS['user_info']['id']);
		}
	}

	$side_message = get_message_list(app_conf("SIDE_MESSAGE_COUNT"),$where);
	foreach($side_message['list'] as $k=>$v)
	{
		$side_message['list'][$k]['url'] = url_pack("message#faq")."#consult-entry-".$v['id'];
	}
	return $side_message;
}

function get_side_vote()
{
	$now = get_gmtime();
	$vote = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."vote where is_effect = 1 and begin_time < ".$now." and (end_time = 0 or end_time > ".$now.") order by sort desc limit 1");
	return $vote;
}


//获取公告
function get_site_notice()
{
	$result = $GLOBALS['cache']->get("SITE_NOTICE_LIST");
	if($result===false)
	{
		$notice_cate_ids = $GLOBALS['db']->getAll("select id from ".DB_PREFIX."article_cate where type_id = 2 and is_effect = 1");
		$ids = array(0);
		foreach($notice_cate_ids as $k=>$v)
		{
			array_push($ids,$v['id']);
		}
		$result = get_article_list(5,0,'','');
		$GLOBALS['cache']->set("SITE_NOTICE_LIST",$result);
	}
	return $result['list'];
}



if($deal)
{
	$side_deal_list = get_side_deal(intval($deal['id']));
	$GLOBALS['tmpl']->assign("side_deal_list",$side_deal_list);
}

//输出返利
if(app_conf("INVITE_REFERRALS_TYPE") == 0)
$referral_tip = sprintf($GLOBALS['lang']['INVITE_REFERRALS_TIP'],format_price(app_conf("INVITE_REFERRALS")));
else
$referral_tip = sprintf($GLOBALS['lang']['INVITE_REFERRALS_TIP'],format_score(app_conf("INVITE_REFERRALS")));
$GLOBALS['tmpl']->assign("invite_referrals_tip",$referral_tip);

//输出在线客服与时间
$qq = explode("|",app_conf("ONLINE_QQ"));
$msn = explode("|",app_conf("ONLINE_MSN"));
$GLOBALS['tmpl']->assign("online_qq",$qq);
$GLOBALS['tmpl']->assign("online_msn",$msn);


//输出团购讨论
$side_deal_message = get_side_deal_message(intval($deal['id']));
$GLOBALS['tmpl']->assign("side_deal_message",$side_deal_message);

//输出问题答疑
$side_message = get_side_message();
$GLOBALS['tmpl']->assign("side_message",$side_message);

//商务合作
$deal_cooperation_tip = sprintf($GLOBALS['lang']['DEAL_COOPERATION_TIP'],url_pack("message#seller"));
$GLOBALS['tmpl']->assign("deal_cooperation_tip",$deal_cooperation_tip);

//输出调查
$vote = get_side_vote();
$GLOBALS['tmpl']->assign("vote",$vote);

//输出公告
$GLOBALS['tmpl']->assign("site_notice_list",get_site_notice());

?>