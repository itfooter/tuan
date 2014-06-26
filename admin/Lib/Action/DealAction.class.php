<?php
class DealAction extends CommonAction{
	public function index()
	{
		//分类
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		
		//输出套餐城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		$supplier_list = M("Supplier")->findAll();
		$this->assign("supplier_list",$supplier_list);
		
		//开始加载搜索条件
		$map['is_delete'] = 0;
		if(trim($_REQUEST['name'])!='')
		{
			$map['name'] = array('like','%'.trim($_REQUEST['name']).'%');			
		}
		if(intval($_REQUEST['city_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_city");
			$city_ids = $child->getChildIds(intval($_REQUEST['city_id']));
			$city_ids[] = intval($_REQUEST['city_id']);
			$map['city_id'] = array("in",$city_ids);
		}
		
		if(intval($_REQUEST['cate_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_cate");
			$cate_ids = $child->getChildIds(intval($_REQUEST['cate_id']));
			$cate_ids[] = intval($_REQUEST['cate_id']);
			$map['cate_id'] = array("in",$cate_ids);
		}
		
		if(intval($_REQUEST['supplier_id'])>0)
		{
			$map['supplier_id'] = intval($_REQUEST['supplier_id']);
		}
		$map['publish_wait'] = 0;
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display();
		return;
	}
	
	public function publish()
	{
		$map['publish_wait'] = 1;
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display();
		return;
	}
	public function trash()
	{
		$condition['is_delete'] = 1;
		$this->assign("default_map",$condition);
		parent::index();
	}
	public function add()
	{
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		$this->assign("new_sort", M("Deal")->where("is_delete=0")->max("sort")+1);
		
		//输出套餐城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		//输出团区域
		$area_list = M("DealCityArea")->where('is_delete = 0')->findAll();
		$area_list = D("DealCityArea")->toFormatTree($area_list,'name');
		$this->assign("area_list",$area_list);
		
		$supplier_list = M("Supplier")->findAll();
		$this->assign("supplier_list",$supplier_list);
		
		$goods_type_list = M("GoodsType")->findAll();
		$this->assign("goods_type_list",$goods_type_list);
		
		$weight_list = M("WeightUnit")->findAll();
		$this->assign("weight_list",$weight_list);
		
		//输出配送方式列表
		$delivery_list = M("Delivery")->where("is_effect=1")->findAll();
		$this->assign("delivery_list",$delivery_list);
		
		//输出支付方式
		$payment_list = M("Payment")->where("is_effect=1")->findAll();
		$this->assign("payment_list",$payment_list);
		
		$this->display();
	}
	
	public function insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();

		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		
		if(!check_empty($data['name']))
		{
			$this->error(L("DEAL_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['sub_name']))
		{
			$this->error(L("DEAL_SUB_NAME_EMPTY_TIP"));
		}	
		if($data['cate_id']==0)
		{
			$this->error(L("DEAL_CATE_EMPTY_TIP"));
		}
		if($data['city_id']==0)
		{
			$this->error(L("DEAL_CITY_EMPTY_TIP"));
		}
		if($data['supplier_id']==0)
		{
			$this->error(L("DEAL_SUPPLIER_EMPTY_TIP"));
		}
		if($data['min_bought']<0)
		{
			$this->error(L("DEAL_MIN_BOUGHT_ERROR_TIP"));
		}
		if($data['max_bought']<0)
		{
			$this->error(L("DEAL_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_min_bought']<0)
		{
			$this->error(L("DEAL_USER_MIN_BOUGHT_ERROR_TIP"));
		}		
		if($data['user_max_bought']<0)
		{
			$this->error(L("DEAL_USER_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_max_bought']<$data['user_min_bought']&&$data['user_max_bought']>0)
		{
			$this->error(L("DEAL_USER_MAX_MIN_BOUGHT_ERROR_TIP"));
		}
		// 更新数据

		$data['notice'] = intval($_REQUEST['notice']);
		$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		$data['coupon_begin_time'] = trim($data['coupon_begin_time'])==''?0:to_timespan($data['coupon_begin_time']);
		$data['coupon_end_time'] = trim($data['coupon_end_time'])==''?0:to_timespan($data['coupon_end_time']);
		//将第一张图片设为套餐图片
		$imgs = $_REQUEST['img'];
		foreach($imgs as $k=>$v)
		{
			if($v!='')
			{
				$data['img'] = $v;
				break;
			}
		}

		$log_info = $data['name'];
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			//开始处理图片
			$imgs = $_REQUEST['img'];
			foreach($imgs as $k=>$v)
			{
				if($v!='')
				{
					$img_data['deal_id'] = $list;
					$img_data['img'] = $v;
					$img_data['sort'] = $k;
					M("DealGallery")->add($img_data);
				}
			}
			//end 处理图片
			
			//开始处理属性
			$deal_attr = $_REQUEST['deal_attr'];
			$deal_attr_price = $_REQUEST['deal_attr_price'];	
			$deal_attr_stock_hd = $_REQUEST['deal_attr_stock_hd'];			
			foreach($deal_attr as $goods_type_attr_id=>$arr)
			{
				foreach($arr as $k=>$v)
				{
					if($v!='')
					{
						$deal_attr_item['deal_id'] = $list;
						$deal_attr_item['goods_type_attr_id'] = $goods_type_attr_id;
						$deal_attr_item['name'] = $v;
						$deal_attr_item['price'] = $deal_attr_price[$goods_type_attr_id][$k];
						$deal_attr_item['is_checked'] = intval($deal_attr_stock_hd[$goods_type_attr_id][$k]);
						M("DealAttr")->add($deal_attr_item);
					}
				}
			}
			
			//开始创建属性库存
			$stock_cfg = $_REQUEST['stock_cfg_num'];
			$attr_cfg = $_REQUEST['stock_attr'];
			$attr_str = $_REQUEST['stock_cfg'];
			foreach($stock_cfg as $row=>$v)
			{
				$stock_data = array();
				$stock_data['deal_id'] = $list;
				$stock_data['stock_cfg'] = $v;
				$stock_data['attr_str'] = $attr_str[$row];
				$attr_cfg_data = array();
				foreach($attr_cfg as $attr_id=>$cfg)
				{
					$attr_cfg_data[$attr_id] = $cfg[$row];
				}
				$stock_data['attr_cfg'] = serialize($attr_cfg_data);
				M("AttrStock")->add($stock_data);
			}
			
			if(intval($_REQUEST['free_delivery'])==1)
			{
				$delivery_ids = $_REQUEST['delivery_id'];
				$free_counts = $_REQUEST['free_count'];
				foreach($delivery_ids as $k=>$v)
				{
					$free_conf = array();
					$free_conf['delivery_id'] = $delivery_ids[$k];
					$free_conf['free_count'] = $free_counts[$k];
					$free_conf['deal_id'] = $list;
					M("FreeDelivery")->add($free_conf);
				}
			}
			
			if(intval($_REQUEST['define_payment'])==1)
			{
				$payment_ids = $_REQUEST['payment_id'];
				foreach($payment_ids as $k=>$v)
				{
					$payment_conf = array();
					$payment_conf['payment_id'] = $payment_ids[$k];
					$payment_conf['deal_id'] = $list;
					M("DealPayment")->add($payment_conf);
				}
			}
			
			$delivery_ids = $_REQUEST['forbid_delivery_id'];
			foreach($delivery_ids as $k=>$v)
			{
					$delivery_conf = array();
					$delivery_conf['delivery_id'] = $delivery_ids[$k];
					$delivery_conf['deal_id'] = $list;
					M("DealDelivery")->add($delivery_conf);
			}
			
			//成功提示
			syn_deal_status($list);
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("INSERT_FAILED").$dbErr,0);
			$this->error(L("INSERT_FAILED").$dbErr);
		}
	}	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['is_delete'] = 0;
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$vo['begin_time'] = $vo['begin_time']!=0?to_date($vo['begin_time']):'';
		$vo['end_time'] = $vo['end_time']!=0?to_date($vo['end_time']):'';
		$vo['coupon_begin_time'] = $vo['coupon_begin_time']!=0?to_date($vo['coupon_begin_time']):'';
		$vo['coupon_end_time'] = $vo['coupon_end_time']!=0?to_date($vo['coupon_end_time']):'';
		$this->assign ( 'vo', $vo );
		
		
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		
		//输出套餐城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		//输出团区域
		$area_list = M("DealCityArea")->where('is_delete = 0')->findAll();
		$area_list = D("DealCityArea")->toFormatTree($area_list,'name');
		$this->assign("area_list",$area_list);
		
		$supplier_list = M("Supplier")->findAll();
		$this->assign("supplier_list",$supplier_list);
		
		$goods_type_list = M("GoodsType")->findAll();
		$this->assign("goods_type_list",$goods_type_list);
		
		//输出图片集
		$img_list = M("DealGallery")->where("deal_id=".$vo['id'])->order("sort asc")->findAll();
		$imgs = array();
		foreach($img_list as $k=>$v)
		{
			$imgs[$v['sort']] = $v['img']; 
		}
		$this->assign("img_list",$imgs);
		
		
		$weight_list = M("WeightUnit")->findAll();
		$this->assign("weight_list",$weight_list);
		
		
		//输出配送方式列表
		$delivery_list = M("Delivery")->where("is_effect=1")->findAll();
		foreach($delivery_list as $k=>$v)
		{
			$delivery_list[$k]['free_count'] = M("FreeDelivery")->where("deal_id=".$vo['id']." and delivery_id = ".$v['id'])->getField("free_count");			
			$delivery_list[$k]['checked'] = M("DealDelivery")->where("deal_id=".$vo['id']." and delivery_id = ".$v['id'])->count();	
		}
		$this->assign("delivery_list",$delivery_list);
		
		//输出支付方式
		$payment_list = M("Payment")->where("is_effect=1")->findAll();
		foreach($payment_list as $k=>$v)
		{
			$payment_list[$k]['checked'] = M("DealPayment")->where("deal_id=".$vo['id']." and payment_id = ".$v['id'])->count();			
		}
		$this->assign("payment_list",$payment_list);
		
		
		//输出规格库存的配置
		$attr_stock = M("AttrStock")->where("deal_id=".intval($vo['id']))->order("id asc")->findAll();
		$attr_cfg_json = "{";
		$attr_stock_json = "{";
		
		foreach($attr_stock as $k=>$v)
		{
			$attr_cfg_json.=$k.":"."{";
			$attr_stock_json.=$k.":"."{";
			foreach($v as $key=>$vvv)
			{
				if($key!='attr_cfg')
				$attr_stock_json.="\"".$key."\":"."\"".$vvv."\",";
			}
			$attr_stock_json = substr($attr_stock_json,0,-1);
			$attr_stock_json.="},";	
			
			$attr_cfg_data = unserialize($v['attr_cfg']);	
			foreach($attr_cfg_data as $attr_id=>$vv)
			{
				$attr_cfg_json.=$attr_id.":"."\"".$vv."\",";
			}	
			$attr_cfg_json = substr($attr_cfg_json,0,-1);
			$attr_cfg_json.="},";		
		}
		if($attr_stock)
		{
			$attr_cfg_json = substr($attr_cfg_json,0,-1);
			$attr_stock_json = substr($attr_stock_json,0,-1);
		}
		
		$attr_cfg_json .= "}";
		$attr_stock_json .= "}";
		
		
		$this->assign("attr_cfg_json",$attr_cfg_json);	
		$this->assign("attr_stock_json",$attr_stock_json);	
		//区别产品发布
		if(isset($_REQUEST['opt']) && trim($_REQUEST['opt']) == 'publish')
			$this->assign("opt",$_REQUEST['opt']);	
		else
			$this->assign("opt",'');	
		$this->display();
	}
	
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['name']))
		{
			$this->error(L("DEAL_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['sub_name']))
		{
			$this->error(L("DEAL_SUB_NAME_EMPTY_TIP"));
		}		
		if($data['cate_id']==0)
		{
			$this->error(L("DEAL_CATE_EMPTY_TIP"));
		}
		if($data['city_id']==0)
		{
			$this->error(L("DEAL_CITY_EMPTY_TIP"));
		}
		if($data['supplier_id']==0)
		{
			$this->error(L("DEAL_SUPPLIER_EMPTY_TIP"));
		}
		if($data['min_bought']<0)
		{
			$this->error(L("DEAL_MIN_BOUGHT_ERROR_TIP"));
		}
		if($data['max_bought']<0)
		{
			$this->error(L("DEAL_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_min_bought']<0)
		{
			$this->error(L("DEAL_USER_MIN_BOUGHT_ERROR_TIP"));
		}	
		if($data['user_max_bought']<0)
		{
			$this->error(L("DEAL_USER_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_max_bought']<$data['user_min_bought']&&$data['user_max_bought']!=0)
		{
			$this->error(L("DEAL_USER_MAX_MIN_BOUGHT_ERROR_TIP"));
		}
		$data['publish_wait'] = '0';
		$data['notice'] = intval($_REQUEST['notice']);
		$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		$data['coupon_begin_time'] = trim($data['coupon_begin_time'])==''?0:to_timespan($data['coupon_begin_time']);
		$data['coupon_end_time'] = trim($data['coupon_end_time'])==''?0:to_timespan($data['coupon_end_time']);
		//将第一张图片设为套餐图片
		$imgs = $_REQUEST['img'];
		foreach($imgs as $k=>$v)
		{
				if($v!='')
				{
					$data['img'] = $v;
					break;
				}
		}
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			
			//开始处理图片
			M("DealGallery")->where("deal_id=".$data['id'])->delete();
			$imgs = $_REQUEST['img'];
			foreach($imgs as $k=>$v)
			{
				if($v!='')
				{
					$img_data['deal_id'] = $data['id'];
					$img_data['img'] = $v;
					$img_data['sort'] = $k;
					M("DealGallery")->add($img_data);
				}
			}
			//end 处理图片
			
			//开始处理属性
			M("DealAttr")->where("deal_id=".$data['id'])->delete();
			$deal_attr = $_REQUEST['deal_attr'];
			$deal_attr_price = $_REQUEST['deal_attr_price'];	
			$deal_attr_stock_hd		= $_REQUEST['deal_attr_stock_hd'];
			foreach($deal_attr as $goods_type_attr_id=>$arr)
			{
				foreach($arr as $k=>$v)
				{
					if($v!='')
					{
						$deal_attr_item['deal_id'] = $data['id'];
						$deal_attr_item['goods_type_attr_id'] = $goods_type_attr_id;
						$deal_attr_item['name'] = $v;
						$deal_attr_item['price'] = $deal_attr_price[$goods_type_attr_id][$k];
						$deal_attr_item['is_checked'] = intval($deal_attr_stock_hd[$goods_type_attr_id][$k]);
						M("DealAttr")->add($deal_attr_item);
					}
				}
			}
			//开始创建属性库存
			M("AttrStock")->where("deal_id=".$data['id'])->delete();
			$stock_cfg = $_REQUEST['stock_cfg_num'];
			$attr_cfg = $_REQUEST['stock_attr'];
			$attr_str = $_REQUEST['stock_cfg'];
			foreach($stock_cfg as $row=>$v)
			{
				$stock_data = array();
				$stock_data['deal_id'] = $data['id'];
				$stock_data['stock_cfg'] = $v;
				$stock_data['attr_str'] = $attr_str[$row];
				$attr_cfg_data = array();
				foreach($attr_cfg as $attr_id=>$cfg)
				{
					$attr_cfg_data[$attr_id] = $cfg[$row];
				}
				$stock_data['attr_cfg'] = serialize($attr_cfg_data);
				$sql = "select sum(oi.number) from ".DB_PREFIX."deal_order_item as oi left join ".
						DB_PREFIX."deal as d on d.id = oi.deal_id left join ".
						DB_PREFIX."deal_order as do on oi.order_id = do.id where".
						" do.pay_status = 2 and do.is_delete = 0 and d.id = ".$data['id'].
						" and oi.attr_str like '%".$attr_str[$row]."%'";
										
				$stock_data['buy_count'] = intval($GLOBALS['db']->getOne($sql));
				M("AttrStock")->add($stock_data);
			}

			M("FreeDelivery")->where("deal_id=".$data['id'])->delete();
			if(intval($_REQUEST['free_delivery'])==1)
			{
				$delivery_ids = $_REQUEST['delivery_id'];
				$free_counts = $_REQUEST['free_count'];
				foreach($delivery_ids as $k=>$v)
				{
					$free_conf = array();
					$free_conf['delivery_id'] = $delivery_ids[$k];
					$free_conf['free_count'] = $free_counts[$k];
					$free_conf['deal_id'] = $data['id'];
					M("FreeDelivery")->add($free_conf);
				}
			}
			
			M("DealPayment")->where("deal_id=".$data['id'])->delete();
			if(intval($_REQUEST['define_payment'])==1)
			{
				$payment_ids = $_REQUEST['payment_id'];
				foreach($payment_ids as $k=>$v)
				{
					$payment_conf = array();
					$payment_conf['payment_id'] = $payment_ids[$k];
					$payment_conf['deal_id'] = $data['id'];
					M("DealPayment")->add($payment_conf);
				}
			}
			
			M("DealDelivery")->where("deal_id=".$data['id'])->delete();
			$delivery_ids = $_REQUEST['forbid_delivery_id'];
			foreach($delivery_ids as $k=>$v)
			{
					$delivery_conf = array();
					$delivery_conf['delivery_id'] = $delivery_ids[$k];
					$delivery_conf['deal_id'] = $data['id'];
					M("DealDelivery")->add($delivery_conf);
			}
			
			//成功提示
			syn_deal_status($data['id']);
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("UPDATE_FAILED").$dbErr,0);
			$this->error(L("UPDATE_FAILED").$dbErr,0);
		}
	}
	
	
	public function delete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				M("DealCoupon")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->setField("is_delete",1);
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 1 );
				if ($list!==false) {
					save_log($info.l("DELETE_SUCCESS"),1);
					$this->success (l("DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("DELETE_FAILED"),0);
					$this->error (l("DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}
	
	public function restore() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				M("DealCoupon")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->setField("is_delete",0);
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];						
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 0 );
				if ($list!==false) {
					save_log($info.l("RESTORE_SUCCESS"),1);
					$this->success (l("RESTORE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("RESTORE_FAILED"),0);
					$this->error (l("RESTORE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}
	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				//删除的验证
				if(M("DealOrder")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->count()>0)
				{
					$this->error(l("DEAL_ORDER_NOT_EMPTY"),$ajax);
				}
				M("DealCoupon")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealDelivery")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealPayment")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealAttr")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("AttrStock")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
					
				if ($list!==false) {
					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	
	
	public function set_sort()
	{
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		$log_info = M(MODULE_NAME)->where("id=".$id)->getField('name');
		if(!check_sort($sort))
		{
			$this->error(l("SORT_FAILED"),1);
		}
		M(MODULE_NAME)->where("id=".$id)->setField("sort",$sort);
		save_log($log_info.l("SORT_SUCCESS"),1);
		$this->success(l("SORT_SUCCESS"),1);
	}
	
	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		$c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField("is_effect");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M(MODULE_NAME)->where("id=".$id)->setField("is_effect",$n_is_effect);	
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
	}
	
	public function attr_html()
	{
		$deal_goods_type = intval($_REQUEST['deal_goods_type']);
		$deal_id = intval($_REQUEST['deal_id']);
		
		if($deal_id>0&&M("Deal")->where("id=".$deal_id)->getField("deal_goods_type")==$deal_goods_type)
		{			
			$goods_type_attr = M()->query("select a.name as attr_name,a.is_checked as is_checked,a.price as price,b.* from ".conf("DB_PREFIX")."deal_attr as a left join ".conf("DB_PREFIX")."goods_type_attr as b on a.goods_type_attr_id = b.id where a.deal_id=".$deal_id." order by a.id asc");

			$goods_type_attr_id = 0;
			if($goods_type_attr)
			{
				foreach($goods_type_attr as $k=>$v)
				{
					$goods_type_attr[$k]['attr_list'] = explode(",",$v['preset_value']);
					if($goods_type_attr_id!=$v['id'])
					{
						$goods_type_attr[$k]['is_first'] = 1;
					}
					else
					{
						$goods_type_attr[$k]['is_first'] = 0;
					}
					$goods_type_attr_id = $v['id'];
				}	
			}
			else 
			{
				$goods_type_attr = M("GoodsTypeAttr")->where("goods_type_id=".$deal_goods_type)->findAll();
				foreach($goods_type_attr as $k=>$v)
				{
					$goods_type_attr[$k]['attr_list'] = explode(",",$v['preset_value']);
					$goods_type_attr[$k]['is_first'] = 1;
				}
			}		
		}
		else
		{
			$goods_type_attr = M("GoodsTypeAttr")->where("goods_type_id=".$deal_goods_type)->findAll();
			foreach($goods_type_attr as $k=>$v)
			{
				$goods_type_attr[$k]['attr_list'] = explode(",",$v['preset_value']);
				$goods_type_attr[$k]['is_first'] = 1;
			}		
		}
		$this->assign("goods_type_attr",$goods_type_attr);		
		$this->display();
	}
	
	public function show_detail()
	{
		$id = intval($_REQUEST['id']);
		
		$deal_info = M("Deal")->getById($id);
		$this->assign("deal_info",$deal_info);
		//购买的单数
		$real_user_count = intval($GLOBALS['db']->getOne("select count(distinct(do.id)) from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.deal_id = ".$id." and do.pay_status = 2"));
		$this->assign("real_user_count",$real_user_count);
		
		$real_buy_count =  intval($GLOBALS['db']->getOne("select sum(doi.number) from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.deal_id = ".$id." and do.pay_status = 2"));
		$this->assign("real_buy_count",$real_buy_count);
		
		$real_coupon_count = intval(M("DealCoupon")->where("deal_id=".$id." and is_valid=1")->count());
		$this->assign("real_coupon_count",$real_coupon_count);

		//总收款，不计退款
		$pay_total_rows = $GLOBALS['db']->getAll("select pn.money from ".DB_PREFIX."payment_notice as pn left join ".DB_PREFIX."deal_order as do on pn.order_id = do.id left join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.pay_status = 2 and doi.deal_id = ".$id." and pn.is_paid = 1 group by pn.id");
		$pay_total = 0;
		foreach($pay_total_rows as $money)
		{
			$pay_total = $pay_total + floatval($money['money']);
		}		
		$this->assign("pay_total",$pay_total);

		//每个支付方式下的收款
		$payment_list = M("Payment")->findAll();
		foreach($payment_list as $k=>$v)
		{
			$payment_pay_total = 0;
			$payment_pay_total_rows = $GLOBALS['db']->getAll("select pn.money from ".DB_PREFIX."payment_notice as pn left join ".DB_PREFIX."deal_order as do on pn.order_id = do.id left join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.pay_status = 2 and doi.deal_id = ".$id." and pn.is_paid = 1 and pn.payment_id = ".$v['id']." group by pn.id");
			foreach($payment_pay_total_rows as $money)
			{
				$payment_pay_total = $payment_pay_total + floatval($money['money']);
			}	
			$payment_list[$k]['pay_total'] = $payment_pay_total;
		}
		$this->assign("payment_list",$payment_list);
		
		
		//订单实收
		$order_total = 0;
		$order_total_rows = $GLOBALS['db']->getAll("select do.pay_amount as money from ".DB_PREFIX."deal_order as do inner join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.pay_status = 2 and doi.deal_id = ".$id." group by do.id");
		foreach($order_total_rows as $money)
		{
				$order_total = $order_total + floatval($money['money']);
		}	
		$this->assign("order_total",$order_total);
		
		//额外退款的订单
		$extra_count = $GLOBALS['db']->getOne("select count(distinct(do.id)) from ".DB_PREFIX."deal_order as do left join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.extra_status > 0 and doi.deal_id = ".$id);
		$this->assign("extra_count",$extra_count);
		
		//额外退款的订单
		$aftersale_count = $GLOBALS['db']->getOne("select count(distinct(do.id)) from ".DB_PREFIX."deal_order as do left join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.after_sale > 0 and doi.deal_id = ".$id);
		$this->assign("aftersale_count",$aftersale_count);
		//已验证数量
		$verify_count = $GLOBALS['db']->getOne("select count(do.id) from ".DB_PREFIX."deal_coupon as do where do.confirm_time <> 0 and do.deal_id = ".$id);
		$this->assign("verify_count",$verify_count);
		//售后退款
		$refund_money = 0;
		$refund_total_rows = $GLOBALS['db']->getAll("select do.refund_money as money from ".DB_PREFIX."deal_order as do inner join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.pay_status = 2 and doi.deal_id = ".$id." group by do.id");
		foreach($refund_total_rows as $money)
		{
				$refund_money = $refund_money + floatval($money['money']);
		}
		$this->assign("refund_money",$refund_money);
		$this->display();
	}
	public function area_list()
	{
			$city_id = intval($_REQUEST['city_id']);
			$id = intval($_REQUEST['id']);
			$area_list = D(DealCityArea)->where("is_delete=0 and is_effect=1 and city_id=".$city_id)->findAll();
			$area_list = D("DealCityArea")->toFormatTree($area_list,'name');
			
			$Deal_info = M("Deal")->getById($id);
			$area_id = intval($Deal_info['area_id']);
			
			foreach($area_list as $k=>$v)
			{
				if($v['id']==$area_id)
				{
					$area_list[$k]['selected'] = true;
				}
			}
			$this->assign("area_list",$area_list);
			
			$this->display();
	
	}
}
?>