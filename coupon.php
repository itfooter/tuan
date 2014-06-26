<?php 

$m = $_REQUEST['m'];
$a = $_REQUEST['a'];
if((trim($m)=='File'&&trim($a)=='do_upload_img')||(trim($m)=='File'&&trim($a)=='do_upload'))
{
	require "admin.php";
	die();
}
else
{
	require './system/common.php';
	require './app/Lib/app_init.php';
	require './app/Lib/page.php';

	function export_order($page = 1,$deal_id=0)
	{
		set_time_limit(0);
		$account_id = intval($_SESSION['account_info']['id']);
		$supplier_id = intval($GLOBALS['db']->getOneCached("select supplier_id from ".DB_PREFIX."supplier_account where id = ".intval($_SESSION['account_info']['id'])));
	
		
		$ext_where = ' and do.is_delete = 0 and do.after_sale = 0';		
		if($deal_id>0)
		{
			$ext_where.=" and doi.deal_id = ".$deal_id;
		}
		
		//分页
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");		
		$order_list_sql = "select do.*,doi.number".
						  " from ".DB_PREFIX."deal_order_item as doi left join ".
						  DB_PREFIX."deal_order as do on do.id = doi.order_id left join ".
						  DB_PREFIX."deal as d on doi.deal_id = d.id where do.pay_status = 2 and d.supplier_id = ".$supplier_id.$ext_where.
						  " group by do.id order by do.create_time desc limit ".$limit;
		$list = $GLOBALS['db']->getAll($order_list_sql);
		
		if($list)
		{
			register_shutdown_function("export_order", $page+1,$deal_id);
			$order_value = array('sn'=>'""', 'user_name'=>'""', 'deal_name'=>'""','number'=>'""', 'create_time'=>'""', 'consignee'=>'""', 'address'=>'""','zip'=>'""','email'=>'""', 'mobile'=>'""', 'memo'=>'""');
		    if($page == 1)
		    {
			    	$content = iconv("utf-8","gbk","订单编号,用户名,团购产品及团购券,订购总数量,下单时间,收货人,发货地址,邮编,用户邮件,手机号码,订单留言");	    		    	
			    	$content = $content . "\n";
		    }
		    
			foreach($list as $k=>$v)
			{
					
					$order_value['sn'] = '"' . "sn:".iconv('utf-8','gbk',$v['order_sn']) . '"';
					$order_value['user_name'] = '"' . iconv('utf-8','gbk',$GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$v['user_id'])) . '"';
					
					
					//获取相应的团购名称，数量与团购券
					$deal_order_item = $GLOBALS['db']->getAllCached("select doi.* from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal as d on doi.deal_id = d.id where doi.order_id = ".$v['id']." and d.supplier_id = ".$supplier_id." and d.id = ".intval($deal_id));
					$str = '';
					foreach($deal_order_item as $kk=>$vv)
					{
						$str .=$vv['sub_name']."[数量：".$vv['number'];	
						
						$coupon_list = $GLOBALS['db']->getAll("select sn,confirm_account,confirm_time,begin_time,end_time from ".DB_PREFIX."deal_coupon where order_deal_id = ".$vv['id']." and is_valid = 1");
						if($coupon_list)
						{
							$str.=" 团购券：";
							foreach($coupon_list as $kkk=>$vvv)
							{
								$str.=$vvv['sn'];
								if($vvv['confirm_account']!=0)
								{
									$account_name = $GLOBALS['db']->getOneCached("select account_name from ".DB_PREFIX."supplier_account where id = ".$vvv['confirm_account']);
									$str.= " (".to_date($vvv['confirm_time']).") ".$GLOBALS['lang']['COUPON_USED'];						
								}
								else
								{
									if($vvv['begin_time']!=0&&$vvv['begin_time']>get_gmtime())
									{
										$str.= " (".$GLOBALS['lang']['COUPON_NOT_BEGIN'].")";
									}
									
									if($vvv['end_time']!=0&&$vvv['end_time']<get_gmtime())
									{
										$str.= " (".$GLOBALS['lang']['COUPON_ENDED'].")";
									}
								}
								
								$str.=",";
							}
							$str = substr($str,0,-1);
							$str.="]";
						}
						else
						{
							$str.=$GLOBALS['lang']['NO_COUPON_GEN']."]";
						}
					}
					
					//end
					
					$order_value['deal_name'] = '"' . iconv('utf-8','gbk',$str) . '"';
					$order_value['number'] = '"' . iconv('utf-8','gbk',$v['number']) . '"';					
					$order_value['create_time'] = '"' . iconv('utf-8','gbk',to_date($v['create_time'])) . '"';				
					$order_value['consignee'] = '"' . iconv('utf-8','gbk',$v['consignee']) . '"';
					
					$region_lv1_name = $GLOBALS['db']->getOneCached("select name from ".DB_PREFIX."delivery_region where id = ".$v['region_lv1']);
					$region_lv2_name = $GLOBALS['db']->getOneCached("select name from ".DB_PREFIX."delivery_region where id = ".$v['region_lv2']);
					$region_lv3_name = $GLOBALS['db']->getOneCached("select name from ".DB_PREFIX."delivery_region where id = ".$v['region_lv3']);
					$region_lv4_name = $GLOBALS['db']->getOneCached("select name from ".DB_PREFIX."delivery_region where id = ".$v['region_lv4']);
					$address = $region_lv1_name.$region_lv2_name.$region_lv3_name.$region_lv4_name.$v['address'];
					$order_value['address'] = '"' . iconv('utf-8','gbk',$address) . '"';
					$order_value['zip'] = '"' . iconv('utf-8','gbk',$v['zip']) . '"';
					$order_value['email'] = '"' . iconv('utf-8','gbk',$v['email']) . '"';
					$order_value['mobile'] = '"' . iconv('utf-8','gbk',$v['mobile']) . '"';
					$order_value['memo'] = '"' . iconv('utf-8','gbk',$v['memo']) . '"';
					
					$content .= implode(",", $order_value) . "\n";
			}
			header("Content-Disposition: attachment; filename=order_list.csv");
		    echo $content;
		}
		else
		{
				if($page==1)
				showErr($GLOBALS['lang']["NO_RESULT"]);
		}	
		
	}
	//var_dump($_REQUEST);exit;
	$_REQUEST['act'] =  htmlspecialchars(strip_tags(trim($_REQUEST['act'])));
	$act=$_REQUEST['act'];
	//商家提交团购
	if($act == 'adddeal')
	{
		$account_id = intval($_SESSION['account_info']['id']);
		$supplier_id = intval($GLOBALS['db']->getOneCached("select supplier_id from ".DB_PREFIX."supplier_account where id = ".intval($_SESSION['account_info']['id'])));
		$data['name'] = addslashes(htmlspecialchars(trim($_REQUEST['name'])));
		$data['sub_name'] = addslashes(htmlspecialchars(trim($_REQUEST['sub_name'])));
		$data['origin_price'] = doubleval($_REQUEST['origin_price']);
		$data['current_price'] = doubleval($_REQUEST['balance_price']);
		$data['max_bought'] = intval($_REQUEST['max_bought']);
		$data['brief'] = addslashes(htmlspecialchars(trim($_REQUEST['brief'])));
		$data['buy_type'] = intval($_REQUEST['buy_type']);
		$data['is_coupon'] = 1;
		$data['cate_id'] = intval($_REQUEST['cate_id']);
		$data['area_id'] = intval($_REQUEST['area_id']);
		$data['city_id'] = intval($_REQUEST['city_id']);
		$data['icon'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['icon']))));
		$data['description'] = trim(replace_public($_REQUEST['descript']));
		$data['description'] = valid_tag($data['description']);
		$data['account_id'] = $supplier_id;
		$data['publish_wait'] = 1;
		$data['create_time'] = get_gmtime();
		$data['is_effect'] = 0;
		$data['supplier_id'] = $supplier_id;		
		$data['begin_time'] = trim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$data['end_time'] = trim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		if($_REQUEST['img0']!='')
		$data['img'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img0']))));
		else if($_REQUEST['img1']!='')
		$data['img'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img1']))));
		else if($_REQUEST['img2']!='')
		$data['img'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img2']))));
		else if($_REQUEST['img3']!='')
		$data['img'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img3']))));
		else if($_REQUEST['img4']!='')
		$data['img'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img4']))));
		else if($_REQUEST['img5']!='')
		$data['img'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img5']))));
	
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$data);
		$deal_id = intval($GLOBALS['db']->insert_id());
		if($deal_id>0)
		{
			if($_REQUEST['img0']!='')
			{
			$deal_gallery_0 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img0'])))),"deal_id"=>$deal_id,"sort"=>0);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_0);
			}
			
			if($_REQUEST['img1']!='')
			{
			$deal_gallery_1 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img1'])))),"deal_id"=>$deal_id,"sort"=>1);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_1);
			}
			
			if($_REQUEST['img2']!='')
			{
			$deal_gallery_2 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img2'])))),"deal_id"=>$deal_id,"sort"=>2);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_2);
			}
			
			if($_REQUEST['img3']!='')
			{
			$deal_gallery_3 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img3'])))),"deal_id"=>$deal_id,"sort"=>3);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_3);
			}
			
			if($_REQUEST['img4']!='')
			{
			$deal_gallery_4 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img4'])))),"deal_id"=>$deal_id,"sort"=>4);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_4);
			}
			
			if($_REQUEST['img5']!='')
			{
			$deal_gallery_5 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img5'])))),"deal_id"=>$deal_id,"sort"=>5);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_5);
			}
			
			showSuccess("提交成功，请等待管理员审核");
		}
		else
		{
			showErr("发布失败");
		}
	}
	//商家发布团购页面
	else if($act=='publish')
	{
		
			if(intval($_SESSION['account_info']['id'])==0)
			{
			showErr($GLOBALS['lang']['SUPPLIER_NOT_LOGIN'],0,url_pack("coupon#verify"));		
			}
			$s_account_info = $_SESSION['account_info'];
	
			$GLOBALS['tmpl']->assign("page_title","发布产品");
			$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_effect = 1 and is_delete =0 order by sort desc");
			$GLOBALS['tmpl']->assign("cate_list",$cate_list);
			$GLOBALS['tmpl']->display("coupon_publish.html");
	}
	else if($act=='area_list')
	{
				$city_id = intval($_REQUEST['city_id']);
				$id = intval($_REQUEST['id']);
				$area_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_city_area where is_delete=0 and is_effect=1 and city_id=".$city_id);
				
				foreach($area_list as $k => $v)
				{
					if($area_list[$k]['pid'] >0)
					{
						$area_list[$k]['title_show'] = "&nbsp;&nbsp;&nbsp;|--".$area_list[$k]['name'];
					}
					else
					{
						$area_list[$k]['title_show'] = "|--".$area_list[$k]['name'];
					}
				}
				$Deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$id);
				$area_id = intval($Deal_info['area_id']);
				foreach($area_list as $k=>$v)
				{
					if($v['id']==$area_id)
					{
						$area_list[$k]['selected'] = true;
					}
				}
				$GLOBALS['tmpl']->assign("area_list",$area_list);
				$tmpl->display("area_list.html");
	}
	//商家修改
	else if($act == 'modify')
	{
	
		$id = intval($_REQUEST['id']);
		$deal_info = $GLOBALS['db']->getRow("select d.* from ".DB_PREFIX."deal as d where d.id = ".$id." and d.publish_wait = 1 ");
		if(!$deal_info)
		{
			showErr("产品不存在或者没有编辑该产品的权限");
		}
		$deal_info['begin_time'] = $deal_info['begin_time']>0?to_date($deal_info['begin_time'],"Y-m-d"):"";
		$deal_info['end_time'] = $deal_info['end_time']>0?to_date($deal_info['end_time'],"Y-m-d"):"";
		$deal_info['images'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_gallery where deal_id = ".$deal_info['id']." order by sort asc");
	
		$GLOBALS['tmpl']->assign("deal_info",$deal_info);
		$GLOBALS['tmpl']->assign("page_title","编辑产品");
		$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_effect = 1 and is_delete =0 order by sort desc");
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		$GLOBALS['tmpl']->display("coupon_modify.html");
	}
	//商家提交修改
	else if($act == 'submit_modify')
	{
		$account_id = intval($_SESSION['account_info']['id']);
		$supplier_id = intval($GLOBALS['db']->getOneCached("select supplier_id from ".DB_PREFIX."supplier_account where id = ".intval($_SESSION['account_info']['id'])));
		$id = intval($_REQUEST['id']);
		$data = $GLOBALS['db']->getRow("select d.* from ".DB_PREFIX."deal as d where d.id = ".$id." and d.publish_wait = 1 ");
		if(!$data)
		{
			showErr("产品不存在或者没有编辑该产品的权限",0,APP_ROOT."/coupon.php?city=&act=deal&");
		}
		
		$data['name'] = addslashes(htmlspecialchars(trim($_REQUEST['name'])));
		$data['sub_name'] = addslashes(htmlspecialchars(trim($_REQUEST['sub_name'])));
		$data['origin_price'] = doubleval($_REQUEST['origin_price']);
		$data['current_price'] = doubleval($_REQUEST['balance_price']);
		$data['max_bought'] = intval($_REQUEST['max_bought']);
		$data['brief'] = addslashes(htmlspecialchars(trim($_REQUEST['brief'])));
		$data['buy_type'] = intval($_REQUEST['buy_type']);
		$data['is_coupon'] = 1;
		$data['cate_id'] = intval($_REQUEST['cate_id']);
		$data['area_id'] = intval($_REQUEST['area_id']);
		$data['city_id'] = intval($_REQUEST['city_id']);
		$data['icon'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['icon']))));
		$data['description'] = trim(replace_public($_REQUEST['descript']));
		$data['description'] = valid_tag($data['description']);
		$data['account_id'] = $supplier_id;
		$data['publish_wait'] = 1;
		$data['create_time'] = get_gmtime();
		$data['is_effect'] = 0;
		$data['supplier_id'] = $supplier_id;		
		$data['begin_time'] = trim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$data['end_time'] = trim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		if($_REQUEST['img0']!='')
		$data['img'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img0']))));
		else if($_REQUEST['img1']!='')
		$data['img'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img1']))));
		else if($_REQUEST['img2']!='')
		$data['img'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img2']))));
		else if($_REQUEST['img3']!='')
		$data['img'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img3']))));
		else if($_REQUEST['img4']!='')
		$data['img'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img4']))));
		else if($_REQUEST['img5']!='')
		$data['img'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img5']))));
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$data,"UPDATE","id=".$data['id']);
		$deal_id = $data['id'];
		if($deal_id>0)
		{
			$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_gallery where deal_id = ".$deal_id." and sort < 6");
			if($_REQUEST['img0']!='')
			{
			$deal_gallery_0 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img0'])))),"deal_id"=>$deal_id,"sort"=>0);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_0);
			}
			
			if($_REQUEST['img1']!='')
			{
			$deal_gallery_1 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img1'])))),"deal_id"=>$deal_id,"sort"=>1);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_1);
			}
			
			if($_REQUEST['img2']!='')
			{
			$deal_gallery_2 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img2'])))),"deal_id"=>$deal_id,"sort"=>2);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_2);
			}
			
			if($_REQUEST['img3']!='')
			{
			$deal_gallery_3 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img3'])))),"deal_id"=>$deal_id,"sort"=>3);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_3);
			}
			
			if($_REQUEST['img4']!='')
			{
			$deal_gallery_4 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img4'])))),"deal_id"=>$deal_id,"sort"=>4);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_4);
			}
			
			if($_REQUEST['img5']!='')
			{
			$deal_gallery_5 = array("img"=>addslashes(htmlspecialchars(trim(replace_public($_REQUEST['img5'])))),"deal_id"=>$deal_id,"sort"=>5);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_gallery",$deal_gallery_5);
			}
			
			showSuccess("提交成功，请等待管理员审核");
		}
		else
		{
			showErr("发布失败");
		}
	}
	else if($act=='verify')
	{
		$account_id = intval($_SESSION['account_info']['id']);
		$account_data = $GLOBALS['db']->getRowCached("select s.name as name,a.account_name as account_name, a.supplier_id as supplier_id from ".DB_PREFIX."supplier_account as a left join ".DB_PREFIX."supplier as s on a.supplier_id = s.id where a.id = ".$account_id);
		$GLOBALS['tmpl']->assign("account_data",$account_data);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['VERIFY_COUPON']);
		$GLOBALS['tmpl']->display("coupon_verify.html");
	}
	elseif($act=='check_coupon')
	{
		$now = get_gmtime();
		$sn = htmlspecialchars(addslashes($_REQUEST['coupon_sn']));
		$coupon_data = $GLOBALS['db']->getRow("select doi.name as name,c.sn as sn from ".DB_PREFIX."deal_coupon as c left join ".DB_PREFIX."deal_order_item as doi on c.order_deal_id = doi.id where c.sn = '".$sn."' and c.is_valid = 1 and c.is_delete = 0 and c.confirm_time = 0 and c.begin_time <".$now." and (c.end_time = 0 or c.end_time>".$now.")");
		header("Content-Type:text/html; charset=utf-8");
		if($coupon_data)
		{
			echo sprintf($GLOBALS['lang']['COUPON_IS_VALID'],$coupon_data['name'],$coupon_data['sn']);
		}
		else
		{
			echo $GLOBALS['lang']['COUPON_INVALID'];
		}
	}
	elseif($act=='use_coupon')
	{
		if(intval($_SESSION['account_info']['id'])==0)
		{
			$result['status'] = 2;
			ajax_return($result);
		}
		else
		{
			$now = get_gmtime();
			$sn = htmlspecialchars(addslashes($_REQUEST['coupon_sn']));
			$pwd = htmlspecialchars(addslashes($_REQUEST['coupon_pwd']));
			$supplier_id = intval($_SESSION['account_info']['supplier_id']);
			$coupon_data = $GLOBALS['db']->getRow("select c.id as id,doi.name as name,c.sn as sn,c.supplier_id as supplier_id,c.confirm_time as confirm_time from ".DB_PREFIX."deal_coupon as c left join ".DB_PREFIX."deal_order_item as doi on c.order_deal_id = doi.id where c.sn = '".$sn."' and c.password = '".$pwd."' and c.is_valid = 1 and c.is_delete = 0  and c.begin_time <".$now." and (c.end_time = 0 or c.end_time>".$now.")"); 
			if($coupon_data)
			{
				if($coupon_data['supplier_id']!=$supplier_id)
				{
					$result['status'] = 0;
					$result['msg'] = $GLOBALS['lang']['COUPON_INVALID_SUPPLIER'];
					ajax_return($result);
				}
				elseif($coupon_data['confirm_time'] > 0)
				{
					$result['status'] = 0;
					$result['msg'] = sprintf($GLOBALS['lang']['COUPON_INVALID_USED'],to_date($coupon_data['confirm_time']));
					ajax_return($result);
				}
				else
				{
					//开始确认
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set confirm_account = ".intval($_SESSION['account_info']['id']).",confirm_time=".$now." where id = ".intval($coupon_data['id']));
					$result['status'] = 1;
					$result['msg'] = sprintf($GLOBALS['lang']['COUPON_USED_OK'],to_date($now));
					send_use_coupon_sms(intval($coupon_data['id'])); //发送团购券确认消息
					send_use_coupon_mail(intval($coupon_data['id'])); //发送团购券确认消息
					ajax_return($result);
				}
			}
			else
			{				
				$result['status'] = 0;
				$result['msg'] = $GLOBALS['lang']['COUPON_INVALID'];
				ajax_return($result);
			}
		}
	}
	elseif($act=='supplier_login')
	{
		if(intval($_SESSION['account_info']['id'])==0)
		{
			$tmpl->assign("page_title",$GLOBALS['lang']['SUPPLIER_LOGIN']);
			$tmpl->display("supplier_login.html");
		}
		else
		{
			app_redirect(url_pack("coupon#verify"));
		}
	}
	elseif($act=='ajax_supplier_login')
	{
		$tmpl->display("inc/ajax_supplier_login.html");
	}
	elseif($act=='loginout')
	{
		unset($_SESSION['account_info']);
		app_redirect(url_pack("coupon#verify"));
	}
	elseif($act == 'supplier_dologin')
	{
		if(check_ipop_limit(get_client_ip(),"supplier_dologin",intval(app_conf("SUBMIT_DELAY"))))
		{
			$account_name = htmlspecialchars(addslashes($_REQUEST['account_name']));
			$account_password = htmlspecialchars(addslashes($_REQUEST['account_password']));
			$account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_account where account_name = '".$account_name."' and account_password = '".md5($account_password)."' and is_effect = 1 and is_delete = 0");
			if($account)
			{
				$_SESSION['account_info'] = $account;
				$result['status'] = 1;
				ajax_return($result);
			}
			else
			{
				$result['status'] = 0;
				$result['msg'] = $GLOBALS['lang']['SUPPLIER_LOGIN_FAILED'];
				ajax_return($result);
			}
		}
		else
		{
			$result['status'] = 0;
			$result['msg'] = $GLOBALS['lang']['SUBMIT_TOO_FAST'];
			ajax_return($result);
		}
	}
	elseif($act=='modify_pwd')
	{
		if(intval($_SESSION['account_info']['id'])==0)
		{
			showErr($GLOBALS['lang']['SUPPLIER_NOT_LOGIN'],0,url_pack("coupon#verify"));		
		}
		$account_id = intval($_SESSION['account_info']['id']);
		$account_data = $GLOBALS['db']->getRowCached("select s.name as name,a.account_name as account_name, a.supplier_id as supplier_id from ".DB_PREFIX."supplier_account as a left join ".DB_PREFIX."supplier as s on a.supplier_id = s.id where a.id = ".$account_id);
		$GLOBALS['tmpl']->assign("account_data",$account_data);
		
		$tmpl->assign("page_title",$GLOBALS['lang']['SUPPLIER_MODIFY_PWD']);
		$tmpl->display("supplier_password.html");
	}
	elseif($act=='do_modify_password')
	{
		if(intval($_SESSION['account_info']['id'])==0)
		{
			showErr($GLOBALS['lang']['SUPPLIER_NOT_LOGIN'],0,url_pack("coupon#verify"));		
		}
		
	
		$new_pwd = htmlspecialchars(addslashes(trim($_REQUEST['account_new_password'])));
		$GLOBALS['db']->query("update ".DB_PREFIX."supplier_account set account_password = '".md5($new_pwd)."' where id = ".intval($_SESSION['account_info']['id']));
		showSuccess($GLOBALS['lang']['PASSWORD_MODIFY_SUCCESS'],0,url_pack("coupon#verify"));	
	}
	elseif($act=='order')
	{
		if(intval($_SESSION['account_info']['id'])==0)
		{
			showErr($GLOBALS['lang']['SUPPLIER_NOT_LOGIN'],0,url_pack("coupon#supplier_login"));		
		}
		$account_id = intval($_SESSION['account_info']['id']);
		$account_data = $GLOBALS['db']->getRowCached("select s.name as name,a.account_name as account_name, a.supplier_id as supplier_id from ".DB_PREFIX."supplier_account as a left join ".DB_PREFIX."supplier as s on a.supplier_id = s.id where a.id = ".$account_id);
		$GLOBALS['tmpl']->assign("account_data",$account_data);
			
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SUPPLIER_ORDER_LIST']);
		
		$supplier_id = intval($GLOBALS['db']->getOneCached("select supplier_id from ".DB_PREFIX."supplier_account where id = ".intval($_SESSION['account_info']['id'])));
		$GLOBALS['tmpl']->assign("supplier_id",$supplier_id);
		
		$order_sn = htmlspecialchars(addslashes(trim($_REQUEST['order_sn'])));
		$coupon_sn = htmlspecialchars(addslashes(trim($_REQUEST['coupon_sn'])));
		
		$deal_id = intval($_REQUEST['id']);
		
		$ext_where = ' and do.is_delete = 0 and do.after_sale = 0';	
		if($order_sn != '')
		{
			$ext_where.= " and do.order_sn like '%".$order_sn."%' ";
			$GLOBALS['tmpl']->assign("order_sn",$order_sn);
		}
		if($coupon_sn != '')
		{
			$ext_where.= " and do.id in (select order_id from ".DB_PREFIX."deal_coupon where sn like '%".$coupon_sn."%')";
			$GLOBALS['tmpl']->assign("coupon_sn",$coupon_sn);
		}
		if($deal_id>0)
		{
			$ext_where.=" and doi.deal_id = ".$deal_id;
			$GLOBALS['tmpl']->assign("deal_id",$deal_id);
		}
		
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			
		$order_list_sql = "select do.id as oid,do.user_id,do.order_sn,do.create_time as do_time,doi.name,doi.sub_name,sum(doi.number) as number,d.* from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on do.id = doi.order_id left join ".DB_PREFIX."deal as d on doi.deal_id = d.id where do.pay_status = 2 and d.supplier_id = ".$supplier_id.$ext_where." group by do.id order by do.create_time desc limit ".$limit;
		$order_list_count_sql = "select count(distinct(do.id)) from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on do.id = doi.order_id left join ".DB_PREFIX."deal as d on doi.deal_id = d.id where do.pay_status = 2 and d.supplier_id = ".$supplier_id.$ext_where;
		$dddd = $GLOBALS['db']->getAll($order_list_sql);
		$GLOBALS['tmpl']->assign('order_list',$dddd);
	
		
		$order_count = $GLOBALS['db']->getOne($order_list_count_sql);
		$page = new Page($order_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->display("coupon_order.html");
	
	}
	elseif($act=='export_order')  //新增关于订单的下载
	{
		if(intval($_SESSION['account_info']['id'])==0)
		{
			showErr($GLOBALS['lang']['SUPPLIER_NOT_LOGIN'],0,url_pack("coupon#supplier_login"));		
		}
		$deal_id = intval($_REQUEST['id']);	
		export_order(1,$deal_id);
	}
	// 新增的关于订单的管理
	elseif($act=='deal')
	{
		if(intval($_SESSION['account_info']['id'])==0)
		{
			showErr($GLOBALS['lang']['SUPPLIER_NOT_LOGIN'],0,url_pack("coupon#verify"));		
		}
		$account_id = intval($_SESSION['account_info']['id']);
		$account_data = $GLOBALS['db']->getRowCached("select s.name as name,a.account_name as account_name, a.supplier_id as supplier_id from ".DB_PREFIX."supplier_account as a left join ".DB_PREFIX."supplier as s on a.supplier_id = s.id where a.id = ".$account_id);
		$GLOBALS['tmpl']->assign("account_data",$account_data);	
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SUPPLIER_DEAL_LIST']);
		//获取当前页的团购商品列表
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
	
		$supplier_id = intval($_SESSION['account_info']['supplier_id']);
		
		$deal_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal where is_delete = 0 and supplier_id = ".$supplier_id." order by sort desc limit ".$limit);
		foreach($deal_list as $k=>$v)
		{
			if($v['supplier_id']>0)
			$deal_list[$k]['supplier_name'] = $GLOBALS['db']->getOneCached("select name from ".DB_PREFIX."supplier where id = ".$v['supplier_id']);
			
			$sql = "select sum(doi.number) from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.deal_id = ".$v['id']." and do.pay_status = 2 and do.is_delete = 0 and do.after_sale = 0";
			$deal_list[$k]['sale_count'] = intval($GLOBALS['db']->getOne($sql));
			//$deal_list[$k]['sql'] = $sql;
			$deal_list[$k]['coupon_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon as dc where dc.deal_id = ".$v['id']." and dc.is_valid = 1 and dc.is_delete = 0 "));
			$deal_list[$k]['confirm_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon as dc where dc.deal_id = ".$v['id']." and dc.is_valid = 1 and dc.is_delete = 0 and dc.confirm_account <> 0"));
		}
		$GLOBALS['tmpl']->assign('deal_list',$deal_list);
		
		$deal_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where is_delete = 0 and supplier_id = ".$supplier_id);
		$page = new Page($deal_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->display("coupon_deal.html");
	
	}
	
	// 新增的关于团购券列表的管理
	elseif($act=='deal_coupon')
	{
		if(intval($_SESSION['account_info']['id'])==0)
		{
			showErr($GLOBALS['lang']['SUPPLIER_NOT_LOGIN'],0,url_pack("coupon#verify"));		
		}
		$account_id = intval($_SESSION['account_info']['id']);
		$account_data = $GLOBALS['db']->getRowCached("select s.name as name,a.account_name as account_name, a.supplier_id as supplier_id from ".DB_PREFIX."supplier_account as a left join ".DB_PREFIX."supplier as s on a.supplier_id = s.id where a.id = ".$account_id);
		$GLOBALS['tmpl']->assign("account_data",$account_data);	
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SUPPLIER_COUPON_LIST']);
		//获取当前页的团购商品列表
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
	
		$supplier_id = intval($_SESSION['account_info']['supplier_id']);
		$deal_id = intval($_REQUEST['id']);
		
		$coupon_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_coupon where deal_id = ".$deal_id." and is_valid = 1 and is_delete = 0 order by id desc limit ".$limit);
	
		$GLOBALS['tmpl']->assign('coupon_list',$coupon_list);
		
		$coupon_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon where deal_id = ".$deal_id." and is_valid = 1 and is_delete = 0");
		$page = new Page($coupon_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->display("coupon_deal_coupon.html");
	
	}
	elseif($act=='view')
	{
		if(intval($_SESSION['account_info']['id'])==0)
		{
			showErr($GLOBALS['lang']['SUPPLIER_NOT_LOGIN'],0,url_pack("coupon#supplier_login"));		
		}
		$account_id = intval($_SESSION['account_info']['id']);
		$account_data = $GLOBALS['db']->getRowCached("select s.name as name,a.account_name as account_name, a.supplier_id as supplier_id from ".DB_PREFIX."supplier_account as a left join ".DB_PREFIX."supplier as s on a.supplier_id = s.id where a.id = ".$account_id);
		$GLOBALS['tmpl']->assign("account_data",$account_data);
			
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SUPPLIER_ORDER_VIEW']);
		
		$supplier_id = intval($GLOBALS['db']->getOneCached("select supplier_id from ".DB_PREFIX."supplier_account where id = ".intval($_SESSION['account_info']['id'])));
		$GLOBALS['tmpl']->assign("supplier_id",$supplier_id);
		
		$order_id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getAll("select do.*,doi.name,doi.sub_name,doi.number,doi.delivery_status,doi.id as doiid from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal as d on doi.deal_id = d.id left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where do.id = ".$order_id." and d.supplier_id = ".$supplier_id." and do.is_delete = 0 and do.pay_status = 2");
		if($order_info)
		{
			$GLOBALS['tmpl']->assign("order_info",$order_info[0]);
			$GLOBALS['tmpl']->assign("order_goods",$order_info);
			$GLOBALS['tmpl']->display("coupon_view.html");
		}
		else
		{
			showErr($GLOBALS['lang']['SUPPLIER_NOT_MATCH'],0);
		}
	}
	elseif($act=='do_delivery')
	{
		if(intval($_SESSION['account_info']['id'])==0)
		{
			showErr($GLOBALS['lang']['SUPPLIER_NOT_LOGIN'],0,url_pack("coupon#supplier_login"));		
		}
		$account_id = intval($_SESSION['account_info']['id']);
		$account_data = $GLOBALS['db']->getRowCached("select a.allow_delivery,s.name as name,a.account_name as account_name, a.supplier_id as supplier_id from ".DB_PREFIX."supplier_account as a left join ".DB_PREFIX."supplier as s on a.supplier_id = s.id where a.id = ".$account_id);
		if(intval($account_data['allow_delivery'])==0)
		{
			showErr($GLOBALS['lang']['NO_DELIVERY_AUTH']);		
		}
		
		$GLOBALS['tmpl']->assign("account_data",$account_data);
			
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SUPPLIER_ORDER_DELIVERY']);
		
		$supplier_id = intval($GLOBALS['db']->getOneCached("select supplier_id from ".DB_PREFIX."supplier_account where id = ".intval($_SESSION['account_info']['id'])));
		$GLOBALS['tmpl']->assign("supplier_id",$supplier_id);
		
		$order_id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getAll("select do.*,doi.name,doi.sub_name,doi.number,doi.delivery_status,doi.id as doiid from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal as d on doi.deal_id = d.id left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where do.id = ".$order_id." and d.supplier_id = ".$supplier_id." and do.is_delete = 0 and do.pay_status = 2 and d.is_delivery = 1");
		if($order_info)
		{
			$GLOBALS['tmpl']->assign("order_info",$order_info[0]);
			$GLOBALS['tmpl']->assign("order_goods",$order_info);
			$GLOBALS['tmpl']->display("coupon_do_delivery.html");
		}
		else
		{
			showErr($GLOBALS['lang']['SUPPLIER_NOT_MATCH'],0);
		}
	}
	elseif($act=='do_delivery_form')
	{
		
		if(intval($_SESSION['account_info']['id'])==0)
		{
			showErr($GLOBALS['lang']['SUPPLIER_NOT_LOGIN'],0,url_pack("coupon#supplier_login"));		
		}
		$account_id = intval($_SESSION['account_info']['id']);
		$account_data = $GLOBALS['db']->getRowCached("select a.allow_delivery,s.name as name,a.account_name as account_name, a.supplier_id as supplier_id from ".DB_PREFIX."supplier_account as a left join ".DB_PREFIX."supplier as s on a.supplier_id = s.id where a.id = ".$account_id);
		if(intval($account_data['allow_delivery'])==0)
		{
			showErr($GLOBALS['lang']['NO_DELIVERY_AUTH']);		
		}
		
		
		$order_id = intval($_REQUEST['order_id']);
		$order_deals = $_REQUEST['order_deals'];
		$delivery_sn = htmlspecialchars(addslashes($_REQUEST['delivery_sn']));
		$memo = htmlspecialchars(addslashes($_REQUEST['memo']));
			if(!$order_deals)
			{
				showErr($GLOBALS['lang']["PLEASE_SELECT_DELIVERY_ITEM"]);
			}
			else
			{
				$deal_names = array();
				foreach($order_deals as $order_deal_id)
				{
					$order_deal_id = intval($order_deal_id);
					$deal_name =$GLOBALS['db']->getOneCached("select d.sub_name from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_order_item as doi on doi.deal_id = d.id where doi.id = ".$order_deal_id);
					array_push($deal_names,$deal_name);
					$rs = make_delivery_notice($order_id,$order_deal_id,$delivery_sn,$memo,$express_id);
					if($rs)
					{
						$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set delivery_status = 1 where id = ".$order_deal_id);
					}
				}
				$deal_names = implode(",",$deal_names);
				
				send_delivery_mail($delivery_sn,$deal_names,$order_id);
				send_delivery_sms($delivery_sn,$deal_names,$order_id);
				//开始同步订单的发货状态
				$order_deal_items = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
				foreach($order_deal_items as $k=>$v)
				{
					
					if(intval($GLOBALS['db']->getOne("select is_delivery from ".DB_PREFIX."deal where id = ".$v['deal_id']))==0) //无需发货的商品
					{
						unset($order_deal_items[$k]);
					}				
				}
				$delivery_deal_items = $order_deal_items;
				foreach($delivery_deal_items as $k=>$v)
				{
					if($v['delivery_status']==0) //未发货去除
					{
						unset($delivery_deal_items[$k]);
					}				 
				}
				
	
				if(count($delivery_deal_items)==0&&count($order_deal_items)!=0)
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 0 where id = ".$order_id); //未发货
				}
				elseif(count($delivery_deal_items)>0&&count($order_deal_items)!=0&&count($delivery_deal_items)<count($order_deal_items))
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 1 where id = ".$order_id); //部分发
				}
				else
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 2 where id = ".$order_id); //全部发
				}		
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set update_time = '".get_gmtime()."' where id = ".$order_id);
				
				
				order_log($account_data['name'].$account_data['account_name'].":".$GLOBALS['lang']["DELIVERY_SUCCESS"].$delivery_sn.$_REQUEST['memo'],$order_id);
				
				showSuccess($GLOBALS['lang']["DELIVERY_SUCCESS"]);
			}
	}
	
	else
	showErr($GLOBALS['lang']['INVALID_ACCESS'],0,APP_ROOT);
}


?>