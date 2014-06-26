<?php

//app项目用到的函数库

/**
 *  获取团购城市列表
 */
function get_deal_citys()
{
	$city_list_result = $GLOBALS['cache']->get("city_list_result");
	if($city_list_result === false)
	{
		$city_list = $GLOBALS['db']->getAllCached("select id,name,is_open,left(uname,1) as zm from ".DB_PREFIX."deal_city where is_effect = 1 and is_delete = 0  order by left(uname,1) asc,sort desc");
		$city_zm_list = array();
		foreach($city_list as $k=>$v)
		{			
			$v['url'] = url_pack("deal_city",$v['id']);
			$v['zm'] = strtoupper($v['zm']);
			$city_zm_list[strtoupper($v['zm'])][] = $v;
			$city_list[$k]['url'] = $v['url'];
		}
		$city_list_result = array('zm'=>$city_zm_list,'ls'=>$city_list);
		$GLOBALS['cache']->set("city_list_result",$city_list_result);
	}
	return $city_list_result;	
}
function show_ke_image($id,$cnt="")
{
	if($cnt)
	{
		$image_path = $cnt;
		$is_show="display:inline-block;";
		$script = "onclick='window.open(this.src);'";
	}
	else{
		$image_path =APP_ROOT."/admin/Tpl/default/Common/images/no_pic.gif";
		$is_show="display:none;";
	}
	return	"<div style='width:120px; height:40px; margin-left:10px; display:inline-block;  float:left;' class='none_border'>
							<script type='text/javascript'>var eid = '".$id."';KE.show({urlType:'domain', id:eid, items : ['upload_image'],skinType: 'tinymce',allowFileManager : false,resizeMode : 0});</script>
							<div style='font-size:0px;'>
							<textarea id='".$id."' name='".$id."' style='width:125px; height:25px;' >".$cnt."</textarea> 
							<input type='text' id='focus_".$id."' style='font-size:0px; border:0px; padding:0px; margin:0px; line-height:0px; width:0px; height:0px;' />
							</div>
						</div>
						<img src='".$image_path."' $script  style='display:inline-block; float:left; cursor:pointer; margin-left:10px; border:#ccc solid 1px; width:35px; height:35px;' id='img_".$id."' />
						<img src='".APP_ROOT."/admin/Tpl/default/Common/images/del.gif' style='".$is_show." margin-left:10px; float:left; border:#ccc solid 1px; width:35px; height:35px; cursor:pointer;' id='img_del_".$id."' onclick='delimg(\"".$id."\")' title='删除' />";
						
}
function replace_public($content)
{
	 $domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?get_domain().APP_ROOT:app_conf("PUBLIC_DOMAIN_ROOT");
	 $domain_origin = get_domain().APP_ROOT;
	 $content = str_replace($domain."/public/","./public/",$content);	
	 $content = str_replace($domain_origin."/public/","./public/",$content);		 
	 return $content;
}
function valid_tag($str)
{
	
	return preg_replace("/<(?!div|ol|ul|li|sup|sub|span|br|img|p|h1|h2|h3|h4|h5|h6|\/div|\/ol|\/ul|\/li|\/sup|\/sub|\/span|\/br|\/img|\/p|\/h1|\/h2|\/h3|\/h4|\/h5|\/h6|blockquote|\/blockquote|strike|\/strike|b|\/b|i|\/i|u|\/u)[^>]*>/i","",$str);
}
function show_ke_textarea($id,$width=550,$height=350,$cnt="")
{	
	return "<script type='text/javascript'> var eid = '".$id."';KE.show({urlType:'domain', id:eid, items : ['fsource', 'image', 'justifyleft', 'justifycenter', 'justifyright','justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript','superscript', 'selectall', 'textcolor', 'bold','italic', 'underline', 'strikethrough', 'fullscreen','-','title', 'fontname', 'fontsize'], skinType: 'tinymce',allowFileManager : false,resizeMode : 0, newlineTag:'nl'});</script><div  style='margin-bottom:5px; '><textarea id='".$id."' name='".$id."' style='width:".$width."px; height:".$height."px;' >".$cnt."</textarea> </div>";
}
/**
 * 获取当前团购城市
 */
