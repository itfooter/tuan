<?php 
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

require './system/common.php';
require './system/libs/deal.php';
require './app/Lib/app_init.php';
require './app/Lib/deal.php';
require './app/Lib/message.php';
require './app/Lib/side.php'; 

if($_REQUEST['act']=='addcart')
{
	$id = intval($_REQUEST['id']);

	$is_lottery = $GLOBALS['db']->getOneCached("select is_lottery from ".DB_PREFIX."deal where id = ".$id);
	if(!$user_info&&$is_lottery==1)
	{
		$GLOBALS['tmpl']->assign("ajax",1);
		$html = $GLOBALS['tmpl']->fetch("inc/login_form.html");
		//弹出窗口处理
		$res['open_win'] = 1;
		$res['html'] = $html;
		ajax_return($res);
	}
		
	

	$check = check_deal_time($id);
	if($check['status'] == 0)
	{
			$res['open_win'] = 2;
			$res['info'] = $check['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$check['data']];
			ajax_return($res);
	}
		
	$attr = $_REQUEST['attr'];
	
	$deal_info = $GLOBALS['cache']->get("CACHE_DEAL_".$id);
	if($deal_info === false)
	{
		$deal_info = get_deal($id);
		$GLOBALS['cache']->set("CACHE_DEAL_".$id,$deal_info);
	}	
	
	
	if(!$attr&&$deal_info['deal_attr_list'])
	{
		$GLOBALS['tmpl']->assign("deal_info",$deal_info);
		if(intval(app_conf("ATTR_SELECT"))==0)
		$html = $GLOBALS['tmpl']->fetch("deal_attr.html");
		else
		$html = $GLOBALS['tmpl']->fetch("deal_attr_check.html");
		//弹出窗口处理
		$res['open_win'] = 1;
		$res['html'] = $html;
		ajax_return($res);
	}
	else
	{
		
		
		//加入购物车处理，有提交属性， 或无属性时
		$attr_str = '0';
		$attr_name = '';
		if($attr)
		{
			$attr_str = implode(",",$attr);
			$attr_names = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."deal_attr where id in(".$attr_str.")");
			$attr_name = '';
			foreach($attr_names as $attr)
			{
				$attr_name .=$attr['name'].",";
			}
			$attr_name = substr($attr_name,0,-1);
		}
		$verify_code = md5($id."_".$attr_str);
		$session_id = session_id();
		
		if(app_conf("CART_ON")==0)
		{
				$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_cart where session_id = '".$session_id."' and user_id = ".intval($GLOBALS['user_info']['id']));
		}
		$cart_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_cart where session_id='".$session_id."' and user_id = ".intval($user_info['id'])." and verify_code = '".$verify_code."'");
		if(!$cart_item)
		{
			$attr_price = $GLOBALS['db']->getOne("select sum(price) from ".DB_PREFIX."deal_attr where id in($attr_str)");
			
			$cart_item['session_id'] = $session_id;
			$cart_item['user_id'] = intval($GLOBALS['user_info']['id']);
			$cart_item['deal_id'] = $id;
			//属性
			if($attr_name != '')
			{
				$cart_item['name'] = $deal_info['name']." [".$attr_name."]";
				$cart_item['sub_name'] = $deal_info['sub_name']." [".$attr_name."]";
			}
			else
			{
				$cart_item['name'] = $deal_info['name'];
				$cart_item['sub_name'] = $deal_info['sub_name'];
			}
			$cart_item['name'] = addslashes($cart_item['name']);
			$cart_item['sub_name'] = addslashes($cart_item['sub_name']);
			$cart_item['attr'] = $attr_str;
			$cart_item['unit_price'] = $deal_info['current_price'] + $attr_price;
			$cart_item['number'] = 1;
			$cart_item['total_price'] = $cart_item['unit_price'] * $cart_item['number'];
			$cart_item['verify_code'] = $verify_code;
			$cart_item['create_time'] = get_gmtime();
			$cart_item['update_time'] = get_gmtime();
			$cart_item['return_score'] = $deal_info['return_score'];
			$cart_item['return_total_score'] = $deal_info['return_score'] * $cart_item['number'];
			$cart_item['return_money'] = $deal_info['return_money'];
			$cart_item['return_total_money'] = $deal_info['return_money'] * $cart_item['number'];
			$cart_item['buy_type']	=	$deal_info['buy_type'];
			
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_cart",$cart_item);
		}		
		$res['open_win'] = 0;
		ajax_return($res);		
	}
}//end addcart
elseif($_REQUEST['act']=='delcart')
{
	$id = intval($_REQUEST['id']);
	$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_cart where id =".$id);
	$cart_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cart where session_id = '".session_id()."' and user_id = ".intval($user_info['id']));
	if($cart_list)
	{
		$GLOBALS['tmpl']->assign("cart_list",$cart_list);
		$GLOBALS['tmpl']->assign('total_price',$GLOBALS['db']->getOne("select sum(total_price) from ".DB_PREFIX."deal_cart where session_id = '".session_id()."' and user_id = ".intval($user_info['id'])));
		$result['html'] = $GLOBALS['tmpl']->fetch("inc/cart_list.html");
		$result['status'] = 1;
		ajax_return($result);
	}
	else
	{
		$result['status'] = 0;
		ajax_return($result);
	}

}//end delcart
elseif($_REQUEST['act']=='modifycart')
{
	$id=intval($_REQUEST['id']);
	$cart_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_cart where id=".$id);
	$number = intval($_REQUEST['number']);
	if($number<=0)
	{
		$result['info'] = $GLOBALS['lang']["BUY_COUNT_NOT_GT_ZERO"]."|".$cart_item['deal_id'];
		$result['status'] = 0;
		ajax_return($result);	
	}
	$add_number = $number - $cart_item['number'];
		
	$check = check_deal_number($cart_item['deal_id'],$add_number);
	if($check['status']==0)
	{
		$result['info'] = $check['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$check['data']]."|".$cart_item['deal_id'];
		$result['status'] = 0;
		ajax_return($result);		
	}	
	
	$GLOBALS['db']->query("update ".DB_PREFIX."deal_cart set number =".$number.", total_price = ".$number."* unit_price, return_total_score = ".$number."* return_score, return_total_money = ".$number."* return_money where id =".$id);
	$cart_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cart where session_id = '".session_id()."' and user_id = ".intval($user_info['id']));
	
	$GLOBALS['tmpl']->assign("cart_list",$cart_list);
	$GLOBALS['tmpl']->assign('total_price',$GLOBALS['db']->getOne("select sum(total_price) from ".DB_PREFIX."deal_cart where session_id = '".session_id()."' and user_id = ".intval($user_info['id'])));
	
	$result['html'] = $GLOBALS['tmpl']->fetch("inc/cart_list.html");
	$result['status'] = 1;
	ajax_return($result);
}//end modifycart
elseif($_REQUEST['act']=='check')
{
		$ajax = intval($_REQUEST['ajax']);
		
		
		//提交验证		
		if(!$user_info)
		{				
			if($ajax==1)
			{
				$GLOBALS['tmpl']->assign("ajax",1);
				$html = $GLOBALS['tmpl']->fetch("inc/login_form.html");
				//弹出窗口处理
				$res['open_win'] = 1;
				$res['html'] = $html;
				ajax_return($res);
			}
			else			
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax,url_pack("user#login"));
		}	
		
		
		$deal_ids = $GLOBALS['db']->getAll("select distinct(deal_id) as deal_id from ".DB_PREFIX."deal_cart where session_id = '".session_id()."' and user_id = ".$user_info['id']);
		foreach($deal_ids as $row)
		{
			$checker = check_deal_time($row['deal_id']);
			if($checker['status']==0)
			{				
				if($ajax==1)
				showErr($checker['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$checker['data']]."|".$row['deal_id'],$ajax,url_pack("cart"));
				else				
				showErr($checker['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$checker['data']],$ajax,url_pack("cart"));
			}
			
			$checker = check_deal_number($row['deal_id']);
			if($checker['status']==0)
			{
				if($ajax==1)
				showErr($checker['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$checker['data']]."|".$row['deal_id'],$ajax,url_pack("cart"));
				else	
				showErr($checker['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$checker['data']],$ajax,url_pack("cart"));
			}
		}	
		
		//关于积分的验证
		$total_score = $GLOBALS['db']->getOne("select sum(return_total_score) from ".DB_PREFIX."deal_cart where user_id = ".intval($user_info['id'])." and session_id = '".session_id()."'");
		$user_score = $GLOBALS['db']->getOne("select score from ".DB_PREFIX."user where id = ".intval($user_info['id']));
		if($user_score+$total_score<0)
		{
			showErr($GLOBALS['lang']['SCORE_NOT_ENOUGHT'],$ajax,url_pack("cart"));
		}
		//关于现金的验证
		$total_money = $GLOBALS['db']->getOne("select sum(return_total_money) from ".DB_PREFIX."deal_cart where user_id = ".intval($user_info['id'])." and session_id = '".session_id()."'");
		$user_money = $GLOBALS['db']->getOne("select money from ".DB_PREFIX."user where id = ".intval($user_info['id']));
		if($user_money+$total_money<0)
		{
			showErr($GLOBALS['lang']['MONEY_NOT_ENOUGHT'],$ajax,url_pack("cart"));
		}
		//增加关于抽奖手机验证
		$is_lottery = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_cart as dc left join ".DB_PREFIX."deal as d on dc.deal_id = d.id where d.is_lottery = 1 and session_id = '".session_id()."' and user_id = ".intval($user_info['id']));
		if($is_lottery)
		{
			$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($user_info['id']));
			$verify = addslashes(htmlspecialchars(trim($_REQUEST['verify'])));	
			$lottery_mobile = addslashes(htmlspecialchars(trim($_REQUEST['mobile'])));		
			if(app_conf("SMS_ON")==1)
			{	
				if($user['lottery_verify'] != ''&&$user['lottery_verify']!=$verify)
				{
					showErr($GLOBALS['lang']['LOTTERY_VERIFY_ERROR'],$ajax);
				}
				elseif($user['lottery_mobile']=='')
				{
					showErr($GLOBALS['lang']['LOTTERY_MOBILE_NOT_VERIFY'],$ajax);
				}
				else 
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."user set lottery_verify = '', verify_create_time = 0 where id = ".intval($user_info['id']));
				}
			}
			else
			{
				if($ajax==1)
				{
					//无短信验证时在此处验证唯一性
					$user_id = intval($GLOBALS['user_info']['id']);
					if($user_id == 0)
					{
						showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax,url_pack("user#login"));
					}
					if($lottery_mobile == '')
					{
						showErr($GLOBALS['lang']['LOTTERY_MOBILE_EMPTY'],$ajax);
					}					
					if(!check_mobile($lottery_mobile))
					{

						showErr($GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'],$ajax);
					}					
					
					//验证手机号的唯一购买
					$lottery_rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."lottery as l left join ".DB_PREFIX."deal_cart as dc on dc.deal_id = l.deal_id where l.user_id <> ".$user_id." and l.mobile = '".$lottery_mobile."'");
					//以上查询是否参与过本期相关的抽奖
					
					//查询是否有用户绑定
					$user= $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where lottery_mobile = '".$lottery_mobile."' and lottery_verify = ''");
					
					if($lottery_rs>0||$user)
					{
						if($user['id'] != intval($user_info['id']))
						{					
							showErr($GLOBALS['lang']['MOBILE_USED_LOTTERY'],$ajax,url_pack("user#login"));
						}
	
					}
					//end
					$GLOBALS['db']->query("update ".DB_PREFIX."user set lottery_mobile = '".$lottery_mobile."' where id = ".intval($user_info['id']));
				}
			}
		}
		
		
		//提交验证
		if($ajax == 1)
		{
			$result['status'] = 1;
			ajax_return($result);	
		}
		else
		{
			if(!$user_info)
			{
				showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax,url_pack("user#login"));
			}
				
			$cart_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cart where session_id = '".session_id()."' and user_id = ".$user_info['id']);
			if(!$cart_list)
			{
				app_redirect(APP_ROOT);
			}
			
			//输出购物车内容
			$GLOBALS['tmpl']->assign("cart_list",$cart_list);
			$GLOBALS['tmpl']->assign('total_price',$GLOBALS['db']->getOne("select sum(total_price) from ".DB_PREFIX."deal_cart where session_id = '".session_id()."' and user_id = ".intval($user_info['id'])));
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['CART_CHECK']);		

			$is_delivery = 0;
			foreach($cart_list as $k=>$v)
			{
				if($GLOBALS['db']->getOneCached("select is_delivery from ".DB_PREFIX."deal where id = ".$v['deal_id'])==1)
				{
					$is_delivery = 1;
					break;
				}
			}
			
			if($is_delivery)
			{
				//输出配送方式
				$consignee_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_consignee where user_id = ".$user_info['id']);
				$GLOBALS['tmpl']->assign("consignee_id",intval($consignee_id));
			}
			
			//配送方式由ajax由 consignee 中的地区动态获取
			
			//输出支付方式
			$payment_list = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."payment where is_effect = 1 order by sort desc");

			foreach($cart_list as $k=>$v)
			{
				if($GLOBALS['db']->getOneCached("select define_payment from ".DB_PREFIX."deal where id = ".$v['deal_id'])==1)
				{
					$define_payment_list = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."deal_payment where deal_id = ".$v['deal_id']);
					$define_payment = array();
					foreach($define_payment_list as $kk=>$vv)
					{
						array_push($define_payment,$vv['payment_id']);
					}
					foreach($payment_list as $k=>$v)
					{
						if(in_array($v['id'],$define_payment))
						{
							unset($payment_list[$k]);
						}
					}
				}
			}
			

			foreach($payment_list as $k=>$v)
			{
				$directory = APP_ROOT_PATH."system/payment/";
				$file = $directory. '/' .$v['class_name']."_payment.php";
				if(file_exists($file))
				{
					require_once($file);
					$payment_class = $v['class_name']."_payment";
					$payment_object = new $payment_class();
					$payment_list[$k]['display_code'] = $payment_object->get_display_code();
					
				}
				else
				{
					unset($payment_list[$k]);
				}
			}
			$GLOBALS['tmpl']->assign("payment_list",$payment_list);
			
			$GLOBALS['tmpl']->assign("is_delivery",$is_delivery);
			
			$is_coupon = 0;
			foreach($cart_list as $k=>$v)
			{
				if($GLOBALS['db']->getOneCached("select is_coupon from ".DB_PREFIX."deal where id = ".$v['deal_id']." and forbid_sms = 0")==1)
				{
					$is_coupon = 1;
					break;
				}
			}
			$GLOBALS['tmpl']->assign("is_coupon",$is_coupon);
			$GLOBALS['tmpl']->assign("coupon_name",app_conf("COUPON_NAME"));

			//购物车检测页
			$GLOBALS['tmpl']->display("cart_check.html");
		}

}//end check
elseif($_REQUEST['act']=='done')
{
	$region_id = intval($_REQUEST['region_lv4']);
	$delivery_id = intval($_REQUEST['delivery']);
	$payment = intval($_REQUEST['payment']);
	$account_money = floatval($_REQUEST['account_money']);
	$all_account_money = intval($_REQUEST['all_account_money']);
	$ecvsn = $_REQUEST['ecvsn']?trim($_REQUEST['ecvsn']):'';
	$ecvpassword = $_REQUEST['ecvpassword']?trim($_REQUEST['ecvpassword']):'';
	
	$user_id = intval($GLOBALS['user_info']['id']);
	$session_id = session_id();
	$goods_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cart where session_id='".$session_id."' and user_id=".$user_id);
	
	if(!$goods_list)
	{
		showErr($GLOBALS['lang']['CART_EMPTY_TIP'],$ajax);
	}
	
	
	//验证购物车
		if(!$user_info)
		{
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax,url_pack("user#login"));
		}
		$deal_ids = $GLOBALS['db']->getAll("select distinct(deal_id) as deal_id from ".DB_PREFIX."deal_cart where session_id = '".$session_id."' and user_id = ".$user_id);
		foreach($deal_ids as $row)
		{
			$checker = check_deal_time($row['deal_id']);
			if($checker['status']==0)
			{
				showErr($checker['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$checker['data']],$ajax,url_pack("cart"));
			}
			
			$checker = check_deal_number($row['deal_id']);
			if($checker['status']==0)
			{
				showErr($checker['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$checker['data']],$ajax,url_pack("cart"));
			}
			
			//验证支付方式的支持
			if($GLOBALS['db']->getOneCached("select define_payment from ".DB_PREFIX."deal where id = ".$row['deal_id'])==1)
			{
				if($GLOBALS['db']->getOneCached("select count(*) from ".DB_PREFIX."deal_payment where deal_id = ".$row['deal_id']." and payment_id = ".$payment))
				{
					showErr($GLOBALS['lang']['INVALID_PAYMENT'],$ajax,url_pack("cart"));
				}
			}
		}

		
		
		
	//结束验证购物车
	//开始验证订单接交信息
	require_once APP_ROOT_PATH."system/libs/cart.php";
	$data = count_buy_total($region_id,$delivery_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword,$goods_list); 
	

	if($data['is_delivery'] == 1)
	{
				//配送验证
				if(!$data['region_info']||$data['region_info']['region_level'] != 4)
				{
					showErr($GLOBALS['lang']['FILL_CORRECT_CONSIGNEE_ADDRESS']);
				}
				if(trim($_REQUEST['consignee'])=='')
				{
					showErr($GLOBALS['lang']['FILL_CORRECT_CONSIGNEE']);
				}
				if(trim($_REQUEST['address'])=='')
				{
					showErr($GLOBALS['lang']['FILL_CORRECT_ADDRESS']);
				}
				if(trim($_REQUEST['zip'])=='')
				{
					showErr($GLOBALS['lang']['FILL_CORRECT_ZIP']);
				}
				if(trim($_REQUEST['mobile'])=='')
				{
					showErr($GLOBALS['lang']['FILL_MOBILE_PHONE']);
					
				}
				if(!check_mobile(trim($_REQUEST['mobile'])))
				{
					showErr($GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE']);
				}
				if(!$data['delivery_info'])
				{
					showErr($GLOBALS['lang']['PLEASE_SELECT_DELIVERY']);
				}			
	}
	
	if(round($data['pay_price'],4)>0&&!$data['payment_info'])
	{
				showErr($GLOBALS['lang']['PLEASE_SELECT_PAYMENT']);
	}	
	//结束验证订单接交信息
	
	//开始生成订单
	$now = get_gmtime();
	$order['type'] = 0; //普通订单
	$order['user_id'] = $user_id;
	$order['create_time'] = $now;	
	$order['total_price'] = $data['pay_total_price'];  //应付总额  商品价 - 会员折扣 + 运费 + 支付手续费
	$order['pay_amount'] = 0;  
	$order['pay_status'] = 0;  //新单都为零， 等下面的流程同步订单状态
	$order['delivery_status'] = $data['is_delivery']==0?5:0;  
	$order['order_status'] = 0;  //新单都为零， 等下面的流程同步订单状态
	$order['return_total_score'] = $data['return_total_score'];  //结单后送的积分
	$order['return_total_money'] = $data['return_total_money'];  //结单后送的现金
	$order['memo'] = htmlspecialchars(addslashes(trim($_REQUEST['memo'])));
	$order['region_lv1'] = intval($_REQUEST['region_lv1']);
	$order['region_lv2'] = intval($_REQUEST['region_lv2']);
	$order['region_lv3'] = intval($_REQUEST['region_lv3']);
	$order['region_lv4'] = intval($_REQUEST['region_lv4']);
	$order['address']	=	htmlspecialchars(addslashes(trim($_REQUEST['address'])));
	$order['mobile']	=	htmlspecialchars(addslashes(trim($_REQUEST['mobile'])));
	$order['consignee']	=	htmlspecialchars(addslashes(trim($_REQUEST['consignee'])));
	$order['zip']	=	htmlspecialchars(addslashes(trim($_REQUEST['zip'])));
	$order['deal_total_price'] = $data['total_price'];   //团购商品总价
	$order['discount_price'] = $data['user_discount'];
	$order['delivery_fee'] = $data['delivery_fee'];
	$order['ecv_money'] = 0;
	$order['account_money'] = 0;
	$order['ecv_sn'] = '';
	$order['delivery_id'] = $data['delivery_info']['id'];
	$order['payment_id'] = $data['payment_info']['id'];
	$order['payment_fee'] = $data['payment_fee'];
	$order['payment_fee'] = $data['payment_fee'];
	$order['bank_id'] = htmlspecialchars(addslashes(trim($_REQUEST['bank_id'])));
	$coupon_mobile = htmlspecialchars(addslashes(trim($_REQUEST['coupon_mobile'])));
	if($coupon_mobile!='')
	$GLOBALS['db']->query("update ".DB_PREFIX."user set mobile = '".$coupon_mobile."' where id = ".intval($_SESSION['user_info']['id']));
	

	do
	{
		$order['order_sn'] = to_date(get_gmtime(),"Ymdhis").rand(10,99);
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order",$order,'INSERT','','SILENT'); 
		$order_id = intval($GLOBALS['db']->insert_id());
	}while($order_id==0);

	//生成订单商品
	foreach($goods_list as $k=>$v)
	{
		$goods_item = array();
		$goods_item['deal_id'] = $v['deal_id'];
		$goods_item['number'] = $v['number'];
		$goods_item['unit_price'] = $v['unit_price'];
		$goods_item['total_price'] = $v['total_price'];
		$goods_item['name'] = addslashes($v['name']);
		$goods_item['sub_name'] = addslashes($v['sub_name']);
		$goods_item['attr'] = $v['attr'];
		$goods_item['verify_code'] = $v['verify_code'];
		$goods_item['order_id'] = $order_id;
		$goods_item['return_score'] = $v['return_score'];
		$goods_item['return_total_score'] = $v['return_total_score'];
		$goods_item['return_money'] = $v['return_money'];
		$goods_item['return_total_money'] = $v['return_total_money'];
		$goods_item['buy_type']	=	$v['buy_type']; 
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order_item",$goods_item,'INSERT','','SILENT'); 	
	}
	
	$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_cart where session_id = '".$session_id."' and user_id = ".$user_id);
	

	if($data['is_delivery']==1)
	{
		//保存收款人
		$user_consignee = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_consignee where user_id = ".$user_id." order by id desc");
		$user_consignee['region_lv1'] = intval($_REQUEST['region_lv1']);
		$user_consignee['region_lv2'] = intval($_REQUEST['region_lv2']);
		$user_consignee['region_lv3'] = intval($_REQUEST['region_lv3']);
		$user_consignee['region_lv4'] = intval($_REQUEST['region_lv4']);
		$user_consignee['address']	=	htmlspecialchars(addslashes(trim($_REQUEST['address'])));
		$user_consignee['mobile']	=	htmlspecialchars(addslashes(trim($_REQUEST['mobile'])));
		$user_consignee['consignee']	=	htmlspecialchars(addslashes(trim($_REQUEST['consignee'])));
		$user_consignee['zip']	=	htmlspecialchars(addslashes(trim($_REQUEST['zip'])));
		$user_consignee['user_id']	=	$user_id;
		if(intval($user_consignee['id'])==0)
		{
			//新增 
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee",$user_consignee,'INSERT','','SILENT'); 	
		}
		else
		{
			//更新
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee",$user_consignee,'UPDATE','id='.$user_consignee['id'],'SILENT'); 
			$GLOBALS['cache']->rm("CONSIGNEE_INFO_".intval($user_consignee['id']));
		}
	}
	
	//开始发送下单短信
	if(app_conf("SMS_ON")==1)
	{
		$u_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);

		$msg = $u_info['user_name'].",您好!亲爱滴已收到您于提交的订单,我们会在收到您的".format_price($order['total_price'])."货款后及时处理订单,祝您购物愉快";
		$msg_data = array();
		$msg_data['dest'] = $u_info['mobile']==''?$user_consignee['mobile']:$u_info['mobile'];
		$msg_data['send_type'] = 0;
		$msg_data['content'] = addslashes($msg);;
		$msg_data['send_time'] = 0;
		$msg_data['is_send'] = 0;
		$msg_data['create_time'] = get_gmtime();
		$msg_data['user_id'] = $u_info['id'];
		$msg_data['is_html'] = 0;

		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入		
	}
	//end	
	
	//生成order_id 后
	//1. 代金券支付
	$ecv_data = $data['ecv_data'];
	if($ecv_data)
	{
		$ecv_payment_id = $GLOBALS['db']->getOneCached("select id from ".DB_PREFIX."payment where class_name = 'Voucher'");
		$payment_notice_id = make_payment_notice($ecv_data['money'],$order_id,$ecv_payment_id);
		require_once APP_ROOT_PATH."system/payment/Voucher_payment.php";
		$voucher_payment = new Voucher_payment();
		$voucher_payment->direct_pay($ecv_data['sn'],$ecv_data['password'],$payment_notice_id);
	}
	
	//2. 余额支付
	$account_money = $data['account_money'];
	if(floatval($account_money) > 0)
	{
		$account_payment_id = $GLOBALS['db']->getOneCached("select id from ".DB_PREFIX."payment where class_name = 'Account'");
		$payment_notice_id = make_payment_notice($account_money,$order_id,$account_payment_id);
		require_once APP_ROOT_PATH."system/payment/Account_payment.php";
		$account_payment = new Account_payment();
		$account_payment->get_payment_code($payment_notice_id);
	}
	
	//3. 相应的支付接口
	$payment_info = $data['payment_info'];
	if($payment_info&&$data['pay_price']>0)
	{
		$payment_notice_id = make_payment_notice($data['pay_price'],$order_id,$payment_info['id']);
		//创建支付接口的付款单
	}
	
	$rs = order_paid($order_id);  
	if($rs)
	{
		app_redirect(url_pack("payment#done",$order_id)); //支付成功
	}
	else
	{
		app_redirect(url_pack("payment#pay",$payment_notice_id)); 
	}
}
elseif($_REQUEST['act']=='order_done')
{
	$id = intval($_REQUEST['id']); //订单号
	$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$id." and is_delete = 0");
	if(!$order)
	{
		showErr($GLOBALS['lang']['INVALID_ORDER_DATA']);
	}
	$region_id = intval($_REQUEST['region_lv4']);
	$delivery_id = intval($_REQUEST['delivery']);
	$payment = intval($_REQUEST['payment']);
	$account_money = floatval($_REQUEST['account_money']);
	$all_account_money = intval($_REQUEST['all_account_money']);
	$ecvsn = $_REQUEST['ecvsn']?trim($_REQUEST['ecvsn']):'';
	$ecvpassword = $_REQUEST['ecvpassword']?trim($_REQUEST['ecvpassword']):'';
	

	$goods_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order['id']);
	
	
	//验证购物车
	if(!$user_info)
	{
		showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],0,url_pack("user#login"));
	}
	
	//验证支付方式的支持
	foreach($goods_list as $k=>$row)
	{
		if($GLOBALS['db']->getOneCached("select define_payment from ".DB_PREFIX."deal where id = ".$row['deal_id'])==1)
		{
				if($GLOBALS['db']->getOneCached("select count(*) from ".DB_PREFIX."deal_payment where deal_id = ".$row['deal_id']." and payment_id = ".$payment))
				{
					showErr($GLOBALS['lang']['INVALID_PAYMENT'],$ajax);
				}
		}
	}
	//结束验证购物车
	
	//开始验证订单接交信息
	require_once APP_ROOT_PATH."system/libs/cart.php";
	$data = count_buy_total($region_id,$delivery_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword,$goods_list,$order['account_money'],$order['ecv_money']); 
	

	if($data['is_delivery'] == 1)
	{
				//配送验证
				if(!$data['region_info']||$data['region_info']['region_level'] != 4)
				{
					showErr($GLOBALS['lang']['FILL_CORRECT_CONSIGNEE_ADDRESS']);
				}
				if(trim($_REQUEST['consignee'])=='')
				{
					showErr($GLOBALS['lang']['FILL_CORRECT_CONSIGNEE']);
				}
				if(trim($_REQUEST['address'])=='')
				{
					showErr($GLOBALS['lang']['FILL_CORRECT_ADDRESS']);
				}
				if(trim($_REQUEST['zip'])=='')
				{
					showErr($GLOBALS['lang']['FILL_CORRECT_ZIP']);
				}
				if(trim($_REQUEST['mobile'])=='')
				{
					showErr($GLOBALS['lang']['FILL_MOBILE_PHONE']);
					
				}
				if(!check_mobile(trim($_REQUEST['mobile'])))
				{
					showErr($GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE']);
				}
				if(!$data['delivery_info'])
				{
					showErr($GLOBALS['lang']['PLEASE_SELECT_DELIVERY']);
				}			
	}
	
	if(round($data['pay_price'],4)>0&&!$data['payment_info'])
	{
				showErr($GLOBALS['lang']['PLEASE_SELECT_PAYMENT']);
	}	
	//结束验证订单接交信息
	
	//开始修正订单
	$now = get_gmtime();
	$order['total_price'] = $data['pay_total_price'];  //应付总额  商品价 - 会员折扣 + 运费 + 支付手续费
	$order['memo'] = htmlspecialchars(trim($_REQUEST['memo']));
	$order['region_lv1'] = intval($_REQUEST['region_lv1']);
	$order['region_lv2'] = intval($_REQUEST['region_lv2']);
	$order['region_lv3'] = intval($_REQUEST['region_lv3']);
	$order['region_lv4'] = intval($_REQUEST['region_lv4']);
	$order['address']	=	htmlspecialchars(addslashes(trim($_REQUEST['address'])));
	$order['mobile']	=	htmlspecialchars(addslashes(trim($_REQUEST['mobile'])));
	$order['consignee']	=	htmlspecialchars(addslashes(trim($_REQUEST['consignee'])));
	$order['zip']	=	htmlspecialchars(addslashes(trim($_REQUEST['zip'])));
	$order['delivery_fee'] = $data['delivery_fee'];
	$order['delivery_id'] = $data['delivery_info']['id'];
	$order['payment_id'] = $data['payment_info']['id'];
	$order['payment_fee'] = $data['payment_fee'];
	$order['bank_id'] = htmlspecialchars(addslashes(trim($_REQUEST['bank_id'])));
	$coupon_mobile = htmlspecialchars(addslashes(trim($_REQUEST['coupon_mobile'])));
	if($coupon_mobile!='')
	$GLOBALS['db']->query("update ".DB_PREFIX."user set mobile = '".$coupon_mobile."' where id = ".intval($_SESSION['user_info']['id']));
	
	$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order",$order,'UPDATE','id='.$order['id'],'SILENT'); 

	
	
	if($data['is_delivery']==1)
	{
		//保存收款人
		$user_consignee = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_consignee where user_id = ".$order['user_id']." order by id desc");
		$user_consignee['region_lv1'] = intval($_REQUEST['region_lv1']);
		$user_consignee['region_lv2'] = intval($_REQUEST['region_lv2']);
		$user_consignee['region_lv3'] = intval($_REQUEST['region_lv3']);
		$user_consignee['region_lv4'] = intval($_REQUEST['region_lv4']);
		$user_consignee['address']	=	htmlspecialchars(addslashes(trim($_REQUEST['address'])));
		$user_consignee['mobile']	=	htmlspecialchars(addslashes(trim($_REQUEST['mobile'])));
		$user_consignee['consignee']	=	htmlspecialchars(addslashes(trim($_REQUEST['consignee'])));
		$user_consignee['zip']	=	htmlspecialchars(addslashes(trim($_REQUEST['zip'])));
		$user_consignee['user_id']	=	$order['user_id'];
		if(intval($user_consignee['id'])==0)
		{
			//新增 
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee",$user_consignee,'INSERT','','SILENT'); 	
		}
		else
		{
			//更新
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee",$user_consignee,'UPDATE','id='.$user_consignee['id'],'SILENT'); 
		}
	}
	
	
	
	//生成order_id 后
	//1. 余额支付
	$account_money = $data['account_money'];
	if(floatval($account_money) > 0)
	{
		$account_payment_id = $GLOBALS['db']->getOneCached("select id from ".DB_PREFIX."payment where class_name = 'Account'");
		$payment_notice_id = make_payment_notice($account_money,$order['id'],$account_payment_id);
		require_once APP_ROOT_PATH."system/payment/Account_payment.php";
		$account_payment = new Account_payment();
		$account_payment->get_payment_code($payment_notice_id);
	}
	
	//3. 相应的支付接口
	$payment_info = $data['payment_info'];
	if($payment_info&&$data['pay_price']>0)
	{
		$payment_notice_id = make_payment_notice($data['pay_price'],$order['id'],$payment_info['id']);
		//创建支付接口的付款单
	}
	
	$rs = order_paid($order['id']); 

	if($rs)
	{
		app_redirect(url_pack("payment#done",$order['id'])); //支付成功
	}
	else
	{
		app_redirect(url_pack("payment#pay",$payment_notice_id)); 
	}
}
else
{
	//增加输出购物车中产品是否参加抽奖
	$is_lottery = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_cart as dc left join ".DB_PREFIX."deal as d on dc.deal_id = d.id where d.is_lottery = 1 and session_id = '".session_id()."' and user_id = ".intval($user_info['id']));
	$GLOBALS['tmpl']->assign("is_lottery",$is_lottery);

	if(!$user_info&&$is_lottery>0) //购物车中有抽奖商品时必需先登录
	{
		showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax,url_pack("user#login"));
	}

	$GLOBALS['db']->query("update ".DB_PREFIX."deal_cart set update_time=".get_gmtime().",user_id = ".intval($user_info['id'])." where session_id = '".session_id()."'");
	$cart_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cart where session_id = '".session_id()."' and user_id = ".intval($user_info['id']));

	$GLOBALS['tmpl']->assign("cart_list",$cart_list);
	$GLOBALS['tmpl']->assign('total_price',$GLOBALS['db']->getOne("select sum(total_price) from ".DB_PREFIX."deal_cart where session_id = '".session_id()."' and user_id = ".intval($user_info['id'])));
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['CART_LIST']);
	
	
	
	//输出抽奖验证过的用户手机号
	$lottery_mobile = $GLOBALS['db']->getOne("select lottery_mobile from ".DB_PREFIX."user where id = ".intval($user_info['id']));
	$GLOBALS['tmpl']->assign("lottery_mobile",$lottery_mobile);
	
	$GLOBALS['tmpl']->display("cart.html");
}



?>