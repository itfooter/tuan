<?php

define("DEAL_ONLINE",1); //进行中
define("DEAL_HISTORY",2); //过期
define("DEAL_NOTICE",3); //未上线


/**
 * 获取指定的团购产品
 */
function get_deal($id=0,$cate_id=0,$city_id=0)
{
		$time = get_gmtime();
		if($id>0)
		$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".intval($id)." and is_effect = 1 and is_delete = 0 and (".$time.">= begin_time or begin_time = 0 or notice = 1) ");
		if(!$deal)
		{
			$sql = "select * from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 and buy_type <> 1 and (".$time.">= begin_time or begin_time = 0 or notice = 1) and (".$time."<end_time or end_time = 0) and buy_status <> 2 ";
			if($cate_id>0)
			{

				$ids = $GLOBALS['cache']->get("DEAL_CATE_BELONE_IDS_".$cate_id);
				if($ids === false)
				{
					$ids_util = new ChildIds("deal_cate");
					$ids = $ids_util->getChildIds($cate_id);
					$ids[] = $cate_id;

					//开始取出父分类ID
					$r_cate_id = $cate_id;
					while($r_cate_id!=0){
						$r_cate_id = $GLOBALS['db']->getOne("select pid from ".DB_PREFIX."deal_cate where id = ".$r_cate_id);
						if($r_cate_id!=0)
						$ids[] = $r_cate_id;
					}
					$GLOBALS['cache']->set("DEAL_CATE_BELONE_IDS_".$cate_id,$ids);
				}

				$sql .= " and cate_id in (".implode(",",$ids).")";
			}
			if($city_id==0)
			{
				$city = get_current_deal_city();
				$city_id = $city['id'];
			}
			if($city_id>0)
			{
				$ids = $GLOBALS['cache']->get("DEAL_CITY_BELONE_IDS_".$city_id);
				if($ids===false)
				{
					$ids_util = new ChildIds("deal_city");
					$ids = $ids_util->getChildIds($city_id);
					$ids[] = $city_id;
					//开始取出父地区ID
					$r_city_id = $city_id;
					while($r_city_id!=0){
						$r_city_id = $GLOBALS['db']->getOneCached("select pid from ".DB_PREFIX."deal_city where id = ".$r_city_id);
						if($r_city_id!=0)
						$ids[] = $r_city_id;
					}
					$GLOBALS['cache']->get("DEAL_CITY_BELONE_IDS_".$city_id,$ids);
				}
				$sql .= " and city_id in (".implode(",",$ids).")";
			}
			$sql.=" order by sort desc";
			$deal = $GLOBALS['db']->getRow($sql);

		}

		if($deal)
		{
			if($deal['time_status']==0 && $deal['begin_time']==0 || $deal['begin_time']<get_gmtime())
			{
				syn_deal_status($deal['id']);
				$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$deal['id']." and is_effect = 1 and is_delete = 0");
			}

			//格式化数据
			$deal['begin_time_format'] = to_date($deal['begin_time']);
			$deal['end_time_format'] = to_date($deal['end_time']);
			$deal['origin_price_format'] = format_price($deal['origin_price']);
			$deal['current_price_format'] = format_price($deal['current_price']);
			$deal['success_time_format']  = to_date($deal['success_time']);

			if($deal['origin_price']>0&&floatval($deal['discount'])==0) //手动折扣
			$deal['save_price'] = $deal['origin_price'] - $deal['current_price'];
			else
			$deal['save_price'] = $deal['origin_price']*((10-$deal['discount'])/10);

			if($deal['origin_price']>0&&floatval($deal['discount'])==0)
			$deal['discount'] = round(($deal['current_price']/$deal['origin_price'])*10,2);

			$deal['discount'] = round($deal['discount'],2);

			$deal['save_price_format'] = format_price($deal['save_price']);

				$deal['deal_success_num'] = sprintf($GLOBALS['lang']['SUCCESS_BUY_COUNT'],$deal['buy_count']);
				$deal['current_bought'] = $deal['buy_count'];
				if($deal['buy_status']==0) //未成功
				{
					$deal['success_less'] = sprintf($GLOBALS['lang']['SUCCESS_LESS_BUY_COUNT'],$deal['min_bought'] - $deal['buy_count']);
				}


			$deal['success_time_tip'] = sprintf($GLOBALS['lang']['SUCCESS_TIME_TIP'],$deal['success_time_format'],$deal['min_bought']);

			//团购图片集
			$img_list = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."deal_gallery where deal_id=".intval($deal['id'])." order by sort asc");
			$deal['image_list'] = $img_list;

			//商户信息
			$deal['supplier_info'] = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."supplier where id = ".intval($deal['supplier_id']));
			$supplier_address_info=$GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."supplier_location where supplier_id = ".intval($deal['supplier_id'])." and is_main = 1");
			$url_b="&wd=".$supplier_address_info['api_address']."&c=131&src=0&wd2=&sug=0";
			$url_b=urlencode($url_b);						
			$supplier_address_info['map']="http://map.baidu.com/?newmap=1&l=18&c=".$supplier_address_info['xpoint'].",".$supplier_address_info['ypoint']."&s=s".$url_b."&sc=0";
			$deal['supplier_address_info'] = $supplier_address_info;


			//属性列表
			$deal_attrs_res = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."deal_attr where deal_id = ".intval($deal['id'])." order by id asc");
			if($deal_attrs_res)
			{
				foreach($deal_attrs_res as $k=>$v)
				{
					$deal_attr[$v['goods_type_attr_id']]['name'] = $GLOBALS['db']->getOneCached("select name from ".DB_PREFIX."goods_type_attr where id = ".intval($v['goods_type_attr_id']));
					$deal_attr[$v['goods_type_attr_id']]['attrs'][] = $v;
				}
				$deal['deal_attr_list'] = $deal_attr;
			}


			$deal['share_url'] = get_domain().url_pack("deal",$deal['id'],$deal['uname']);
			if($GLOBALS['user_info'])
			{
				if(app_conf("URL_MODEL")==0)
				{
					$deal['share_url'] .= "&r=".base64_encode(intval($GLOBALS['user_info']['id']));
                                        $deal['share_url'] = $deal['share_url'];
				}
				else
				{
					$deal['share_url'] .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
                                        $deal['share_url'] = urlencode($deal['share_url']);
				}
			}

			//查询抽奖号
			$deal['lottery_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."lottery where deal_id = ".intval($deal['id'])." and buyer_id <> 0 ")) + intval($deal['buy_count']);
		}
		return $deal;

}


