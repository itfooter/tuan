<?php
class BroadcastAction extends CommonAction{
	public function index()
	{
		parent::index();
	}
	public function load_file()
	{
		$tmpl = $_REQUEST['tmpl'];
		$directory = APP_ROOT_PATH."app/Tpl/".$tmpl."/";
		$files = get_all_files($directory);
		$tmpl_files = array();
		foreach($files as $item)
		{
			if(substr($item,-5)==".html")
			{
				$item = explode($directory,$item);
				$item = $item[1];
				$tmpl_files[] = $item;
			}
		}
		$this->ajaxReturn($tmpl_files);
	}
	public function load_adv_id()
	{
		$tmpl = $_REQUEST['tmpl'];
		$file = $_REQUEST['file'];
		$directory = APP_ROOT_PATH."app/Tpl/".$tmpl."/";
		$file_content = @file_get_contents($directory.$file);
		

		$layout_array = array();
		$adv_ids = array();
		preg_match_all("/<adv(\s+)adv_id=\"(\S+)\"([^>]*)>/",$file_content,$layout_array);
		foreach($layout_array[2] as $item)
		{
			$adv_ids[] = $item;
		}
		
		$this->ajaxReturn($adv_ids);
	}
	public function add()
	{
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
			$this->error(L("IMAGE_NAME_EMPTY_TIP"));
		}	
		
	if(!check_empty($data['city_ids']))
	{
		$this->error(L("ADV_IDS_EMPTY_TIP"));
	}
		

		// 更新数据
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
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$this->assign ( 'vo', $vo );
		
		$this->display();
	}
	
public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
	
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));				
	   if($data['code']=='')
		{
			$this->error(L("ADV_CODE_EMPTY_TIP"));
		}
        if($data['city_ids']=='')
		{
			$this->error(L("ADV_IDS_EMPTY_TIP"));
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
}
?>