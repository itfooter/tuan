<?php if ($this->_var['supplier_address_info']['xpoint'] && $this->_var['supplier_address_info']['ypoint']): ?>																	
<li>
	<div id="container"></div>
    <span class="saler_map">
        <a href="<?php echo $this->_var['supplier_address_info']['map']; ?>" target="_blank"><?php echo $this->_var['LANG']['VIEW_MAP']; ?></a>	
    </span>
</li>	
<?php endif; ?>					
<?php if ($this->_var['supplier_address_info']['address']): ?>				
<li><?php echo $this->_var['supplier_address_info']['address']; ?></li>		
<?php endif; ?>		
<?php if ($this->_var['supplier_address_info']['tel']): ?>						
<li><?php echo $this->_var['supplier_address_info']['tel']; ?>‎</li>	
<?php endif; ?>	
<?php if ($this->_var['supplier_address_info']['open_time']): ?>									
<li><?php echo $this->_var['LANG']['OPEN_TIME']; ?>：<?php echo $this->_var['supplier_address_info']['open_time']; ?>‎</li>	
<?php endif; ?>	
<?php if ($this->_var['supplier_address_info']['route']): ?>										
<li><?php echo $this->_var['LANG']['BUS_ROUTE']; ?>：<?php echo $this->_var['supplier_address_info']['route']; ?>‎</li>											
<?php endif; ?>				
<!--baidu-->
<script type="text/javascript" src="http://api.map.baidu.com/api?v=1.2"></script> 
<script type="text/javascript">
var blue_point = "__ROOT__/system/blue_point.png";

var red_point = "__ROOT__/system/red_point.png";
var xpoint='<?php echo $this->_var['supplier_address_info']['xpoint']; ?>';
var ypoint='<?php echo $this->_var['supplier_address_info']['ypoint']; ?>';
var map = new BMap.Map("container"); //可以修改容器ID
        var opts = {type: BMAP_NAVIGATION_CONTROL_ZOOM}  
        map.addControl(new BMap.NavigationControl());  
        // map.centerAndZoom(new BMap.Point(116.404, 39.915), 11);  
        // 创建地理编码服务实例  
        var point = new BMap.Point(xpoint,ypoint);
        
        // 将结果显示在地图上，并调整地图视野  
        map.centerAndZoom(point, 16);  
        map.addOverlay(new BMap.Marker(point));
</script>