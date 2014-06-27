<?php 
require './system/common.php';
require './app/Lib/app_init.php';

// 加载子地区option
if($_REQUEST['act'] == 'load_region')
{
	$pid = intval($_REQUEST['id']);
	$region_html = "<option value ='0'>=".$GLOBALS['lang']['SELECT_PLEASE']."=</option>";
	if($pid != 0)
	{
		$regions = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_region where pid = ".$pid); 

		foreach($regions as $k=>$v)
		{
			$region_html .= "<option value ='".$v['id']."'>".$v['name']."</option>";
		} 
	}
	header("Content-Type:text/html; charset=utf-8");
	echo $region_html;
}

// 加载指定的收货人
if($_REQUEST['act']=='load_consignee')
{
	$consignee_id = intval($_REQUEST['id']);
	if($consignee_id>0)
	{
		$consignee_info = $GLOBALS['cache']->get("CONSIGNEE_INFO_".$consignee_id);
		if($consignee_info === false)
		{
			$consignee_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_consignee where id = ".$consignee_id);
			$GLOBALS['cache']->set("CONSIGNEE_INFO_".$consignee_id,$consignee_info);
		}
		
		
		$region_lv1 = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_region where pid = 0");  //一级地址
		foreach($region_lv1 as $k=>$v)
		{
			if($v['id'] == $consignee_info['region_lv1'])
			{
				$region_lv1[$k]['selected'] = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv1",$region_lv1);
		
		$region_lv2 = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_region where pid = ".$consignee_info['region_lv1']);  //二级地址
		foreach($region_lv2 as $k=>$v)
		{
			if($v['id'] == $consignee_info['region_lv2'])
			{
				$region_lv2[$k]['selected'] = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);
		
		$region_lv3 = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_region where pid = ".$consignee_info['region_lv2']);  //三级地址
		foreach($region_lv3 as $k=>$v)
		{
			if($v['id'] == $consignee_info['region_lv3'])
			{
				$region_lv3[$k]['selected'] = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv3",$region_lv3);
		
		$region_lv4 = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_region where pid = ".$consignee_info['region_lv3']);  //四级地址
		foreach($region_lv4 as $k=>$v)
		{
			if($v['id'] == $consignee_info['region_lv4'])
			{
				$region_lv4[$k]['selected'] = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("region_lv4",$region_lv4);
		
		$GLOBALS['tmpl']->assign("consignee_info",$consignee_info);
	}
	else
	{
		$region_lv1 = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."delivery_region where pid = 0");  //一级地址
		$GLOBALS['tmpl']->assign("region_lv1",$region_lv1);
	}
	
	$GLOBALS['tmpl']->display("inc/cart/cart_consignee.html");
}

// 加载针对配送地区相对应的配送方式
if($_REQUEST['act']=='load_delivery')
{
	$region_id = intval($_REQUEST['id']);
	$order_id = intval($_REQUEST['order_id']);
	require_once APP_ROOT_PATH."system/libs/cart.php";
	$delivery_list = load_support_delivery($region_id,$order_id);
	$GLOBALS['tmpl']->assign("delivery_list",$delivery_list);
	$GLOBALS['tmpl']->display("inc/cart/cart_delivery.html");
}

// ajax动态载入购买总计
if($_REQUEST['act']=='count_buy_total')
{
	require_once APP_ROOT_PATH."system/libs/cart.php";
	$region_id = intval($_REQUEST['region_id']); //配送地区
	$delivery_id =  intval($_REQUEST['delivery_id']); //配送方式
	$account_money =  floatval($_REQUEST['account_money']); //余额
	$ecvsn = $_REQUEST['ecvsn']?trim(addslashes($_REQUEST['ecvsn'])):'';
	$ecvpassword = $_REQUEST['ecvpassword']?trim(addslashes($_REQUEST['ecvpassword'])):'';
	$payment = intval($_REQUEST['payment']);
	$all_account_money = intval($_REQUEST['all_account_money']);
	$bank_id = addslashes(trim($_REQUEST['bank_id']));
	
	$user_id = intval($GLOBALS['user_info']['id']);
	$session_id = session_id();
	$goods_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cart where session_id='".$session_id."' and user_id=".$user_id);
	
	$result = count_buy_total($region_id,$delivery_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword,$goods_list,0,0,$bank_id);
	

	$GLOBALS['tmpl']->assign("result",$result);
	$html = $GLOBALS['tmpl']->fetch("inc/cart/cart_total.html");
	$data = $result;
	$data['html'] = $html;
	
	ajax_return($data);
	
}


// ajax动态载入订单购买总计
if($_REQUEST['act']=='count_order_total')
{
	require_once APP_ROOT_PATH."system/libs/cart.php";
	$order_id = intval($_REQUEST['id']);
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
	
	
	$region_id = intval($_REQUEST['region_id']); //配送地区
	$delivery_id =  intval($_REQUEST['delivery_id']); //配送方式
	$account_money =  floatval($_REQUEST['account_money']); //余额

	$ecvsn = $_REQUEST['ecvsn']?trim(addslashes($_REQUEST['ecvsn'])):'';
	$ecvpassword = $_REQUEST['ecvpassword']?trim(addslashes($_REQUEST['ecvpassword'])):'';
	
	$payment = intval($_REQUEST['payment']);
	$all_account_money = intval($_REQUEST['all_account_money']);
	$bank_id = addslashes(trim($_REQUEST['bank_id']));
	
	$goods_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
	
	$result = count_buy_total($region_id,$delivery_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword,$goods_list,$order_info['account_money'],$order_info['ecv_money'],$bank_id);
		
	$GLOBALS['tmpl']->assign("result",$result);
	$html = $GLOBALS['tmpl']->fetch("inc/cart/cart_total.html");
	$data = $result;
	$data['html'] = $html;
	
	ajax_return($data);
	
}

if($_REQUEST['act']=='get_supplier_location')
{
	$id = intval($_REQUEST['id']);
	$supplier_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."supplier_location where id = ".$id);
        $url_b="&wd=".$supplier_info['api_address']."&c=131&src=0&wd2=&sug=0";
	$url_b=urlencode($url_b);						
	$supplier_info['map']="http://map.baidu.com/?newmap=1&l=18&c=".$supplier_info['xpoint'].",".$supplier_info['ypoint']."&s=s".$url_b."&sc=0";
	$GLOBALS['tmpl']->assign("supplier_address_info",$supplier_info);
	$html = $GLOBALS['tmpl']->fetch("inc/sp_location.html");
	header("Content-Type:text/html; charset=utf-8");
	echo $html;
}

if($_REQUEST['act']=='verify_ecv')
{
	$ecvsn = trim(addslashes($_REQUEST['ecvsn']));
	$ecvpassword = trim(addslashes($_REQUEST['ecvpassword']));
	$user_id = intval($GLOBALS['user_info']['id']);
	$now = get_gmtime();
	$ecv_sql = "select e.*,et.name from ".DB_PREFIX."ecv as e left join ".
				DB_PREFIX."ecv_type as et on e.ecv_type_id = et.id where e.sn = '".
				$ecvsn."' and e.password = '".
				$ecvpassword."' and ((e.begin_time <> 0 and e.begin_time < ".$now.") or e.begin_time = 0) and ".
				"((e.end_time <> 0 and e.end_time > ".$now.") or e.end_time = 0) and ((e.use_limit <> 0 and e.use_limit > e.use_count) or (e.use_limit = 0)) ".
				"and (e.user_id = ".$user_id." or e.user_id = 0)";
	$ecv_data = $GLOBALS['db']->getRow($ecv_sql);
	header("Content-Type:text/html; charset=utf-8");
	if($ecv_data)
	echo "[".$ecv_data['name']."] ".$GLOBALS['lang']['IS_VALID'];
	else
	echo $GLOBALS['lang']['IS_INVALID_ECV'];
}


if($_REQUEST['act']=='check_field')
{
	$field_name = trim(addslashes($_REQUEST['field_name']));
	$field_data = trim(addslashes($_REQUEST['field_data']));
	require_once APP_ROOT_PATH."system/libs/user.php";
	$res = check_user($field_name,$field_data);
	$result = array("status"=>1,"info"=>'');
	if($res['status'])
	{
		ajax_return($result);
	}
	else
	{
		$error = $res['data'];		
		if(!$error['field_show_name'])
		{
				$error['field_show_name'] = $GLOBALS['lang']['USER_TITLE_'.strtoupper($error['field_name'])];
		}
		if($error['error']==EMPTY_ERROR)
		{
			$error_msg = sprintf($GLOBALS['lang']['EMPTY_ERROR_TIP'],$error['field_show_name']);
		}
		if($error['error']==FORMAT_ERROR)
		{
			$error_msg = sprintf($GLOBALS['lang']['FORMAT_ERROR_TIP'],$error['field_show_name']);
		}
		if($error['error']==EXIST_ERROR)
		{
			$error_msg = sprintf($GLOBALS['lang']['EXIST_ERROR_TIP'],$error['field_show_name']);
		}
		$result['status'] = 0;
		$result['info'] = $error_msg;
		ajax_return($result);
	}
}

if($_REQUEST['act'] == 'switch_style')
{
	$type = trim(addslashes($_REQUEST['type']));
	if($type=='grid')
	{
		es_cookie::set("list_type",1); 
	}
	else
	{
		es_cookie::set("list_type",0); 
	}
}

if($_REQUEST['act']=='check_buy')
{
	$id = intval($_REQUEST['id']);
	header("Content-Type:text/html; charset=utf-8");
	$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.deal_id = ".$id." and do.user_id = ".intval($GLOBALS['user_info']['id'])." and do.pay_status = 2");
	echo $rs;
}

if($_REQUEST['act'] == 'set_sort')
{
	$type = trim(addslashes($_REQUEST['type']));
	es_cookie::set("sort_field",$type); 
	if($type!='sort')
	{
		$sort_type = trim(es_cookie::get("sort_type")); 
		if($sort_type&&$sort_type=='desc')
		{
			es_cookie::set("sort_type",'asc'); 
		}
		else
		{
			es_cookie::set("sort_type",'desc'); 
		}		
	}
	else
	{
		es_cookie::set("sort_type",'desc'); 
	}
}

if($_REQUEST['act'] == 'set_sort_idx')
{
	$type = trim(addslashes($_REQUEST['type']));
	es_cookie::set("sort_field_idx",$type); 
	if($type!='sort')
	{
		$sort_type = trim(es_cookie::get("sort_type_idx")); 
		if($sort_type&&$sort_type=='desc')
		{
			es_cookie::set("sort_type_idx",'asc'); 
		}
		else
		{
			es_cookie::set("sort_type_idx",'desc'); 
		}		
	}
	else
	{
		es_cookie::set("sort_type_idx",'desc'); 
	}
}


if($_REQUEST['act'] == 'reopen')
{
	$user_id = intval($GLOBALS['user_info']['id']);
	if($user_id == 0)
	{
		$GLOBALS['tmpl']->assign("ajax",1);
		$data['open_win'] = 1;
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/login_form.html");
		ajax_return($data);
	}
	else
	{
		$deal_id = intval($_REQUEST['id']);		
		if(!check_ipop_limit(get_client_ip(),"reopen",3600,$deal_id))
		{
			$data['open_win'] = 0;
			$data['info'] = $GLOBALS['lang']['REOPEN_SUBMIT_FAST'];
			$data['status'] = 0;
			ajax_return($data);
		}
		else
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."deal set reopen = reopen + 1 where id = ".$deal_id." and time_status = 2");
			$rs = $GLOBALS['db']->affected_rows();
			if($rs == 0)
			{
				$data['open_win'] = 0;
				$data['info'] = $GLOBALS['lang']['REOPEN_SUBMIT_FAILED'];
				$data['status'] = 0;
				ajax_return($data);
			}
			else
			{
				$data['open_win'] = 0;
				$data['status'] = 1;
				$data['info'] = $GLOBALS['lang']['REOPEN_SUBMIT_OK'];
				ajax_return($data);
			}
		}
	}
}

if($_REQUEST['act'] == 'get_verify_code')
{
	if(app_conf("SMS_ON")==0)
	{
		$data['status'] = 0;
		$data['info'] = $GLOBALS['lang']['SMS_OFF'];
		ajax_return($data);		
	}
	$lottery_mobile = addslashes(htmlspecialchars(trim($_REQUEST['lottery_mobile'])));
	$user_id = intval($GLOBALS['user_info']['id']);
	if($user_id == 0)
	{
		$data['status'] = 0;
		$data['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		ajax_return($data);
	}
	if($lottery_mobile == '')
	{
		$data['status'] = 0;
		$data['info'] = $GLOBALS['lang']['LOTTERY_MOBILE_EMPTY'];
		ajax_return($data);
	}
	
	if(!check_mobile($lottery_mobile))
	{
		$data['status'] = 0;
		$data['info'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
		ajax_return($data);
	}
	
	
	//验证手机号的唯一购买
	$lottery_rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."lottery as l left join ".DB_PREFIX."deal_cart as dc on dc.deal_id = l.deal_id where l.user_id <> ".$user_id." and l.mobile = '".$lottery_mobile."'");
	//以上查询是否参与过本期相关的抽奖
	
	//查询是否有用户绑定
	$user= $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where lottery_mobile = '".$lottery_mobile."' and lottery_verify = ''");
	
	if($lottery_rs>0||$user)
	{
		if($user['id'] == intval($user_info['id']))
		{
			$data['status'] = 1;
			$data['info'] = $GLOBALS['lang']['MOBILE_VERIFIED'];
		}
		else
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['MOBILE_USED_LOTTERY'];
		}
		
		ajax_return($data);
	}
	
	
	if(!check_ipop_limit(get_client_ip(),"lottery_verify",300,0))
	{
		$data['status'] = 0;
		$data['info'] = $GLOBALS['lang']['LOTTERY_SEND_FAST'];
		ajax_return($data);
	}
	
	//开始生成手机验证
	$code = rand(1111,9999);
	$GLOBALS['db']->query("update ".DB_PREFIX."user set lottery_verify = '".$code."',lottery_mobile = '".$lottery_mobile."',verify_create_time = '".get_gmtime()."' where id = ".$user_id);
	send_verify_sms($lottery_mobile,$code);
	$data['status'] = 1;
	$data['info'] = $GLOBALS['lang']['LOTTERY_VERIFY_SEND_OK'];
	ajax_return($data);

}
if($_REQUEST['act'] == 'check_deal_buy')
{
	$id = intval($_REQUEST['id']);
	$user_info = $GLOBALS['user_info'];
		if(!$user_info)
		{
			$data['status'] = 0;
			$data['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($data);
		}
		else
		{
			if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.deal_id = ".$id." and do.user_id = ".intval($user_info['id'])." and do.pay_status = 2")==0)
			{
				$data['status'] = 0;
				$data['info'] = ($GLOBALS['lang']['AFTER_BUY_MESSAGE_TIP']);
				ajax_return($data);
			}
			else
			{
				$data['status'] = 1;
				$data['info'] = "";
				ajax_return($data);
			}
		}
}

//增加收藏
if($_REQUEST['act'] == 'collection_add')
{
	$id = intval($_REQUEST['id']);
	$user_info = $GLOBALS['user_info'];
	$data['status'] = 0;
	if(!$user_info)
	{
		$data['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		ajax_return($data);
	}
	else
	{
		if($GLOBALS['db']->getRow("select id from ".DB_PREFIX."deal where id = ".$id))
		{
			//是否已经收藏过了
			$collection = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."collection where deal_id = ".$id." and user_id = ".$user_info['id']);
			if($collection)
			{
				$data['info'] = ($GLOBALS['lang']['ALREADY_COLLECTION']);
			}
			else
			{
                $data['deal_id'] = $id;
                $data['user_id'] = $user_info['id'];
                $data['create_time'] = time();
				if($GLOBALS['db']->autoExecute(DB_PREFIX."collection",$data,'INSERT','','SILENT'))
				{
					$data['info'] = ($GLOBALS['lang']['COLLECTION_SUCCESS_TIP']);
				}
				else
				{
					$data['info'] = ($GLOBALS['lang']['COLLECTION_FAIL_TIP']);
				}
			}
			ajax_return($data);
		}
		else
		{
			$data['status'] = 1;
			$data['info'] = "套餐不存在";
			ajax_return($data);
		}
	}
}
?>