function get_current_deal_city()
{		
	if(es_cookie::is_set("deal_city"))
	{	
		$deal_city_id = es_cookie::get("deal_city");
		$deal_city = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_city where is_effect = 1 and is_delete = 0 and id = ".intval($deal_city_id));
	}
	else
	{
		//设置如存在的IP订位
		if(file_exists(APP_ROOT_PATH."system/extend/ip.php"))
		{			
			require_once APP_ROOT_PATH."system/extend/ip.php";
			$ip =  get_client_ip();
			$iplocation = new iplocate();
			$address=$iplocation->getaddress($ip);
			$city_list = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."deal_city where is_effect = 1 and is_delete = 0");
			foreach ($city_list as $city)
			{
				if(strpos($address['area1'],$city['name']))
				{
					$deal_city = $city;
					break;
				}
			}
		}
		if(!$deal_city)
		$deal_city = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_city where is_default = 1 and is_effect = 1 and is_delete = 0");
	}
	return $deal_city;
}

/**
 * 获取页面的标题，关键词与描述
 */
function get_shop_info()
{
	if($GLOBALS['city_name'])
	{
		$shop_info['SHOP_TITLE']	=	$GLOBALS['deal_city']['seo_title']==''?app_conf('SHOP_TITLE'):$GLOBALS['deal_city']['seo_title'];
		$shop_info['SHOP_KEYWORD']	=	$GLOBALS['deal_city']['seo_keyword']==''?app_conf('SHOP_KEYWORD'):$GLOBALS['deal_city']['seo_keyword'];
		$shop_info['SHOP_DESCRIPTION']	= $GLOBALS['deal_city']['seo_description']==''?app_conf('SHOP_DESCRIPTION'):$GLOBALS['deal_city']['seo_description'];
	}
	else
	{
		$shop_info['SHOP_TITLE']	=	app_conf('SHOP_TITLE');
		$shop_info['SHOP_KEYWORD']	=	app_conf('SHOP_KEYWORD');
		$shop_info['SHOP_DESCRIPTION']	=	app_conf('SHOP_DESCRIPTION');
	}

	return $shop_info;
}

/**
 * 获取导航菜单
 */
function get_nav_list()
{
	$nav_list = $GLOBALS['cache']->get("CACHE_NAV_LIST");
	if($nav_list === false)
	{
		$nav_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."nav where is_effect = 1 order by sort desc");
		$GLOBALS['cache']->set("CACHE_NAV_LIST",$nav_list);
	}
	return $nav_list;
}

