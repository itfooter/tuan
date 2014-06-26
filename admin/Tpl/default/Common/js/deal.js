function init_dealform()
{
	$("form").bind("submit",function(){
		var attr_select = $(".attr_select_box");
		if(attr_select.length>0)
		{
			for(i=0;i<attr_select.length;i++)
			{
				if($(attr_select[i]).val()=='')
				{
					alert(LANG['ATTR_SETTING_EMPTY']);
					return false;
				}
			}
		}		
		
	});
	//绑定副标题20个字数的限制
	$("input[name='sub_name']").bind("keyup change",function(){
		if($(this).val().length>20)
		{
			$(this).val($(this).val().substr(0,20));
		}		
	});
	
	//绑定套餐券时间行显示
	$("select[name='is_coupon']").bind("change",function(){
		load_coupon_time();
	});
	
	//绑定套餐商品类型，显示属性
	$("select[name='deal_goods_type']").bind("change",function(){
		load_attr_html();
	});
	
	if($("select[name='city_id']").val()!="0"){
		load_area_list()
	}
	$("select[name='city_id']").bind("change",function(){
		load_area_list();
	});
	
	//绑定配送行的显示
	$("select[name='is_delivery']").bind("change",function(){
		load_weight();
	});
	
	 $("select[name='buy_type']").bind("change",function(){
	 	switch_buy_type();
	 });
	 
	 $("select[name='free_delivery']").bind("change",function(){
		 load_free_delivery();
	 });
	 $("select[name='define_payment']").bind("change",function(){
		 load_payment_box();
	 });
	 
	switch_buy_type();
	load_attr_html();
	load_coupon_time();
	load_weight();
	load_free_delivery();
	load_payment_box();
}

function load_payment_box()
{
	var define_payment = $("select[name='define_payment']").val();
	if(define_payment==1)
	{
		$(".define_payment").show();
	}
	else
	{
		$(".define_payment").hide();
	}	
}
function load_free_delivery()
{
	var free_delivery = $("select[name='free_delivery']").val();
	if(free_delivery==1)
	{
		$(".free_delivery").show();
	}
	else
	{
		$(".free_delivery").hide();
	}
}

//积分兑换和普通购买的切换
function switch_buy_type()
{
	var buy_type = $("select[name='buy_type']").val();
	if(buy_type==1)
	{
		$("select[name='define_payment']").val(0);
		$(".buy_type_0").find(".textbox").val("");
		$(".buy_type_0").hide();
		
	}
	else
	{
		if(buy_type==2)
		{
			$("#price_title").html(LANG['DEAL_ORDER_PRICE']);
		}
		if(buy_type==3)
		{
			$("#price_title").html(LANG['DEAL_SECOND_PRICE']);
		}
		if(buy_type==0)
		{
			$("#price_title").html(LANG['DEAL_CURRENT_PRICE']);
		}
		$(".buy_type_0").show();
	}
}

function load_attr_html()
{
		deal_goods_type = $("select[name='deal_goods_type']").val();
		deal_id = $("input[name='id']").val();
		if(deal_goods_type>0)
		{
			$("#deal_attr_row").show();
			$.ajax({ 
				url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=attr_html&deal_goods_type="+deal_goods_type+"&deal_id="+deal_id, 
				data: "ajax=1",
				success: function(obj){
					$("#deal_attr").html(obj);
				}
		});
		}
		else
		{
			$("#deal_attr_row").hide();
			$("#deal_attr").html("");
		}
}

function load_coupon_time()
{
		if($("select[name='is_coupon']").val()==0)
		{
			$(".coupon_time").hide();
		}
		else
		{
			$(".coupon_time").show();
		}
}

function load_weight()
{
		if($("select[name='is_delivery']").val()==0)
		{
			$(".weight_row").hide();
			$(".free_delivery").hide();
			$("select[name='free_delivery']").val(0);
		}
		else
		{
			$(".weight_row").show();
		}
}

