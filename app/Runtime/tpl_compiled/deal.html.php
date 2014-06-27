<?php echo $this->fetch('inc/header.html'); ?> 
<style>
 #container{height:250px;}  
</style>
<div id="bdw" class="bdw">
	<div id="bd" class="cf">
		<?php if ($this->_var['deal']['buy_status'] == 2): ?>
		<?php echo $this->fetch('inc/sold_out_tip.html'); ?> 
		<?php endif; ?>
		<?php if ($this->_var['coupon_data']): ?>
		<?php echo $this->fetch('inc/new_coupon_tip.html'); ?> 
		<?php endif; ?>
		<div id="deal-default">
			<?php if ($this->_var['deal_cate_list']): ?>
			<div id="dashboard" class="dashboard cf">
					<ul>
						<?php $_from = $this->_var['deal_cate_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cate');if (count($_from)):
    foreach ($_from AS $this->_var['cate']):
?>
                        <?php if ($this->_var['cate']['pid'] == 0): ?>
						<li <?php if ($this->_var['cate']['current'] == 1): ?>class="current"<?php endif; ?>><a href="<?php echo $this->_var['cate']['url']; ?>"><?php echo $this->_var['cate']['name']; ?></a>
						<span></span>
						</li>
                        <?php endif; ?>
						<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>													
					</ul>
			</div>
			<?php endif; ?>
			<div id="content">
				<?php if (! $this->_var['deal_cate_list']): ?>
				<?php echo $this->fetch('inc/share.html'); ?>
				<?php endif; ?> 
				<div id="deal-intro" class="cf">
					<h1>
						<span class='sub_title'>
							<?php if (count ( $this->_var['deal_city_list'] ) > 1): ?>
							<?php echo $this->_var['deal_city']['name']; ?>
							<?php endif; ?>
							<?php if ($this->_var['deal']['buy_type'] == 0): ?>
								<?php echo $this->_var['LANG']['DEAL_CURRENT']; ?>
							<?php endif; ?>
							<?php if ($this->_var['deal']['buy_type'] == 2): ?>
								<?php echo $this->_var['LANG']['DEAL_ORDER']; ?>
							<?php endif; ?>
							<?php if ($this->_var['deal']['buy_type'] == 3): ?>
								<?php echo $this->_var['LANG']['DEAL_SECOND']; ?>
							<?php endif; ?>						
						</span> <?php echo $this->_var['deal']['name']; ?>				
					</h1>
					<div class="main">
						<div class="deal-buy">
							<div class="deal-price-tag"></div>
							<p class="deal-price" id="deal-price">
								<strong><?php echo $this->_var['deal']['current_price_format']; ?></strong>
								<!--begin button status-->
								<?php if ($this->_var['deal']['time_status'] == 0): ?> 
								<span>		
									<a href="javascript:void(0);">					
										<img src="<?php echo $this->_var['TMPL']; ?>/images/bg-deal-wait-text.gif">	
									</a>								
								</span>
								<?php endif; ?>
								<?php if ($this->_var['deal']['time_status'] == 1): ?>
									<?php if ($this->_var['deal']['buy_status'] == 2): ?> 
									<span>
										<a href="javascript:void(0);">
											<img src="<?php echo $this->_var['TMPL']; ?>/images/bg-deal-soldout-text.gif">
										</a>
									</span>
									<?php else: ?>
									<span>
										<a href="javascript:void(0);" onclick="add_cart(<?php echo $this->_var['deal']['id']; ?>)">
											<?php if ($this->_var['deal']['is_lottery'] == 1 && $this->_var['deal']['current_price'] == 0): ?>
												<img src="<?php echo $this->_var['TMPL']; ?>/images/button-deal-lottery.gif">
											<?php else: ?>	
												<?php if ($this->_var['deal']['buy_type'] == 0): ?>
													<img src="<?php echo $this->_var['TMPL']; ?>/images/button-deal-buy.gif">
												<?php endif; ?>
												<?php if ($this->_var['deal']['buy_type'] == 2): ?>
													<img src="<?php echo $this->_var['TMPL']; ?>/images/button-deal-order.gif">
												<?php endif; ?>
												<?php if ($this->_var['deal']['buy_type'] == 3): ?>
													<img src="<?php echo $this->_var['TMPL']; ?>/images/button-deal-second.gif">
												<?php endif; ?>
											<?php endif; ?>
										</a>
									</span>
									<?php endif; ?>
								<?php endif; ?>
								<?php if ($this->_var['deal']['time_status'] == 2): ?> 
									<?php if ($this->_var['deal']['buy_status'] == 2): ?> 
									<span>
											<a href="javascript:void(0);">
												<img src="<?php echo $this->_var['TMPL']; ?>/images/bg-deal-soldout-text.gif">
											</a>
										</span>
									<?php else: ?>
									<span>		
										<a href="javascript:void(0);">					
											<img src="<?php echo $this->_var['TMPL']; ?>/images/bg-deal-expired-text.gif">	
										</a>								
									</span>
									<?php endif; ?>
								<?php endif; ?>
								<!--end button status-->		
							</p>
						</div>
						<table class="deal-discount">
							<tbody>
								<tr>
									<th><?php echo $this->_var['LANG']['ORIGIN_PRICE']; ?></th>
									<th><?php echo $this->_var['LANG']['DISCOUNT']; ?></th>
									<th><?php echo $this->_var['LANG']['SAVE_PRICE']; ?></th>
								</tr>
								<tr>
									<td><?php echo $this->_var['deal']['origin_price_format']; ?></td>
									<td><?php echo $this->_var['deal']['discount']; ?><?php echo $this->_var['LANG']['DISCOUNT_OFF']; ?></td>
									<td><?php echo $this->_var['deal']['save_price_format']; ?></td>
								</tr>

							</tbody>
						</table>
						<?php if (( $this->_var['deal']['begin_time'] != 0 && $this->_var['deal']['time_status'] == 0 ) || ( $this->_var['deal']['end_time'] != 0 && $this->_var['deal']['time_status'] == 1 )): ?> 
						<div id="deal-timeleft-box">
							<div id="deal-timeleft" class="deal-box deal-timeleft deal-on">
								<h3><?php echo $this->_var['LANG']['TIME_LEFT']; ?></h3>
								<div class="limitdate">
									<ul id="counter"></ul>
								</div>
							</div>						
						</div>
						<?php endif; ?>

						<div id=deal-status class="deal-box deal-status deal-status-open">
							
						<?php if ($this->_var['deal']['time_status'] == 0): ?>
							<?php echo $this->_var['LANG']['DEAL_NOT_BEGIN']; ?>
							<br />
							<?php 