/**
 * 获取正在团购的产品列表
 */
function get_deal_list($limit,$cate_id=0,$city_id=0, $type=array(DEAL_ONLINE,DEAL_HISTORY,DEAL_NOTICE), $where='',$orderby = '',$area_id=0)
{

		$time = get_gmtime();
		$time_condition = ' and ( 1<>1 ';
		if(in_array(DEAL_ONLINE,$type))
		{

			//进行中的团购
			$time_condition .= " or ((".$time.">= begin_time or begin_time = 0) and (".$time."<end_time or end_time = 0) and buy_status <> 2) ";
		}
		if(in_array(DEAL_HISTORY,$type))
		{
			//往期团购
			$time_condition .= " or ((".$time.">=end_time and end_time <> 0) or buy_status = 2) ";
		}
		if(in_array(DEAL_NOTICE,$type))
		{
			//预告
			$time_condition .= " or ((".$time." < begin_time and begin_time <> 0 and notice = 1)) ";
		}

		$time_condition .= ')';

			$count_sql = "select count(*) from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 ".$time_condition;
			$sql = "select * from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0 ".$time_condition;
			if($cate_id>0)
			{
				$ids = $GLOBALS['cache']->get("DEAL_CATE_BELONE_IDS_".$cate_id);
				if($ids === false)
				{
					$ids_util = new ChildIds("deal_cate");
					$ids = $ids_util->getChildIds($cate_id);
					$ids[] = $cate_id;

					//开始取出父分类ID
					$r_cate_id = $cate_id;
					while($r_cate_id!=0){
						$r_cate_id = $GLOBALS['db']->getOne("select pid from ".DB_PREFIX."deal_cate where id = ".$r_cate_id);
						if($r_cate_id!=0)
						$ids[] = $r_cate_id;
					}

					$GLOBALS['cache']->set("DEAL_CATE_BELONE_IDS_".$cate_id,$ids);
				}
				$sql .= " and cate_id in (".implode(",",$ids).")";
				$count_sql .= " and cate_id in (".implode(",",$ids).")";
			}
			if($city_id==0)
			{
				$city = get_current_deal_city();
				$city_id = $city['id'];
			}
			if($city_id>0)
			{
				$ids = $GLOBALS['cache']->get("DEAL_CITY_BELONE_IDS_".$city_id);
				if($ids===false)
				{
					$ids_util = new ChildIds("deal_city");
					$ids = $ids_util->getChildIds($city_id);
					$ids[] = $city_id;
					//开始取出父地区ID
					$r_city_id = $city_id;
					while($r_city_id!=0){
						$r_city_id = $GLOBALS['db']->getOneCached("select pid from ".DB_PREFIX."deal_city where id = ".$r_city_id);
						if($r_city_id!=0)
						$ids[] = $r_city_id;
					}
					$GLOBALS['cache']->set("DEAL_CITY_BELONE_IDS_".$city_id,$ids);
				}

				$sql .= " and city_id in (".implode(",",$ids).")";
				$count_sql .= " and city_id in (".implode(",",$ids).")";
			}
			
			if($area_id>0){
				$ids = $GLOBALS['cache']->get("DEAL_AREA_BELONE_IDS_".$area_id);
				if($ids === false)
				{
					$ids_util = new ChildIds("deal_city_area");
					$ids = $ids_util->getChildIds($area_id);
					$ids[] = $area_id;

					//开始取出父分类ID
					$r_area_id = $area_id;
					while($r_area_id!=0){
						$r_area_id = $GLOBALS['db']->getOne("select pid from ".DB_PREFIX."deal_city_area where id = ".$r_area_id);
						if($r_area_id!=0)
						$ids[] = $r_area_id;
					}

					$GLOBALS['cache']->set("DEAL_AREA_BELONE_IDS_".$area_id,$ids);
				}
				$sql .= " and area_id in (".implode(",",$ids).")";
				$count_sql .= " and area_id in (".implode(",",$ids).")";
			}

		if($where != '')
		{
			$sql.=" and ".$where;
			$count_sql.=" and ".$where;
		}

		if($orderby=='')
		$sql.=" order by sort desc limit ".$limit;
		else
		$sql.=" order by ".$orderby." limit ".$limit;

		$deals = $GLOBALS['db']->getAll($sql);
		$deals_count = $GLOBALS['db']->getOne($count_sql);

 		if($deals)
		{
			foreach($deals as $k=>$deal)
			{
				//团购图片集
				$img_list = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."deal_gallery where deal_id=".intval($deal['id'])." order by sort asc");
				$deal['image_list'] = $img_list;

				//格式化数据
				$deal['begin_time_format'] = to_date($deal['begin_time']);
				$deal['end_time_format'] = to_date($deal['end_time']);
				$deal['origin_price_format'] = format_price($deal['origin_price']);
				$deal['current_price_format'] = format_price($deal['current_price']);
				$deal['success_time_format']  = to_date($deal['success_time']);

				if($deal['origin_price']>0&&floatval($deal['discount'])==0) //手动折扣
				$deal['save_price'] = $deal['origin_price'] - $deal['current_price'];
				else
				$deal['save_price'] = $deal['origin_price']*((10-$deal['discount'])/10);
				if($deal['origin_price']>0&&floatval($deal['discount'])==0)
				{
					$deal['discount'] = round(($deal['current_price']/$deal['origin_price'])*10,2);
				}

				$deal['discount'] = round($deal['discount'],2);

				$deal['share_url'] = get_domain().url_pack("deal",$deal['id'],$deal['uname']);


					if($GLOBALS['user_info'])
					{
						if(app_conf("URL_MODEL")==0)
						{
							$deal['share_url'] .= "&r=".base64_encode(intval($GLOBALS['user_info']['id']));
                                                        $deal['share_url'] = urlencode($deal['share_url']);
						}
						else
						{
							$deal['share_url'] .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
                                                        $deal['share_url'] = urlencode($deal['share_url']);

                                                }
					}



				$deal['save_price_format'] = format_price($deal['save_price']);
				$deal['url'] = url_pack("deal",$deal['id'],$deal['uname']);
				$deal['deal_success_num'] = sprintf($GLOBALS['lang']['SUCCESS_BUY_COUNT'],$deal['buy_count']);
				$deal['current_bought'] = $deal['buy_count'];
				//查询抽奖号
				$deal['lottery_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."lottery where deal_id = ".intval($deal['id'])." and buyer_id <> 0 ")) + intval($deal['buy_count']);
				if($deal['buy_status']==0) //未成功
				{
					$deal['success_less'] = sprintf($GLOBALS['lang']['SUCCESS_LESS_BUY_COUNT'],$deal['min_bought'] - $deal['buy_count']);
				}
				$deals[$k] = $deal;
			}
		}
		return array('list'=>$deals,'count'=>$deals_count);
}