function get_help()
{
	$help_list = $GLOBALS['cache']->get("HELP_RESULT");
	if($help_list===false)
	{
		$ids_util = new ChildIds("article_cate");
		$help_list = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."article_cate where type_id = 1 and is_delete = 0 order by sort desc limit ".intval(app_conf("HELP_CATE_LIMIT")));
		foreach($help_list as $k=>$v)
		{
			$ids = $GLOBALS['cache']->get("CACHE_HELP_ARTICLE_CATE_".$v['id']);
			if($ids===false)
			{
				$ids = $ids_util->getChildIds($v['id']);
				$ids[] = $v['id'];
				$GLOBALS['cache']->set("CACHE_HELP_ARTICLE_CATE_".$v['id'],$ids);
			}
			$help_cate_list = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."article where cate_id in (".implode(",",$ids).") and is_delete = 0 order by sort desc limit ".intval(app_conf("HELP_ITEM_LIMIT")));
			foreach($help_cate_list as $kk=>$vv)
			{
				if($vv['rel_url']!='')
				{
					if(!preg_match ("/http:\/\//i", $vv['rel_url']))
					{
						if(substr($vv['rel_url'],0,2)=='u:')
						{
							$help_cate_list[$kk]['url'] = url_pack(substr($vv['rel_url'],2));
						}
						else
						$help_cate_list[$kk]['url'] = APP_ROOT."/".$vv['rel_url'];
					}
					else
					$help_cate_list[$kk]['url'] = $vv['rel_url'];
					
					$help_cate_list[$kk]['new'] = 1;
				}
				else
				$help_cate_list[$kk]['url'] = url_pack("article",$vv['id'],$vv['uname']);
			}
			$help_list[$k]['help_list'] = $help_cate_list;
		}
		$GLOBALS['cache']->set("HELP_RESULT",$help_list);
	}
	return $help_list;
}
//封装url
function url_pack($url,$id = 0,$uname='')
{
	$show_city = intval($GLOBALS['city_count'])>1?true:false;  //有多个城市时显示城市名称到url
	
	$arr = explode("#",$url);
	$module = trim($arr[0]);
	$action = trim($arr[1]);
        $action = htmlspecialchars(strip_tags($action));
	if($module == 'deal_city')
	{
		$deal_city = $GLOBALS['db']->getRowCached("select id,name,uname from ".DB_PREFIX."deal_city where id=".$id." and is_delete = 0 and is_effect = 1");
		if(app_conf("URL_MODEL")==0)
		{
			//原始
			$url = APP_ROOT."/index.php?city=".$deal_city['uname'];
		}
		else
		{
			//重写
			$url = APP_ROOT."/".$deal_city['uname'];
		}
		return $url;
	}	
	else
	{
//		if($module=='deal')
//		{
//			$deal_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal where id = ".$id);
//			
//			if($deal_info['buy_type']==1)
//			{
//				$module = "score";
//				$action = "show";
//			}
//			else
//			{
//				$uname = $deal_info['uname'];
//			}			
//		}
//		if($module=='deals'||$module=='index')
//		{
//			$uname = $GLOBALS['db']->getOneCached("select uname from ".DB_PREFIX."deal_cate where id = ".$id);
//		}
//		if($module=='article')
//		{
//			$uname = $GLOBALS['db']->getOneCached("select uname from ".DB_PREFIX."article where id = ".$id);
//		}
		if(app_conf("URL_MODEL")==0)
		{
			//原始			
			$url = APP_ROOT."/".$module.".php?";
			if($show_city)
			{
				$city_uname = $GLOBALS['deal_city']['uname'];
				$url = $url."city=".$city_uname."&";
			}			
			
			if($action&&$action!='')
			$url .= "act=".$action."&";
			if(intval($id)!=0&&$uname=='')
			$url .= "id=".$id."&";
			else
			if($uname!='')
			$url.="id=".$uname."&";
		}
		else
		{
			//重写
			if($show_city)
			{
				$city_uname = $GLOBALS['deal_city']['uname'];
				$url = APP_ROOT."/".$city_uname;
			}
			else
			{
				$url = APP_ROOT;
			}
			if($module!='index')
			$url = $url."/".$module;
			else 
			{
				if(!$show_city)
				$url = $url."/";
			}
			if($action&&$action!=''&&$action!='index')
			$url .= "/".$action;
			if(intval($id)!=0&&$uname=='')
			{
				if($module!='index'||$show_city)
				$url .= "/".$id;
				else
				$url.=$id;
			}
			elseif($uname!='')
			{
				if($module!='index'||$show_city)
				$url .= "/".$uname;
				else
				$url.=$uname;				
			}
		}
		return $url;
	}
}
/*
 * $name 表示参数名称
 * $form 获取的方式 post ,get 或者 request
 * $used 目的html 表示进行html字符处理, mysql 表示进行mysql_real_escape_string处理
 */
 function get_request($name='',$form='request',$used='html'){
 	switch($form){
 		case 'post':
 		if(isset($_POST[$name])){
 			if($used=='html'){
 				return htmlspecialchars(addslashes(trim($_POST[$name])));
 			}else{
 				return mysql_real_escape_string($_POST[$name]);
 			}
 		}
 		case 'get':
 		if(isset($_GET[$name])){
 			if($used=='html'){
 				return htmlspecialchars(addslashes(trim($_GET[$name])));
 			}else{
 				return mysql_real_escape_string($_GET[$name]);
 			}
 		}
 		case 'request':
 		if(isset($_REQUEST[$name])){
 			if($used=='html'){
 				return htmlspecialchars(addslashes(trim($_REQUEST[$name])));
 			}else{
 				return mysql_real_escape_string($_REQUEST[$name]);
 			}
 		}
 	}
 }

//获取所有子集的类
class ChildIds
{
	public function __construct($tb_name)
	{
		$this->tb_name = $tb_name;	
	}
	private $tb_name;
	private $childIds;
	private function _getChildIds($pid = '0', $pk_str='id' , $pid_str ='pid')
	{
		$childItem_arr = $GLOBALS['db']->getAllCached("select id from ".DB_PREFIX.$this->tb_name." where ".$pid_str."=".intval($pid));
		if($childItem_arr)
		{
			foreach($childItem_arr as $childItem)
			{
				$this->childIds[] = $childItem[$pk_str];
				$this->_getChildIds($childItem[$pk_str],$pk_str,$pid_str);
			}
		}
	}
	public function getChildIds($pid = '0', $pk_str='id' , $pid_str ='pid')
	{
		$this->childIds = array();
		$this->_getChildIds($pid,$pk_str,$pid_str);
		return $this->childIds;
	}
}

