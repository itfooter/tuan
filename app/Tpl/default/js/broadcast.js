$(document).ready(function(){   
//首页顶部广告轮播

	var width=948;
	var sWidth =width; //获取焦点图的宽度（显示面积）
	var lent = $("#focus ul li").length; //获取焦点图个数
	var tourse = 0;
	var picTimer;
	
	var btn = "<div class='btnBg'></div><div class='btn'>";
	for(var i=0; i < lent; i++) {
		btn += "<span></span>";
	}
	btn += "</div>";     
	$("#focus").append(btn);     
	$("#focus .btnBg").css("opacity",0);                                              
	$("#focus ul li img").css("width", width).css("height", 310);/*图片样式 */
	$("#focus ul li ").css("width", width);
	$("#focus ul ").css("width", width);																								


	//为小按钮添加鼠标滑入事件，以显示相应的内容
	$("#focus .btn span").mouseenter(function() {   
		tourse = $("#focus .btn span").index(this);  
		focusshow(tourse);        
	}).eq(0).trigger("mouseenter");   

    $("#focus ul").css("width",sWidth * (lent)); 
	//鼠标滑上焦点图时停止自动播放，滑出时开始自动播放
	$("#focus").hover(function() {clearInterval(picTimer);},function() {
		picTimer = setInterval(function() {
			focusshow(tourse);
			
			tourse++;//用于循环
			if(tourse == lent) {tourse = 0;}
		},4000); //此4000代表自动播放的间隔，单位：毫秒
	}).trigger("mouseleave");  
	
	//显示图片函数，根据接收的index值显示相应的内容
	function focusshow(tourse) { //普通切换
		var nowLeft = -tourse*sWidth; 
		$("#focus ul").stop(true,false).animate({"right":nowLeft},450); 
		$("#focus .btn span").removeClass("on").eq(tourse).addClass("on"); //为当前的按钮切换到选中的效果
		}
		
	 //首页精彩活动
	 var newsboxwidth=$('#events').width();
	 var len=$('#event_ul li').length;
	 var index=0;
	//下一页
	 $('#nextpage').click(function(){
	 	index+=1;
		if(index==len){index=0};
		showpic(index);
	 })
	
	 //上一页
	 $('#prepage').click(function(){
	 	index -= 1;//index=index-1=-1
		if(index==-1){index=len-1};
		showpic(index);
	 })



 });
 
