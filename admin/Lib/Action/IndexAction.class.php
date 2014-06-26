<?php
class IndexAction extends AuthAction{
	//首页
    public function index(){
		$this->display();
    }
    
    //清空缓存
    public function clear_cache()
    {
    	$ajax = intval($_REQUEST['ajax']);
    	clear_cache();    	
       	$this->success(L('CLEAR_SUCCESS'),$ajax);
    }
    
    //框架头
	public function top()
	{
		$navs = M("RoleNav")->where("is_effect=1 and is_delete=0")->order("sort asc")->findAll();
		$this->assign("navs",$navs);
		$this->display();
	}
	//框架左侧
	public function left()
	{
		$adm_session = Session::get(md5(conf("AUTH_KEY")));
		$adm_id = intval($adm_session['adm_id']);
		
		$nav_id = intval($_REQUEST['id']);
		$nav_group = M("RoleGroup")->where("nav_id=".$nav_id." and is_effect = 1 and is_delete = 0")->order("sort asc")->findAll();		
		foreach($nav_group as $k=>$v)
		{
			$sql = "select role_node.`action` as a,role_module.`module` as m,role_node.id as nid,role_node.name as name from ".conf("DB_PREFIX")."role_node as role_node left join ".
				   conf("DB_PREFIX")."role_module as role_module on role_module.id = role_node.module_id ".
				   "where role_node.is_effect = 1 and role_node.is_delete = 0 and role_module.is_effect = 1 and role_module.is_delete = 0 and role_node.group_id = ".$v['id'];
			
			$nav_group[$k]['nodes'] = M()->query($sql);
		}
		$this->assign("menus",$nav_group);
		$this->display();
	}
	//默认框架主区域
	public function main()
	{
		//会员数
		$total_user = M("User")->count();
		$total_verify_user = M("User")->where("is_effect=1")->count();
		$this->assign("total_user",$total_user);
		$this->assign("total_verify_user",$total_verify_user);
		
		//套餐数
		$deal_count = M("Deal")->where("time_status = 1 and buy_status <> 2 and is_delete = 0 and is_effect = 1 and buy_type = 0")->count();
		$score_count = M("Deal")->where("time_status = 1 and buy_status <> 2 and is_delete = 0 and is_effect = 1 and buy_type = 1")->count();
		$this->assign("deal_count",$deal_count);
		$this->assign("score_count",$score_count);
		
		//订单数
		$order_count = M("DealOrder")->where("type = 0")->count();
		$this->assign("order_count",$order_count);
		$order_buy_count = M("DealOrder")->where("pay_status=2 and type = 0")->count();
		$this->assign("order_buy_count",$order_buy_count);
		
		//充值单数
		$incharge_order_count = M("DealOrder")->where("type = 1")->count();
		$this->assign("incharge_order_count",$incharge_order_count);
		$incharge_order_buy_count = M("DealOrder")->where("pay_status=2 and type = 1")->count();
		$this->assign("incharge_order_buy_count",$incharge_order_buy_count);
		

		$this->display();
	}	
	//底部
	public function footer()
	{
		$this->display();
	}
	
	//修改管理员密码
	public function change_password()
	{
		$adm_session = Session::get(md5(conf("AUTH_KEY")));
		$this->assign("adm_data",$adm_session);
		$this->display();
	}
	public function do_change_password()
	{
		$adm_id = intval($_REQUEST['adm_id']);
		if(!check_empty($_REQUEST['adm_password']))
		{
			$this->error(L("ADM_PASSWORD_EMPTY_TIP"));
		}
		if(!check_empty($_REQUEST['adm_new_password']))
		{
			$this->error(L("ADM_NEW_PASSWORD_EMPTY_TIP"));
		}
		if($_REQUEST['adm_confirm_password']!=$_REQUEST['adm_new_password'])
		{
			$this->error(L("ADM_NEW_PASSWORD_NOT_MATCH_TIP"));
		}		
		if(M("Admin")->where("id=".$adm_id)->getField("adm_password")!=md5($_REQUEST['adm_password']))
		{
			$this->error(L("ADM_PASSWORD_ERROR"));
		}
		M("Admin")->where("id=".$adm_id)->setField("adm_password",md5($_REQUEST['adm_new_password']));
		save_log(M("Admin")->where("id=".$adm_id)->getField("adm_name").L("CHANGE_SUCCESS"),1);
		$this->success(L("CHANGE_SUCCESS"));
		
		
	}
	
	public function reset_sending()
	{
		$field = trim($_REQUEST['field']);
		if($field=='DEAL_MSG_LOCK'||$field=='PROMOTE_MSG_LOCK')
		{
			M("Conf")->where("name='".$field."'")->setField("value",'0');
			$this->success(L("RESET_SUCCESS"),1);
		}
		else
		{
			$this->error(L("INVALID_OPERATION"),1);
		}
	}
}
?>