//显示错误
function showErr($msg,$ajax=0,$jump='',$stay=0)
{
	if($ajax==1)
	{
		$result['status'] = 0;
		$result['info'] = $msg;
		header("Content-Type:text/html; charset=utf-8");
        echo(json_encode($result));exit;
	}
	else
	{
		$GLOBALS['tmpl']->assign('page_title',$GLOBALS['lang']['ERROR_TITLE']." - ".$msg);
		$GLOBALS['tmpl']->assign('msg',$msg);
		if($jump=='')
		{
			$jump = $_SERVER['HTTP_REFERER'];
		}
		if(!$jump&&$jump=='')
		$jump = APP_ROOT."/";
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->assign("stay",$stay);
		$GLOBALS['tmpl']->display("error.html");
		exit;
	}
}

//显示成功
function showSuccess($msg,$ajax=0,$jump='',$stay=0)
{
	if($ajax==1)
	{
		$result['status'] = 1;
		$result['info'] = $msg;
		header("Content-Type:text/html; charset=utf-8");
        echo(json_encode($result));exit;
	}
	else
	{
		$GLOBALS['tmpl']->assign('page_title',$GLOBALS['lang']['SUCCESS_TITLE']." - ".$msg);
		$GLOBALS['tmpl']->assign('msg',$msg);
		if($jump=='')
		{
			$jump = $_SERVER['HTTP_REFERER'];
		}
		if(!$jump&&$jump=='')
		$jump = APP_ROOT."/";
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->assign("stay",$stay);
		$GLOBALS['tmpl']->display("success.html");
		exit;
	}
}

/*ajax返回*/
function ajax_return($data)
{
		header("Content-Type:text/html; charset=utf-8");
        echo(json_encode($data));
        exit;	
}


function get_user_name($id)
{
	return $GLOBALS['db']->getOneCached("select user_name from ".DB_PREFIX."user where id = ".intval($id));
}


function get_message_rel_data($message,$field='name')
{
	return $GLOBALS['db']->getOneCached("select ".$field." from ".DB_PREFIX.$message['rel_table']." where id = ".intval($message['rel_id']));
}
function get_delivery_sn($id)
{
	$is_delivery = $GLOBALS['db']->getOne("select d.is_delivery from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal as d on doi.deal_id = d.id where doi.id = ".intval($id));
	if($is_delivery==0)
	return $GLOBALS['lang']['ORDER_DELIVERY_STATUS_5'];
	else
	{
		$delivery_notice =  $GLOBALS['db']->getRow("select * from ".DB_PREFIX."delivery_notice where order_item_id = ".intval($id)." order by delivery_time desc");
		if($delivery_notice)
		{
			$str = $delivery_notice['notice_sn'];
			if($delivery_notice['express_id']!=0)
			$track_node = "<a href='javascript:void(0);' onclick='track_express(\"".$delivery_notice['notice_sn']."\",\"".$delivery_notice['express_id']."\");' >".$GLOBALS['lang']['TRACK_EXPRESS']."</a>&nbsp;";
			else
			$track_node = "";
			if($delivery_notice['is_arrival']==0)
			{
				$str.="<br />".$track_node."<a href='".url_pack("uc_order#arrival",$delivery_notice['id'])."'>".$GLOBALS['lang']['CONFIRM_ARRIVAL']."</a>";  
			}
			else
			{
				$str.="<br />".$track_node.$GLOBALS['lang']['ARRIVALED'];
			}
			return $str;
		}
		else
		return $GLOBALS['lang']['ORDER_DELIVERY_STATUS_0'];
	}
}

function get_order_item_list($order_id)
{
	$deal_order_item = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
	$str = '';
	foreach($deal_order_item as $k=>$v)
	{
		$str .="<br /><span title='".$v['name']."'>".msubstr($v['name'])."</span>[".$v['number']."]";	
	}
	return $str;
}