$k = array (
  'name' => 'sprintf',
  'format' => $this->_var['LANG']['DEAL_BEGIN_FORMAT'],
  'value' => $this->_var['deal']['begin_time_format'],
);
echo $k['name']($k['format'],$k['value']);
?>
						<?php endif; ?>
						<?php if ($this->_var['deal']['time_status'] == 1): ?> 
							<?php if ($this->_var['deal']['buy_status'] == 0): ?> 
								<p class=deal-buy-tip-top><?php echo $this->_var['deal']['deal_success_num']; ?></p>
								<p class="deal-buy-tip-notice"><?php echo $this->_var['LANG']['DEAL_LIMIT_TIP']; ?></p>
								<div class="progress-pointer" style="padding-left:<?php echo $this->_var['deal']['current_bought'] / $this->_var['deal']['min_bought'] * 194 -5; ?>px;"><span></span></div>
								<div class="progress-bar">
							
									<div class="progress-left" style="width:<?php echo $this->_var['deal']['current_bought'] / $this->_var['deal']['min_bought'] * 194; ?>px;"></div>
									<div class="progress-right "></div>
								</div>
								<div class="cf">
									<div class="min">0</div>
									<div class="max"><?php echo $this->_var['deal']['min_bought']; ?></div>
								</div>
								<p class="deal-buy-tip-btm"><?php echo $this->_var['deal']['success_less']; ?></p>
							<?php endif; ?>
							<?php if ($this->_var['deal']['buy_status'] == 1): ?> 
								<p class=deal-buy-tip-top><?php echo $this->_var['deal']['deal_success_num']; ?></p>
								<p class="deal-buy-tip-notice">
									<?php if ($this->_var['deal']['is_lottery'] == 0): ?>
									<?PHP
									$c_deal = $this->_var['deal'];
									if($c_deal['max_bought'] != 0 && $c_deal['max_bought'] - $c_deal['buy_count'] <= 10)
									{
										echo sprintf($GLOBALS['lang']['REMAIN_TIP'],$c_deal['max_bought'] - $c_deal['buy_count']);
									}
									?>
									<?php echo $this->_var['LANG']['DEAL_LIMIT_TIP']; ?>
									<?php else: ?>
									<?php 
