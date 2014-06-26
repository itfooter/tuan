<?php
	require_once APP_ROOT_PATH."app/Lib/page.php";
	if($GLOBALS['user_info'])
	{
		$c_user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($user_info['id']));
		$c_user_info['user_group'] = $GLOBALS['db']->getOneCached("select name from ".DB_PREFIX."user_group where id = ".intval($user_info['group_id']));
		$c_user_info['referral_total'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where pid = ".intval($user_info['id']));
		$GLOBALS['tmpl']->assign("user_info",$c_user_info);
	}
	else
	{
		app_redirect(url_pack("user#login"));
	}
	
	//查询会员日志
	function get_user_log($limit,$user_id)
	{
		$user_id = intval($user_id);
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_log where user_id = ".$user_id." order by log_time desc limit ".$limit);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_log where user_id = ".$user_id);
		return array("list"=>$list,'count'=>$count);
	}
	
	//查询会员充值订单
	function get_user_incharge($limit,$user_id)
	{
		$user_id = intval($user_id);
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order where user_id = ".$user_id." and type = 1 and is_delete = 0 order by create_time desc limit ".$limit);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order where user_id = ".$user_id." and type = 1 and is_delete = 0");
		foreach($list as $k=>$v)
		{
			$list[$k]['payment_notice'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where order_id = ".$v['id']);
			$list[$k]['payment'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$v['payment_id']);
		}
		return array("list"=>$list,'count'=>$count);
	}
	
	//查询会员的团购券
	function get_user_coupon($limit,$user_id,$status=0)
	{
		$user_id = intval($user_id);
		$ext_condition = '';
		if($status==1)
		{
			$ext_condition = " and confirm_time = 0 ";
		}
		if($status==2)
		{
			$ext_condition = " and confirm_time <> 0 ";
		}
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_coupon where user_id = ".$user_id." and is_delete = 0 and is_valid = 1 ".$ext_condition." order by order_id desc limit ".$limit);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon where user_id = ".$user_id." and is_delete = 0 and is_valid = 1 ".$ext_condition);
		foreach($list as $k=>$v)
		{
			if($GLOBALS['db']->getOne("select forbid_sms from ".DB_PREFIX."deal where id = ".$v['deal_id'])==1)
			{
				//禁止发券时，将已发数改为上限
				$list[$k]['sms_count'] = app_conf("SMS_COUPON_LIMIT");
			}
			$list[$k]['deal_item'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$v['order_deal_id']);
		}
		return array("list"=>$list,'count'=>$count);		
	}
	
	
	//查询会员订单
	function get_user_order($limit,$user_id)
	{
		$user_id = intval($user_id);
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order where user_id = ".$user_id." and type = 0 and is_delete = 0 order by create_time desc limit ".$limit);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order where user_id = ".$user_id." and type = 0 and is_delete = 0");
		foreach($list as $k=>$v)
		{
			$list[$k]['payment_notice'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where order_id = ".$v['id']);
		}
		return array("list"=>$list,'count'=>$count);
	}
	
	//查询会员抽奖
	function get_user_lottery($limit,$user_id)
	{
		$user_id = intval($user_id);
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."lottery where user_id = ".$user_id." order by create_time desc limit ".$limit);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."lottery where user_id = ".$user_id);
		foreach($list as $k=>$v)
		{
			$list[$k]['deal_name'] = $GLOBALS['db']->getOneCached("select name from ".DB_PREFIX."deal where id = ".$v['deal_id']);
			$list[$k]['deal_sub_name'] = $GLOBALS['db']->getOneCached("select sub_name from ".DB_PREFIX."deal where id = ".$v['deal_id']);
			if($v['buyer_id']==0)
			{
				$buyer = $GLOBALS['db']->getOneCached("select user_name from ".DB_PREFIX."user where id = ".$v['user_id']);
			}
			else
			{
				$buyer = $GLOBALS['db']->getOneCached("select user_name from ".DB_PREFIX."user where id = ".$v['buyer_id']);
			}
			$list[$k]['buyer'] = $buyer;
		}
		return array("list"=>$list,'count'=>$count);
	}
	
	//查询会员邀请及返利列表
	function get_invite_list($limit,$user_id)
	{
		$user_id = intval($user_id);
		$sql = "select u.user_name as i_user_name,u.referral_count as i_referral_count,u.create_time as i_reg_time,o.order_sn as i_order_sn,r.create_time as i_referral_time, r.pay_time as i_pay_time,r.money as i_money,r.score as i_score from ".DB_PREFIX."user as u left join ".DB_PREFIX."referrals as r on u.id = r.rel_user_id and u.pid = r.user_id left join ".DB_PREFIX."deal_order as o on r.order_id = o.id where u.pid = ".$user_id." limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."user where pid = ".$user_id;
		$list = $GLOBALS['db']->getAll($sql);
		$count = $GLOBALS['db']->getOne($sql_count);
		return array("list"=>$list,'count'=>$count);
	}
	
	//查询代金券列表
	function get_voucher_list($limit,$user_id)
	{
		$user_id = intval($user_id);
		$sql = "select * from ".DB_PREFIX."ecv as e left join ".DB_PREFIX."ecv_type as et on e.ecv_type_id = et.id where e.user_id = ".$user_id." order by e.id desc limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."ecv where user_id = ".$user_id;
		
		$list = $GLOBALS['db']->getAll($sql);
		$count = $GLOBALS['db']->getOne($sql_count);
		return array("list"=>$list,'count'=>$count);
	}
	
	//查询可兑换代金券列表
	function get_exchange_voucher_list($limit)
	{
		$sql = "select * from ".DB_PREFIX."ecv_type where send_type = 1 order by id desc limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."ecv_type where send_type = 1";
		
		$list = $GLOBALS['db']->getAll($sql);
		$count = $GLOBALS['db']->getOne($sql_count);
		return array("list"=>$list,'count'=>$count);
	}
        
?>