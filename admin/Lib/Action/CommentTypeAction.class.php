<?php
class CommentTypeAction extends CommonAction{
	
	public function index(){
		$map['is_main'] = 0;
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
	public function add()
	{
		$deal_cates = M("DealCate")->where("is_delete=0 and is_effect=1")->findAll();
		$this->assign("deal_cates",$deal_cates);
		$this->display();
	}
	public function insert() {
		B('FilterString');
		$data = M("CommentType")->create ();
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		
		if(!check_empty($data['name']))
		{
			$this->error(L("TAGNAME_EMPTY_TIP"));
		}
		

		// 更新数据
		$log_info = $data['name'];
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			//成功提示
			foreach($_REQUEST['cate_id'] as $cate_id)
			{
				if(intval($cate_id)>0)
				{
					$link_data=  array();
					$link_data['category_id'] = intval($cate_id);
					$link_data['comment_type_id'] = $list;
					M("CommentTypeLink")->add($link_data);
				}
			}		
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
	}
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$this->assign ( 'vo', $vo );
		
		$deal_cates = M("DealCate")->where("is_delete=0 and is_effect=1")->findAll();
		foreach($deal_cates as $k=>$v)
		{
			$deal_cates[$k]['checked'] = M("CommentTypeLink")->where("category_id=".$v['id']." and comment_type_id = ".$vo['id'])->count();
		}
		$this->assign("deal_cates",$deal_cates);
		
		$this->display();
	}
	public function update()
	{
		$data = M(MODULE_NAME)->create ();
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		
		if(!check_empty($data['name']))
		{
			$this->error(L("TAGNAME_EMPTY_TIP"));
		}

		
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		$log_info = $data['name'];
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			//成功提示			
			M("CommentTypeLink")->where("comment_type_id=".$data['id'])->delete();
			foreach($_REQUEST['cate_id'] as $cate_id)
			{
				if(intval($cate_id)>0)
				{
					$link_data=  array();
					$link_data['category_id'] = intval($cate_id);
					$link_data['comment_type_id'] = $data['id'];
					M("CommentTypeLink")->add($link_data);
				}
			}	
			syn_deal_status($data['id']);
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
		}
	}
	
}
?>