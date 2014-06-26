<?php
class SupplierLocationAction extends CommonAction{
	public function index()
	{
		$supplier_id = intval($_REQUEST['supplier_id']);
		$supplier_info = M("Supplier")->getById($supplier_id);
		if(!$supplier_info)
		{
			$this->error(l("SUPPLIER_NOT_EXIST"));
		}
		$condition['supplier_id'] = intval($_REQUEST['supplier_id']);
		$this->assign("default_map",$condition);
		$this->assign("supplier_info",$supplier_info);
		parent::index();
		
	}
	public function add()
	{
		$supplier_id = intval($_REQUEST['supplier_id']);
		$supplier_info = M("Supplier")->getById($supplier_id);
		if(!$supplier_info)
		{
			$this->error(l("SUPPLIER_NOT_EXIST"));
		}
		$this->assign("supplier_info",$supplier_info);

		$this->display();
	}
	
	public function insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();

		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add",array("supplier_id"=>$data['supplier_id'])));
		if(!check_empty($data['name']))
		{
			$this->error(L("LOCATION_NAME_EMPTY_TIP"));
		}		
		if($data['supplier_id']==0)
		{
			$this->error(L("SUPPLIER_NOT_EXIST"));
		}
		// 更新数据
		
		if(M("SupplierLocation")->where("supplier_id=".$data['supplier_id'])->count()==0)
		{
			$data['is_main'] = 1;
		}
		$log_info = $data['name'];
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
	}	
	
	public function setMain()
	{
		$location_id = intval($_REQUEST['id']);
		$location = M(MODULE_NAME)->getById($location_id);
		M(MODULE_NAME)->where("supplier_id=".$location['supplier_id'])->setField("is_main",0);
		$location['is_main'] = 1;
		M(MODULE_NAME)->save($location);
		$log_info = $location['name'];
		save_log($log_info.L("SET_TO_MAIN"),1);
		redirect(u("SupplierLocation/index",array("supplier_id"=>$location['supplier_id'])));
	}
	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$supplier_info = M("Supplier")->getById($vo['supplier_id']);
		if(!$supplier_info)
		{
			$this->error(l("SUPPLIER_NOT_EXIST"));
		}
		$this->assign("supplier_info",$supplier_info);
		
		$this->assign ( 'vo', $vo );
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
			$this->error(L("LOCATION_NAME_EMPTY_TIP"));
		}	
		if($data['supplier_id']==0)
		{
			$this->error(L("SUPPLIER_NOT_EXIST"));
		}
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
		}
	}
	
	
	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				if( M(MODULE_NAME)->where(array ('id' => array ('in', explode ( ',', $id ) ) ,'is_main'=>1))->count() > 0 )
				{
					$this->error(L("MAIN_LOCATION_CANT_DELETE"),$ajax);
				}
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
				//删除相关预览图
//				foreach($rel_data as $data)
//				{
//					@unlink(get_real_path().$data['preview']);
//				}			
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
	
	
	
	
}
?>