//用于获取可同步登录的API
function get_api_login()
{
	if(trim($_REQUEST['act'])!='api_login')
	{
		$apis = $GLOBALS['db']->getAllCached("select * from ".DB_PREFIX."api_login");
		$str = "<div class='blank'></div>";
		foreach($apis as $k=>$api)
		{
			if(file_exists(APP_ROOT_PATH."system/api_login/".$api['class_name']."_api.php"))
			{
				require_once APP_ROOT_PATH."system/api_login/".$api['class_name']."_api.php";
				$api_class = $api['class_name']."_api";
				$api_obj = new $api_class($api);
				$url = $api_obj->get_api_url();
				$str .= $url;
			}
		}
		return $str;
	}
	else
	return '';
}

//获取已过时间
function pass_date($time)
{
		$time_span = get_gmtime() - $time;
		if($time_span>3600*24*365)
		{
			//一年以前
			$time_span_lang = round($time_span/(3600*24*365)).$GLOBALS['lang']['SUPPLIER_YEAR'];
		}
		elseif($time_span>3600*24*30)
		{
			//一月
			$time_span_lang = round($time_span/(3600*24*30)).$GLOBALS['lang']['SUPPLIER_MON'];
		}
		elseif($time_span>3600*24)
		{
			//一天
			$time_span_lang = round($time_span/(3600*24)).$GLOBALS['lang']['SUPPLIER_DAY'];
		}
		elseif($time_span>3600)
		{
			//一小时
			$time_span_lang = round($time_span/(3600)).$GLOBALS['lang']['SUPPLIER_HOUR'];
		}
	    elseif($time_span>60)
		{
			//一分
			$time_span_lang = round($time_span/(60)).$GLOBALS['lang']['SUPPLIER_MIN'];
		}
		else
		{
			//一秒
			$time_span_lang = $time_span.$GLOBALS['lang']['SUPPLIER_SEC'];
		}
		return $time_span_lang;
}

//以下关于商家发货的新增函数
function get_region_name($id)
{
	return $GLOBALS['db']->getOneCached("select name from ".DB_PREFIX."delivery_region where id = ".$id);
}
function get_user_info($id)
{
	$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$id);
	$str = $user_info['user_name'];
	if($user_info['mobile']!='')
	{
		$str .="(".$GLOBALS['lang']['MOBILE'].":".$user_info['mobile'].")";
	}
	return $str;
}
function get_coupon_sn($deal_order_item_id)
{
	$coupon_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_coupon where order_deal_id =".$deal_order_item_id." and is_valid = 1");
	$str = "<br />";
	foreach($coupon_list as $row)
	{
		$begin_time = $row['begin_time']==0?$GLOBALS['lang']['NOT_LIMIT_TIME']:to_date($row['begin_time']);
		$end_time = $row['end_time']==0?$GLOBALS['lang']['NOT_LIMIT_TIME']:to_date($row['end_time']);
		$str.=$row['sn']."(".$begin_time."-".$end_time.")";
		if($row['confirm_time']!=0)
		$str.=$GLOBALS['lang']['COUPON_HAS_USED'];
		$str.="<br />";
	}
	return $str;
}

function get_delivery_status($id)
{
	$account_id = intval($_SESSION['account_info']['id']);
	$account_data = $GLOBALS['db']->getRow("select a.allow_delivery,s.name as name,a.account_name as account_name, a.supplier_id as supplier_id from ".DB_PREFIX."supplier_account as a left join ".DB_PREFIX."supplier as s on a.supplier_id = s.id where a.id = ".$account_id);
	
	$res = $GLOBALS['db']->getRow("select d.is_delivery,do.id from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal as d on doi.deal_id = d.id left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.id = ".intval($id));
	$is_delivery = intval($res['is_delivery']);
	if($is_delivery==0)
	return $GLOBALS['lang']['ORDER_DELIVERY_STATUS_5'];
	else
	{
		$delivery_notice =  $GLOBALS['db']->getRow("select * from ".DB_PREFIX."delivery_notice where order_item_id = ".intval($id)." order by delivery_time desc");
		if($delivery_notice)
		{
			$str = $delivery_notice['notice_sn'];	
			if($account_data['allow_delivery'])		
			$str = $str."<br /><a href='".url_pack("coupon#do_delivery",$res['id'])."'>".$GLOBALS['lang']['REDELIVERY']."</a>";
			return $str;
		}
		else
		{
			$str = $GLOBALS['lang']['ORDER_DELIVERY_STATUS_0'];
			if($account_data['allow_delivery'])		
			$str = $str."<br /><a href='".url_pack("coupon#do_delivery",$res['id'])."'>".$GLOBALS['lang']['DODELIVERY']."</a>";
			return $str;
		}
	}
}