$k = array (
  'name' => 'sprintf',
  'f' => $this->_var['LANG']['TOTAL_LOTTERY_COUNT'],
  'p' => $this->_var['deal']['lottery_count'],
);
echo $k['name']($k['f'],$k['p']);
?>
									<br /> <a href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'uc_order#lottery',
);
echo $k['name']($k['value']);
?>" style="color:#f30;"><?php echo $this->_var['LANG']['INVITE_LOTTERY_DEAL_TIP']; ?></a>
									<?php endif; ?>
								</p>
								<p class=deal-buy-on><?php echo $this->_var['LANG']['DEAL_SUCCESS_CONTINUE_BUY']; ?></p>
								<p class=deal-buy-tip-btm><?php echo $this->_var['deal']['success_time_tip']; ?></p>
							<?php endif; ?>
							<?php if ($this->_var['deal']['buy_status'] == 2): ?> 
							<p class=deal-buy-tip-top><?php echo $this->_var['deal']['deal_success_num']; ?></p>
							<img src="<?php echo $this->_var['TMPL']; ?>/images/bg-sold-out.gif">
							<?php endif; ?>							
						<?php endif; ?>
						<?php if ($this->_var['deal']['time_status'] == 2): ?> 
							<?php if ($this->_var['deal']['buy_status'] == 0): ?> 
							<img src="<?php echo $this->_var['TMPL']; ?>/images/bg-deal-expired-fail.gif">
							<?php endif; ?>
							<?php if ($this->_var['deal']['buy_status'] == 1): ?> 
							<p class=deal-buy-tip-top><?php echo $this->_var['deal']['deal_success_num']; ?></p>
							<img src="<?php echo $this->_var['TMPL']; ?>/images/bg-deal-expired-success.gif">
							<?php endif; ?>
							<?php if ($this->_var['deal']['buy_status'] == 2): ?> 
							<p class=deal-buy-tip-top><?php echo $this->_var['deal']['deal_success_num']; ?></p>
							<img src="<?php echo $this->_var['TMPL']; ?>/images/bg-sold-out.gif">
							<?php endif; ?>
						<?php endif; ?>
						
						</div>
					</div>
					<div class=side>
						<div id="goods_imgs" class="deal-buy-cover-img">
							<div class="mid">
								<ul>
								<?php $_from = $this->_var['deal']['image_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'image');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['image']):
?>
								<li <?php if ($this->_var['key'] == 0): ?>class="first"<?php endif; ?>>
								<img src="<?php echo $this->_var['image']['img']; ?>">
								</li>
								<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>								
								</ul>
								<div id="img_list">
									<?php if (count ( $this->_var['deal']['image_list'] ) > 1): ?>
									<?php $_from = $this->_var['deal']['image_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'image');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['image']):
?>
									<a <?php if ($this->_var['key'] == 0): ?>class="active"<?php endif; ?> ref="<?php echo $this->_var['key']+1;?>"><?php echo $this->_var['key']+1;?></a>
									<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>	
									<?php endif; ?>							
								</div>
							</div>
						</div>					
						<div class=digest>
							<?php echo $this->_var['deal']['brief']; ?>
						</div>
					</div>
				</div>

				<div id=deal-stuff class=cf>
					<?php if ($this->_var['deal_cate_list']): ?>
					<?php echo $this->fetch('inc/share.html'); ?>
					<div class="blank1"></div>
					<?php endif; ?> 
					<div class="box box-split">
						<div class=box-top></div>
						<div class="box-content cf">
							<div class=main  <?php if (app_conf ( "SUPPLIER_DETAIL" ) == 0 || $this->_var['deal'] [ 'hide_supplier_detail' ] == 1): ?> style="width:650px;"<?php endif; ?>>
								<H2><?php echo $this->_var['LANG']['DEAL_DETAIL']; ?></H2>
								<?php echo $this->_var['deal']['description']; ?>
								<div class="blank"></div>
								<?php if (app_conf ( "SUPPLIER_DETAIL" ) == 1 && $this->_var['deal'] [ 'hide_supplier_detail' ] == 0): ?>
								<H2><?php echo $this->_var['LANG']['SUPPLIER_DETAIL']; ?></H2>
								<?php echo $this->_var['deal']['supplier_info']['content']; ?>
								<?php endif; ?>
							</div>
							<?php if (app_conf ( "SUPPLIER_DETAIL" ) == 1 && $this->_var['deal'] [ 'hide_supplier_detail' ] == 0): ?>
							<div class=side>
								<div id=side-business>
									<H2><?php echo $this->_var['deal']['supplier_info']['name']; ?>  </H2>
									<?PHP if(count($this->_var['locations'])>1){ ?>
									 <div class="blank"></div>
									 <select name="locations" id="locations_select">
									 	<?php $_from = $this->_var['locations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'location');if (count($_from)):
    foreach ($_from AS $this->_var['location']):
?>
									 	<option value="<?php echo $this->_var['location']['id']; ?>"><?php echo $this->_var['location']['name']; ?></option>
										<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
									 </select>
									 <div class="blank"></div>
									 <?PHP }?>
									<ul>	
										<span id="sp_location">
										<?php if ($this->_var['deal']['supplier_address_info']['xpoint'] && $this->_var['deal']['supplier_address_info']['ypoint']): ?>																	
										<li>
										<div id="container"></div>
                                                                                <span class="saler_map">
										                                                                        
                                                                               <a href="<?php echo $this->_var['deal']['supplier_address_info']['map']; ?>" target="_blank"><?php echo $this->_var['LANG']['VIEW_MAP']; ?></a>	
                                                                                </span>
										</li>	
										<?php endif; ?>					
										<?php if ($this->_var['deal']['supplier_address_info']['address']): ?>				
										<li><?php echo $this->_var['deal']['supplier_address_info']['address']; ?></li>		
										<?php endif; ?>		
										<?php if ($this->_var['deal']['supplier_address_info']['tel']): ?>						
										<li><?php echo $this->_var['deal']['supplier_address_info']['tel']; ?>‎</li>	
										<?php endif; ?>	
										<?php if ($this->_var['deal']['supplier_address_info']['open_time']): ?>									
										<li><?php echo $this->_var['LANG']['OPEN_TIME']; ?>：<?php echo $this->_var['deal']['supplier_address_info']['open_time']; ?>‎</li>	
										<?php endif; ?>	
										<?php if ($this->_var['deal']['supplier_address_info']['route']): ?>										
										<li><?php echo $this->_var['LANG']['BUS_ROUTE']; ?>：<?php echo $this->_var['deal']['supplier_address_info']['route']; ?>‎</li>											
										<?php endif; ?>	
										</span>	
									</ul>
									<div class="blank"></div>
									<?php if ($this->_var['deal']['supplier_info']['preview']): ?>			
										<span style="text-align:center">										
										<img src="<?php echo $this->_var['deal']['supplier_info']['preview']; ?> " width="200">
										</span>		
									<?php endif; ?>
									
									<?php if ($this->_var['deal']['supplier_info']['is_effect'] == 1): ?>
									<div class="blank"></div>
									<div style="text-align:center">
									<a href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'supplier#show',
  'id' => $this->_var['deal']['supplier_info']['id'],
);
echo $k['name']($k['value'],$k['id']);
?>"><?php echo $this->_var['LANG']['VIEW_SUPPLIER_INFO']; ?></a>
									</div>
									<?php endif; ?>
								</div>
							</div>
							<?php endif; ?>
							<div class=clear></div>

						</div>
						<div class=box-bottom></div>
					</div>
				</div>
			</div>
			<?php echo $this->fetch('inc/side.html'); ?> 
	</div>
	<!-- bd end -->
</div>
<?php if (( $this->_var['deal']['begin_time'] != 0 && $this->_var['deal']['time_status'] == 0 ) || ( $this->_var['deal']['end_time'] != 0 && $this->_var['deal']['time_status'] == 1 )): ?> 
<script type="text/javascript">
<?php if ($this->_var['deal']['time_status'] == 1): ?> 
var endTime = <?php echo $this->_var['deal']['end_time']; ?>000;
var nowTime = <?php 
$k = array (
  'name' => 'get_gmtime',
);
echo $k['name']();
?>000;
var sysSecond = (endTime - nowTime) / 1000;
<?php endif; ?>
<?php if ($this->_var['deal']['time_status'] == 0): ?> 
var beginTime = <?php echo $this->_var['deal']['begin_time']; ?>000;
var nowTime = <?php 
$k = array (
  'name' => 'get_gmtime',
);
echo $k['name']();
?>000;
var sysSecond = (beginTime - nowTime) / 1000;
<?php endif; ?>
var interValObj;
setRemainTime();
function setRemainTime()
{	
	if (sysSecond > 0)
	{
		var second = Math.floor(sysSecond % 60);              // 计算秒     
		var minite = Math.floor((sysSecond / 60) % 60);       //计算分
		var hour = Math.floor((sysSecond / 3600) % 24);       //计算小时
		var day = Math.floor((sysSecond / 3600) / 24);        //计算天
		var timeHtml = "<li><span>"+hour+"</span>"+LANG['HOUR']+"</li><li><span>"+minite+"</span>"+LANG['MIN']+"</li>";
		if(day > 0)
			timeHtml ="<li><span>"+day+"</span>"+LANG['DAY']+"</li>" + timeHtml;
		
		timeHtml+="<li><span>"+second+"</span>"+LANG['SEC']+"</li>";
		
		try
		{
			$("#counter").html(timeHtml);
			sysSecond--;
		}
		catch(e){}}
	else
	{window.clearTimeout(interValObj);}
	interValObj = window.setTimeout("setRemainTime()", 1000); 	
}
</script>
<?php endif; ?>
<!--baidu-->
<script type="text/javascript" src="http://api.map.baidu.com/api?v=1.2"></script> 

<script type="text/javascript">
var red_point_new = APP_ROOT+"/system/red_point.png";

var xpoint='<?php echo $this->_var['deal']['supplier_address_info']['xpoint']; ?>';
var ypoint='<?php echo $this->_var['deal']['supplier_address_info']['ypoint']; ?>';

        var map = new BMap.Map("container"); //可以修改容器ID
        var opts = {type: BMAP_NAVIGATION_CONTROL_ZOOM}  
        map.addControl(new BMap.NavigationControl());  
       
        var point = new BMap.Point(xpoint,ypoint);
        
        // 将结果显示在地图上，并调整地图视野  
        map.centerAndZoom(point, 16);  
        var myIcon = new BMap.Icon(red_point_new, new BMap.Size(28, 38));  
      
        map.addOverlay(new BMap.Marker(point));
</script>
<!--图片轮播-->
<script type="text/javascript">

var errHideTimeOut;
var userMenuTimeOut;
var ecvTimeOut;
var imgListCurr=0;
var imgListNext=0;
var imgListCount=0;
var imgListInterval;

function imagesInit()
{
	imgListCount = $('#img_list a').size();
	
	if(imgListCount < 2)
		return;
	
	imgListInterval = setInterval('imagesRun()',3000);
	 
	$('#goods_imgs li,#img_list a').hover(function(){
		clearInterval(imgListInterval);
	},function(){
		imgListInterval = setInterval('imagesRun()',3000);
	});
	 
	$('#img_list a').click(function(){
		var index = $('#img_list a').index(this);
		if (imgListCurr != index){
			imagesPlay(index);
            imgListCurr = index;
        };
		return false;
    });
}

function imagesRun()
{
	imgListNext = imgListCurr + 1;
    if (imgListCurr == imgListCount - 1)
		imgListNext = 0;
		
	imagesPlay(imgListNext);
	
	imgListCurr++;
	
    if (imgListCurr > imgListCount - 1)
	{
		imgListCurr = 0;
		imgListNext = imgListCurr + 1;
	}
}

function imagesPlay(next)
{
	$('#goods_imgs li').eq(imgListCurr).css({'opacity':'0.5'}).animate({'left':'-440px','opacity':'1'},'slow',function(){
		$(this).css({'left':'440px'});
	}).end().eq(next).animate({'left':'0px','opacity':'1'},'slow',function(){
		$('#img_list a').siblings('a').removeClass('active').end().eq(next).addClass('active');
	});
}
function switch_location()
{
	var location_id = $("#locations_select").val();
	$.ajax({
		url: APP_ROOT+"/ajax.php?act=get_supplier_location&id="+location_id,
		success:function(html)
		{
			$("#sp_location").html(html);
		}
	});
}
$(document).ready(function(){
	imagesInit();
	$("#locations_select").bind("change",function(){
		switch_location();
	});
});
</script>
<?php echo $this->fetch('inc/footer.html'); ?>