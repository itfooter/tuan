<?php echo $this->fetch('inc/header.html'); ?> 
<div id="bdw" class="bdw">
	<div id="bd" class="cf">
		<div id="deal-default">
			<div id="dashboard" class="dashboard">
					<ul>
						<?php $_from = $this->_var['user_menu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'menu_item');if (count($_from)):
    foreach ($_from AS $this->_var['menu_item']):
?>
						<li <?php if ($this->_var['menu_item']['act'] == 1): ?>class="current"<?php endif; ?>><a href="<?php echo $this->_var['menu_item']['url']; ?>"><?php echo $this->_var['menu_item']['name']; ?></a>
						<span></span>
						</li>
						<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>			
					</ul>
			</div>
			<div id="content" class="cf" style="overflow:hidden;">	
				<div id="uc" class="cf">	
				<?php echo $this->fetch($this->_var['inc_file']); ?>
				</div>			
			</div>
			<!--side-->
			<?php echo $this->fetch('inc/uc/user_info.html'); ?>
	</div>
	<!-- bd end -->
</div>
<?php echo $this->fetch('inc/footer.html'); ?>