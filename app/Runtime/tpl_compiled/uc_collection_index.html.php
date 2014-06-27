<div class="box">
	<div class="box">
		<div class="box-top"></div>
		<div class="box-content">
			<div class="head">
				<h2><?php echo $this->_var['page_title']; ?></h2>
				<ul class="filter">
					<li class="current"><a href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'uc_order#index',
);
echo $k['name']($k['value']);
?>"><?php echo $this->_var['LANG']['UC_ORDER']; ?></a></li>
					<li><a href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'uc_order#lottery',
);
echo $k['name']($k['value']);
?>"><?php echo $this->_var['LANG']['UC_LOTTERY']; ?></a></li>

				</ul>
			</div>
			<div class="sect">
				<table cellspacing="0" cellpadding="0" border="0" class="coupons-table" >
					<tr>
						<th>套餐项目</th>
						<th>金额</th>
						<th>状态</th>
						<th><?php echo $this->_var['LANG']['OPERATION']; ?></th>
					</tr>
					<?php $_from = $this->_var['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'order');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['order']):
?>
					<tr <?php if ($this->_var['key'] % 2 == 0): ?>class="alt"<?php endif; ?>>
					<td style="text-align:left;">
						<a href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'deal',
  'id' => $this->_var['order']['deal_id'],
);
echo $k['name']($k['value'],$k['id']);
?>">
						<?php echo $this->_var['order']['sub_name']; ?>
						</a>
					</td>

					<td><?php 
$k = array (
  'name' => 'format_price',
  'value' => $this->_var['order']['current_price'],
);
echo $k['name']($k['value']);
?></td>
					<td>
						<?PHP echo $this->_var['LANG']['TIME_STATUS_'.$this->_var['order']['time_status']];?>
					</td>
					<td>
						<a href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'deal',
  'id' => $this->_var['order']['deal_id'],
);
echo $k['name']($k['value'],$k['id']);
?>">购买</a>
						<a href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'uc_collection#del',
  'id' => $this->_var['order']['id'],
);
echo $k['name']($k['value'],$k['id']);
?>"><?php echo $this->_var['LANG']['CANCEL']; ?></a>
					</td>
					</tr>
					<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				</table>

				<div class="blank"></div>
				<div class="pages"><?php echo $this->_var['pages']; ?></div>
			</div><!--end sect-->
		</div>
		<div class="box-bottom"></div>
	</div>
</div>