//获取团购分类
function get_deal_cate($name="cate_list_index",$city_id=0,$area_id=0,$show_count = false,$exe="")
{
	if($city_id==0)
	{
		$city = get_current_deal_city();
		$city_id = $city['id'];
	}
	
	$cate_list = $GLOBALS['cache']->get($name.intval($_REQUEST['id'])."_".$area_id."_".$city_id."_".$exe);	
	if($cate_list===false)
	{
		$cate_list = $GLOBALS['db']->getAll("select id,name,uname from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 {$exe} order by sort desc");
		foreach($cate_list as $k=>$v)
		{			
				if($show_count)
				{
					$cate_deal_list_rs = get_deal_list(1,$v['id'],$city_id, $type=array(DEAL_ONLINE,DEAL_NOTICE), $where='buy_type<>1',$orderby = '',$area_id);	
					$cate_list[$k]['count'] = $cate_deal_list_rs['count'];
				}			
				if($v['uname']!='')
				$cate_list[$k]['url'] = APP_ROOT."/index.php?id=".$v['uname']."&area_id=".$area_id;
				else
				$cate_list[$k]['url'] = APP_ROOT."/index.php?id=".$v['id']."&area_id=".$area_id;
		}
		$GLOBALS['cache']->set($name.intval($_REQUEST['id'])."_".$area_id."_".$city_id."_".$exe,$cate_list);
	}
	return $cate_list;
}
function get_deal_area($name="area_list_index",$city_id=0,$cate_id=0,$show_count = false,$exe="")
{
	if($city_id==0)
		{
			$city = get_current_deal_city();
			$city_id = $city['id'];
		}
	$area_list = $GLOBALS['cache']->get($name.intval($_REQUEST['area_id'])."_".$city_id."_".$cate_id."_".$exe);
	if($area_list===false)
	{
		$area_list = $GLOBALS['db']->getAll("select id,name,uname from ".DB_PREFIX."deal_city_area where is_delete = 0 and is_effect = 1 and city_id=".$city_id." $exe order by sort desc");
		foreach($area_list as $k=>$v)
		{			
				$area_deal_list_rs = get_deal_list(1,$cate_id,$city_id, $type=array(DEAL_ONLINE,DEAL_NOTICE), $where='buy_type<>1',$orderby = '',$v['id']);	
				$area_list[$k]['count'] = $area_deal_list_rs['count'];
				
				if($v['uname']!='')
				$area_list[$k]['url'] = APP_ROOT."/index.php?area_id=".$v['uname']."&id=".$cate_id;
				else
				$area_list[$k]['url'] = APP_ROOT."/index.php?area_id=".$v['id']."&id=".$cate_id;
		}
		$GLOBALS['cache']->set($name.intval($_REQUEST['area_id'])."_".$city_id."_".$cate_id."_".$exe,$area_list);
	}
	return $area_list;
}
?>