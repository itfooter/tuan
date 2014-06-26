<?php if (!defined('THINK_PATH')) exit();?>

<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="__TMPL__Common/style/style.css" />
<script type="text/javascript">
 	var VAR_MODULE = "<?php echo conf("VAR_MODULE");?>";
	var VAR_ACTION = "<?php echo conf("VAR_ACTION");?>";
	var MODULE_NAME	=	'<?php echo MODULE_NAME; ?>';
	var ACTION_NAME	=	'<?php echo ACTION_NAME; ?>';
	var ROOT = '__APP__';
	var ROOT_PATH = '<?php echo APP_ROOT; ?>';
	var CURRENT_URL = '<?php echo trim($_SERVER['REQUEST_URI']);?>';
</script>
<script type="text/javascript" src="__TMPL__Common/js/jquery.js"></script>
<script type="text/javascript" src="__TMPL__Common/js/jquery.timer.js"></script>
<script type="text/javascript" src="__TMPL__Common/js/script.js"></script>
<script type="text/javascript" src="__ROOT__/admin/Runtime/lang.js"></script>
<script type='text/javascript'  src='__ROOT__/admin/public/kindeditor/kindeditor.js'></script>
</head>
<body>
<div id="info"></div>

<!--<script src="http://ditu.google.cn/maps?file=api&v=2&key=<?php echo conf('GOOGLE_MAP_API_KEY');?>&sensor=true"
        type="text/javascript">
</script>-->
<style type="text/css">
    td span label{float:left; padding:3px; margin:2px; background:#E6E6E6; cursor:pointer; display:inline-block;}
    td span label.active{background:#F60; color:#fff;}
    #container{height:200px; width: 200px; float:left;}  
    #container_front{width: 600px; height:500px; border: 1px solid #000; position: absolute; top: 10px; background-color: #fff; overflow: hidden;}
    #container_m{ width: 550px; height: 450px; margin: 0 auto;}
    #cancel_btn{display: block; width: 600px; height: 18px; line-height: 18px; text-align: right;}
</style>

<script type="text/javascript" src="__TMPL__Common/js/map.js"></script>
<script type="text/javascript">
function loadGeoInfo()
{
	var address = $("input[name='api_address']").val();
    var geocoder = new GClientGeocoder();
    geocoder.getLatLng(
    address,
    function(point)
    {	
    	if(!point)
    	{
    		alert("<?php echo L("NO_ADDRESS_API");?>");
    	}
    	else
    	{
			$("input[name='xpoint']").val(point.x);
			$("input[name='ypoint']").val(point.y);
    		return;
    	}				
    });
}
</script>
<script type="text/javascript">
var blue_point = "__ROOT__/system/blue_point.png";
var red_point = "__ROOT__/system/red_point.png";
	$(document).ready(function(){
		 $("input[name='search_api']").bind("click",function(){  
		 	var api_address = $("input[name='api_address']").val();
			var city=$("select[name='city_id']").find("option:selected").attr("rel");
			if ($.trim(api_address) == '') {
				alert("<?php echo L("INPUT_KEY_PLEASE");?>");
			}
			else 
			{
				search_api(api_address, city);
			}
        });
		draw_map('0','0');
		$("#container_front").hide();
        $("#cancel_btn").bind("click",function(){ $("#container_front").hide(); });
        $("input[name='chang_api']").bind("click",function(){ 
            editMap($("input[name='xpoint']").attr('value'),$("input[name='ypoint']").attr('value'));
        });
		
		
	});

</script>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=1.2"></script> 
<div class="main">
<div class="main_title">[<?php echo ($supplier_info["name"]); ?>] <?php echo L("ADD_LOCATION");?> <a href="<?php echo u("SupplierLocation/index",array("supplier_id"=>$supplier_info['id']));?>" class="back_list"><?php echo L("BACK_LIST");?></a></div>
<div class="blank5"></div>
<form name="edit" action="__APP__" method="post" enctype="multipart/form-data">
<table class="form" cellpadding=0 cellspacing=0>
	<tr>
		<td colspan=2 class="topTd"></td>
	</tr>
	<tr>
		<td class="item_title"><?php echo L("NAME");?>:</td>
		<td class="item_input"><input type="text" class="textbox require" name="name" /></td>
	</tr>
	<tr>
		<td class="item_title"><?php echo L("LOCATION_ADDRESS");?>:</td>
		<td class="item_input"><textarea class="textarea" name="address" ></textarea></td>
	</tr>	
	<tr>
		<td class="item_title"><?php echo L("LOCATION_ROUTE");?>:</td>
		<td class="item_input"><textarea class="textarea" name="route" ></textarea></td>
	</tr>	
	<tr>
		<td class="item_title"><?php echo L("LOCATION_TEL");?>:</td>
		<td class="item_input"><input type="text" class="textbox" name="tel" /></td>
	</tr>
	<tr>
		<td class="item_title"><?php echo L("LOCATION_CONTACT");?>:</td>
		<td class="item_input"><input type="text" class="textbox" name="contact" /></td>
	</tr>
	<tr>
		<td class="item_title"><?php echo L("LOCATION_OPENTIME");?>:</td>
		<td class="item_input"><input type="text" class="textbox" name="open_time" /></td>
	</tr>
<!--	<tr>
		<td class="item_title"><?php echo L("LOCATION_API_ADDRESS");?>:</td>
		<td class="item_input">
			<input type="text" class="textbox" name="api_address" />
			<input type="button" class="button" value="<?php echo L("LOCATE_POINT");?>" onclick="loadGeoInfo();" />
		</td>
	</tr>
	<tr>
		<td class="item_title"><?php echo L("LOCATION_API_POINT");?>:</td>
		<td class="item_input">
			<?php echo L("XPOINT");?>:<input type="text" class="textbox" name="xpoint" />
			<?php echo L("YPOINT");?>:<input type="text" class="textbox" name="ypoint" />
		</td>
	</tr>
-->	

<tr>
            <td class="item_title">地图定位</td>
            <td class="item_input">            	
            	关键词：<input type="text" class="textbox" name="api_address" value="" /> 
				<input type="button" value="查找" class="button" name="search_api" id="search_api" >
				<div style="height:10px; clear:both;"></div>
                <div id="container"></div>
				<div style="height:10px; clear:both;"></div>
                <script type="text/javascript"></script> 
                <input type="button" value="手动修改" name="chang_api" id="chang_api">
                <div style="position:relative; top:-400px;">
                    <div  id="container_front">
                        <a href="#" id="cancel_btn">取消</a>
                        <div id="container_m"></div>
                    </div>
                </div>
				<input type="hidden" name="xpoint" />
				<input type="hidden" name="ypoint" />
            </td>
    </tr>
        <tr>
		<td class="item_title"><?php echo L("LOCATION_BRIEF");?>:</td>
		<td class="item_input"><textarea class="textarea" name="brief" ></textarea></td>
	</tr>
	<tr>
		<td class="item_title"></td>
		<td class="item_input">
			<!--隐藏元素-->
			<input type="hidden" name="supplier_id" value="<?php echo ($supplier_info["id"]); ?>" />
			<input type="hidden" name="<?php echo conf("VAR_MODULE");?>" value="SupplierLocation" />
			<input type="hidden" name="<?php echo conf("VAR_ACTION");?>" value="insert" />
			<!--隐藏元素-->
			<input type="submit" class="button" value="<?php echo L("ADD");?>" />
			<input type="reset" class="button" value="<?php echo L("RESET");?>" />
		</td>
	</tr>
	<tr>
		<td colspan=2 class="bottomTd"></td>
	</tr>
</table>	 
</form>
</div>
</body>
</html>