//加载属性库存表
function load_attr_stock(obj)
{
	if(obj)
	{
		 attr_cfg_json = '';
		 attr_stock_json = '';
	}
	

	if($(".deal_attr_stock:checked").length>0)
	{
			$(".max_bought_row").find("input[name='max_bought']").val("");
			$(".max_bought_row").hide();
	}
	else
	{
			$(".max_bought_row").show();
	}
	//初始化deal_attr_stock_hd
	var deal_attr_stock_box = $(".deal_attr_stock");
	for(i=0;i<deal_attr_stock_box.length;i++)
	{
		var v = $(deal_attr_stock_box[i]).attr("checked")?1:0;
		$(deal_attr_stock_box[i]).parent().find(".deal_attr_stock_hd").val(v);
	}
	var box = $(".deal_attr_stock:checked");
	if(!box.length>0)
	{
		$("#stock_table").html("");
		return;
	}
	
	var x = 1; //行数
	var y = 0; //列数
	var attr_id = 0;
	var attr_item_count = 0; //每组属性的个数
	var attr_arr = new Array();
	for(i=0;i<box.length;i++)
	{
		if($(box[i]).attr("rel")!=attr_id)
		{
			y++;
			attr_id = $(box[i]).attr("rel");
			attr_arr.push(attr_id);
		}
		else
		{
			attr_item_count++;
		}
	}

	//开始计算行数
	for(i=0;i<attr_arr.length;i++)
	{
		x = x * parseInt($("input[name='deal_attr_stock["+attr_arr[i]+"][]']:checked").length);
	}	
	var html = "<table width='100%' style='border-left: solid #ccc 1px; border-top: solid #ccc 1px;'>";	
	html += "<tr>";
	for(j=0;j<attr_arr.length;j++)
	{
		html+="<th>"+$("#title_"+attr_arr[j]).html()+"</th>";
	}
	html+="<th>"+LANG['DEAL_MAX_BOUGHT_TIP']+"</th>";
	html +="</tr>";
	
	for(i=0;i<x;i++)
	{
		html += "<tr>";
		for(j=0;j<attr_arr.length;j++)
		{
			html+="<td><select name='stock_attr["+attr_arr[j]+"][]' class='attr_select_box' onchange='check_same(this);'><option value=''>"+LANG['EMPTY_SELECT']+"</option>";
			
			//开始获取相应的选取值
			var cbo = $("input[name='deal_attr_stock["+attr_arr[j]+"][]']:checked");
			for(k=0;k<cbo.length;k++)
			{
				var cnt = $(cbo[k]).parent().find("*[name='deal_attr["+attr_arr[j]+"][]']").val();				
				html =  html + "<option value='"+cnt+"'";
				if(attr_cfg_json!=''&&attr_cfg_json[i][attr_arr[j]]==cnt)
				html = html + " selected='selected' ";
				html = html + ">"+cnt+"</option>";
			}
			
			html+="</select></td>";
		}
		html+="<td><input type='text' class='textbox' style='width: 50px;' name='stock_cfg_num[]' value='";
		if(attr_stock_json!='')
		html = html + attr_stock_json[i]['stock_cfg'];		
		html=html+"' /> <input type='hidden' name='stock_cfg[]' value='";
		if(attr_stock_json!='')
		html+=attr_stock_json[i]['attr_str'];
		html+="' /> </td>";
		html +="</tr>";
	}	
	html += "</table>";
	$("#stock_table").html(html);
}

//检测当前行的配置
function check_same(obj)
{
	var selectbox = $(obj).parent().parent().find("select");
	var row_value = '';
	for(i=0;i<selectbox.length;i++)
	{
		if($(selectbox[i]).val()!='')
			row_value += $(selectbox[i]).val();
		else
		{
			$(obj).parent().parent().find("input[name='stock_cfg[]']").val("");
			return;
		}
	}
	//开始检测是否存在该配置
	var stock_cfg = $("input[name='stock_cfg[]']");
	for(i=0;i<stock_cfg.length;i++)
	{
		if(row_value==$(stock_cfg[i]).val()&&row_value!=''&&stock_cfg[i]!=obj)
		{
			alert(LANG['SPEC_EXIST']);
			$(obj).parent().parent().find("input[name='stock_cfg[]']").val("");
			$(obj).val("");
			return;
		}
	}
	$(obj).parent().parent().find("input[name='stock_cfg[]']").val(row_value);
}

function load_area_list()
{
	var city_id = $("select[name='city_id']").val();
	if($("input[name='id']").length>0)
		var id = $("input[name='id']").val();
	else
		id = 0;
	$.ajax({
		  url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=area_list&city_id="+city_id+"&id="+id,
		  cache: false,
		  success:function(data)
		  {
			$("#area_list").html(data);
		  }
		}); 
} 