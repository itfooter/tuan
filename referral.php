<?php 
require './system/common.php';
require './app/Lib/app_init.php';
require './app/Lib/deal.php';
require './app/Lib/message.php';
require './app/Lib/side.php'; 

/**
 * 获取指定的团购产品
 */
function get_referral_deal($id=0,$cate_id=0,$city_id=0)
{		
		$time = get_gmtime();
		if($id>0)
		$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".intval($id)." and is_referral = 1 and is_effect = 1 and is_delete = 0 and (".$time.">= begin_time or begin_time = 0 or notice = 1) ");
		if(!$deal)
		{			
			$sql = "select * from ".DB_PREFIX."deal where is_referral = 1 and is_effect = 1 and is_delete = 0 and buy_type <> 1 and (".$time.">= begin_time or begin_time = 0 or notice = 1) and (".$time."<end_time or end_time = 0) and buy_status <> 2 ";
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
				$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where is_referral = 1 and id = ".$deal['id']." and is_effect = 1 and is_delete = 0");
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
			$deal['supplier_address_info'] = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."supplier_location where supplier_id = ".intval($deal['supplier_id'])." and is_main = 1");

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
			
			$deal['share_url'] = get_domain().url_pack("deal",$deal['id']);
			if($GLOBALS['user_info'])
			{
				if(app_conf("URL_MODEL")==0)
				{
					$deal['share_url'] .= "&r=".base64_encode(intval($GLOBALS['user_info']['id']));
				}
				else
				{
					$deal['share_url'] .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
				}
			}
		}
		return $deal;
	
}

$id = intval($_REQUEST['id']);
$deal = get_referral_deal($id);
if(!$deal||$deal['buy_type']==1||$deal['is_referral']==0)
{
	app_redirect(APP_ROOT."/");
}

$_SESSION['before_login'] = $_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:url_pack("deal");
$GLOBALS['tmpl']->assign("deal",$deal);
$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['REFERRAL_PAGE']);
$GLOBALS['tmpl']->display("referral.html");
?>