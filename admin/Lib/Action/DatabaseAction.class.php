<?php

// 数据库管理
class DatabaseAction extends CommonAction{
	protected $db =  NULL;
 	public function __construct() {
        // 获取数据库对象实例
         parent::__construct();
        $this->db   =  Db::getInstance();
       
    }
	public function index()
	{
		$db_back_dir = get_real_path()."public/db_backup/";
		$sql_list = $this->dirFileInfo($db_back_dir,".sql");
		$this->assign("sql_list",$sql_list);
		$this->display();
	}
    public function sql()
    {
        // 获取数据库列表
        $this->getDbList();
        // 获取当前数据库
        $dbName   =  $this->getUseDb();
        // 获取当前库的数据表
        $tables   = $this->db->getTables($dbName);
        $this->assign('tables',$tables);
        $this->display();
        return ;
    }	
	public function dump()
	{
		$sqlDump = new SqlDump();
		if($sqlDump->dump())
		{
			$msg = L("DB_BACKUP_SUCCESS");
			save_log($msg,1);
			$this->success($msg,true);	
			
		}
		else
		{
			$msg = L("DB_BACKUP_FAILED");
			save_log($msg,0);
			$this->error($msg,true);	
		}
	}
	
	public function delete()
	{
		$groupname = $_REQUEST['file'];
		$db_back_dir = get_real_path()."public/db_backup/";
		$sql_list = $this->dirFileInfo($db_back_dir,".sql");
		$deleteGroup = $sql_list[$groupname];
		foreach($deleteGroup as $fileItem)
		{
			@unlink($db_back_dir.$fileItem['filename']);
		}
		save_log($groupname.L('DELETE_SUCCESS'),1);
		$this->success(L('DELETE_SUCCESS'),true);		
	}
	
	public function restore()
	{
		$groupname = $_REQUEST['file'];
		$db_back_dir = get_real_path()."public/db_backup/";
		$sql_list = $this->dirFileInfo($db_back_dir,".sql");
		$restoreGroup = $sql_list[$groupname];
		
		$sqlDump = new SqlDump();
		$msg = $sqlDump->restore($restoreGroup);
		if($msg=="")
		{
			$msg = L("DB_RESTORE_SUCCESS");
			save_log($msg,1);
			$this->success($msg,true);		
		}
		else 
		{
			$msg = L("DB_RESTORE_FAILED");
			save_log($msg,0);
			$this->error($msg,true);	
		}
		
	}
	
	
	//用于获取指定路径下的文件组
	private function dirFileInfo($dir,$type)   
	{  
		  if(!is_dir($dir))
		  		return   false;  
		  $dirhandle=opendir($dir);  
		  $arrayFileName=array();  
		  while(($file   =   readdir($dirhandle))   !==   false)
		  {  	
		 	 if (($file!=".")&&($file!=".."))   
		 	 {  
		  		$typelen=0-strlen($type);  		   
		  		if	(substr($file,$typelen)==$type)  
		  		{
		  			$file_only_name = substr($file,0,strlen($file)+$typelen);
		  			$file_name_arr = explode("_",$file_only_name);
		  			$file_only_name = $file_name_arr[0];
		  			$fileIdx = $file_name_arr[1];
		  			if($fileIdx)
		  			{
			 	 		$arrayFileName[$file_only_name][$fileIdx]=array
			 	 		(
			 	 			'filename'=>$file,
			 	 			'filedate'=>to_date($file_only_name)
			 	 		);
		  			}
		  			else 
		  			{
		  				$arrayFileName[$file_only_name][]=array
			 	 		(
			 	 			'filename'=>$file,
			 	 			'filedate'=>to_date($file_only_name)
			 	 		);
		  			}
		  		}
		  	}  
		   
		  }  
		  //通过ArrayList类对数组排序
		  foreach($arrayFileName as $k=>$group)
		  {
		  		$arr = new ArrayList($group);
		  		$arr->ksort();
		  		$arrayFileName[$k] = $arr->toArray();
		  }

	  	return   $arrayFileName;  
   }
   
	/**
     +----------------------------------------------------------
     * 获取数据库列表
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function getDbList() {
        if(!$dbs   =  Session::get('_databaseList_')) {
            $dbs =$this->db->query('show databases');
            Session::set('_databaseList_',$dbs);
        }
        $this->assign('dbs',$dbs);
    }

    /**
     +----------------------------------------------------------
     * 获取当前操作的数据库
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function getUseDb() {
        if(isset($_GET['dbName'])){
            $dbName   =  $_GET['dbName'];
            Session::set('useDb',$dbName);
        }elseif(Session::get('useDb')) {
            $dbName   =  Session::get('useDb');
        }else{
            $dbName   =  conf('DB_NAME');
            Session::set('useDb',$dbName);
        }
        $this->assign('useDb',$dbName);
        return $dbName;
    }
    
	/**
     +----------------------------------------------------------
     * 执行SQL语句
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function execute()
    {
        $sql  = trim($_REQUEST['sql']);
        if(MAGIC_QUOTES_GPC) {
            $sql   = stripslashes($sql);
        }
        if(empty($sql)) {
            $this->error('SQL不能为空！');
        }
       $this->db->execute('USE '.Session::get('useDb'));
        if(!empty($_POST['bench'])) {
           $this->db->execute('SET PROFILING=1;');
        }
        $startTime	=	microtime(TRUE);
        $queryIps = 'INSERT|UPDATE|DELETE|REPLACE|'
                . 'CREATE|DROP|'
                . 'LOAD DATA|SELECT .* INTO|COPY|'
                . 'ALTER|GRANT|TRUNCATE|REVOKE|'
                . 'LOCK|UNLOCK';
        if (preg_match('/^\s*"?(' . $queryIps . ')\s+/i', $sql)) {
            $result=   $this->db->execute($sql);
            $type = 'execute';
        }else {
            $result=   $this->db->query($sql);
            $type = 'query';
        }
        $runtime	 =	 number_format((microtime(TRUE) - $startTime), 6);
        if(!empty($_POST['record'])) {
            // 记录执行SQL语句
            Log::write('RunTime:'.$runtime.'s SQL = '.$sql,Log::SQL);
        }
        if(false !== $result) {
            $array[] =  $runtime.'s';
            if(!empty($_POST['bench'])) {
                $data   = $this->db->query('SHOW PROFILE');
                $fields = array_keys($data[0]);
                $a[] = $fields;
                foreach($data as $key=>$val) {
                    $val  = array_values($val);
                    $a[] = $val;
                }
                $array[] =  $a;
            }else{
                $array[]  = '';
            }
            if($type == 'query') {
                if(empty($result)) {
                    $this->ajaxReturn($array,'SQL执行成功！',1);
                }
                $fields = array_keys($result[0]);
                $array[] = $fields;
                foreach($result as $key=>$val) {
                    $val  = array_values($val);
                    $array[] = $val;
                }
                $this->ajaxReturn($array,'SQL执行成功！',1);
            }else {
                $this->ajaxReturn($array,'SQL执行成功！',1);
            }
        }else {
            $this->error('SQL错误！');
        }
    }
    
    public function getTables() {
        $dbName   =  $_REQUEST['db'];
        Session::set('useDb',$dbName);
        // 获取数据库的表列表
        $tables   = $this->db->getTables($dbName);
        $this->ajaxReturn($tables,'数据表获取完成',1);
    }
}
?>