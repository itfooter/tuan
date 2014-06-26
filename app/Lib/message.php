<?php

function get_comments($id)
{
	//获取商品评论分类
	$types = $GLOBALS['db']->getAll("select ectl.comment_type_id from ".DB_PREFIX."deal edl,".DB_PREFIX."comment_type_link ectl where edl.cate_id=ectl.category_id and edl.id = ".$id);
	//获取星级评论
	$comments="";
	foreach($types as $k=>$v)
	{
		
		$name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."comment_type where is_effect = 1 and id=".$v['comment_type_id']);
		if($name)
			$comments['com'][$name] = intval($GLOBALS['db']->getOne("select sum(point) from ".DB_PREFIX."comment where type=".$v['comment_type_id']." and rel_id=".$id));
	}
	//获取总评
	$mainid = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."comment_type where is_main=1");
	for($point = 1; $point<6; $point++)
	$comments['main'][$point] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."comment where type=".$mainid." and point=".$point." and rel_id=".$id);
	$comments['mainc'] = intval($GLOBALS['db']->getOne("select sum(point) from ".DB_PREFIX."comment where type=".$mainid." and rel_id=".$id));
	return $comments;
}
function get_message_list($limit,$where='')
{
	$city_id = intval($GLOBALS['deal_city']['id']);
				$ids = $GLOBALS['cache']->get("DEAL_CITY_BELONE_IDS_".$city_id);
				if($ids===false)
				{					
					$ids_util = new ChildIds("deal_city");
					$ids = $ids_util->getChildIds($city_id);
					$ids[] = $city_id;
					//开始取出父地区ID
					$r_city_id = $city_id;
					while($r_city_id!=0){
						$r_city_id = $GLOBALS['db']->getOne("select pid from ".DB_PREFIX."deal_city where id = ".$r_city_id);
						if($r_city_id!=0)
						$ids[] = $r_city_id;
					}
					$GLOBALS['cache']->get("DEAL_CITY_BELONE_IDS_".$city_id,$ids);
				}
				
	$sql = "select * from ".DB_PREFIX."message where pid = 0 and city_id in( ".implode(",",$ids).")";

	$sql_count = "select count(*) from ".DB_PREFIX."message where pid = 0 and city_id in( ".implode(",",$ids).")";
	if($where!='')
	{
		$sql .= " and ".$where;
		$sql_count .=  " and ".$where;
	}
	
	$sql.=" order by create_time desc ";
	$sql.=" limit ".$limit;
	$list = $GLOBALS['db']->getAll($sql);
	$count = $GLOBALS['db']->getOne($sql_count);
	
	return array('list'=>$list,'count'=>$count);
}
?>