function get_order_item_link($order_item_id)
{
	if($order_item_id==0)
	{
		return $GLOBALS['lang']['NO_DEAL_COUPON'];
	}
	else
	{
		$order = $GLOBALS['db']->getRow("select order_id,name from ".DB_PREFIX."deal_order_item where id = ".$order_item_id);
		if($order)
		{
			return "<a href='".url_pack("coupon#view",$order['order_id'])."'>".$order['name']."</a>";
		}
		else
		{
			return $GLOBALS['lang']['DEAL_DELETE_COUPON'];
		}
	}
}

function get_coupon_confirm_time($time)
{
	if($time==0)
	{
		return $GLOBALS['lang']['NOT_CONFIRM'];
	}
	else
	{
		return to_date($time);
	}
}

/**
 * 获取文章列表
 */
function get_article_list($limit, $cate_id=0, $where='',$orderby = '')
{			
			if($cate_id>0)
			{
				$count_sql = "select count(*) from ".DB_PREFIX."article as a left join ".DB_PREFIX."article_cate as ac on a.cate_id = ac.id where a.is_effect = 1 and a.is_delete = 0 and ac.is_delete = 0 and ac.is_effect = 1 ";
				$sql = "select a.* from ".DB_PREFIX."article as a left join ".DB_PREFIX."article_cate as ac on a.cate_id = ac.id where a.is_effect = 1 and a.is_delete = 0 and ac.is_delete = 0 and ac.is_effect = 1 ";
				
				$ids = $GLOBALS['cache']->get("DEAL_SHOP_ACATE_BELONE_IDS_".$cate_id);
				if($ids === false)
				{
						$ids_util = new ChildIds("article_cate");
						$ids = $ids_util->getChildIds($cate_id);
						$ids[] = $cate_id;
						
						$GLOBALS['cache']->set("DEAL_SHOP_ACATE_BELONE_IDS_".$cate_id,$ids);
				}

				$sql .= " and a.cate_id in (".implode(",",$ids).")";
				$count_sql .= " and a.cate_id in (".implode(",",$ids).")";
			}
			else
			{
				$count_sql = "select count(*) from ".DB_PREFIX."article as a where a.is_effect = 1 and a.is_delete = 0 ";
				$sql = "select a.* from ".DB_PREFIX."article as a where a.is_effect = 1 and a.is_delete = 0 ";
				
				$sql .= " and a.cate_id = 0 ";
				$count_sql .= " and a.cate_id = 0 ";
			}
				
			
			if($where != '')
			{
				$sql.=" and ".$where;
				$count_sql.=" and ".$where;
			}
			
			if($orderby=='')
			$sql.=" order by a.sort desc limit ".$limit;
			else
			$sql.=" order by ".$orderby." limit ".$limit;
	
			$articles = $GLOBALS['db']->getAll($sql);	
			foreach($articles as $k=>$v)
			{
				$articles[$k]['url'] = url_pack("article",$v['id'],$v['uname']);
			}	
			$articles_count = $GLOBALS['db']->getOne($count_sql);
			
	 		
			$res = array('list'=>$articles,'count'=>$articles_count);	
		
		return $res;
}


/**
 * 获得查询次数以及查询时间
 *
 * @access  public
 * @return  string
 */
function run_info()
{

	if(!SHOW_DEBUG)return "";

	$query_time = number_format($GLOBALS['db']->queryTime,6);

	
	$str = "共运行了".$GLOBALS['db']->queryCount."条语句，时间:". $query_time;

	foreach($GLOBALS['db']->queryLog as $K=>$sql)
	{
		if($K==0)$str.="<br />SQL语句列表：";
		$str.="<br />行".($K+1).":".$sql;
	}
         
	return "<div style='width:940px; padding:10px; line-height:22px; border:1px solid #ccc; text-align:left; margin:30px auto; font-size:14px; color:#999; height:150px; overflow-y:auto;'>".$str."</div>";
}


?>