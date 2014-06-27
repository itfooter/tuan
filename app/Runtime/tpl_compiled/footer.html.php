<div id="bd"><adv adv_id="foot_adv" /></div>
<div class=go-top><a href="#doc"><span class=top-arrow>↑</span><?php echo $this->_var['LANG']['GO_TOP']; ?></a></div>
<div id="ftw">
        <div id="ft">
			<p class="contact"><a href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'message#feedback',
);
echo $k['name']($k['value']);
?>"><?php echo $this->_var['LANG']['FEEDBACK']; ?></a></p>
            <ul class="cf">
            	<?php $_from = $this->_var['deal_help']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'help_cate');if (count($_from)):
    foreach ($_from AS $this->_var['help_cate']):
?>
				<li class="col">
                    <h3><?php echo $this->_var['help_cate']['title']; ?></h3>
                    <ul class="sub-list">
						<?php $_from = $this->_var['help_cate']['help_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'help_item');if (count($_from)):
    foreach ($_from AS $this->_var['help_item']):
?>
						<li><a href="<?php echo $this->_var['help_item']['url']; ?>" <?php if ($this->_var['help_item']['new'] == 1): ?>target="_blank"<?php endif; ?>><?php echo $this->_var['help_item']['title']; ?></a></li>
						<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>             
					</ul>
                </li>  
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>            
				<li class="col end">
                    <div class=logo-footer>
                    	<a href="<?php echo $this->_var['APP_ROOT']; ?>/" title="<?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'SHOP_TITLE',
);
echo $k['name']($k['value']);
?>">
                    		<img alt="<?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'SHOP_TITLE',
);
echo $k['name']($k['value']);
?>" src="<?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'FOOTER_LOGO',
);
echo $k['name']($k['value']);
?>" />
						</a>
					</div>
                </li>
            </ul>
			<div class="blank"></div>
			<div class="flink">
			<?php $_from = $this->_var['f_link_data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'link_group');if (count($_from)):
    foreach ($_from AS $this->_var['link_group']):
?>			
				<h2 style="color:#ccc;"><?php echo $this->_var['link_group']['name']; ?></h2>
				<?php $_from = $this->_var['link_group']['links']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'link');if (count($_from)):
    foreach ($_from AS $this->_var['link']):
?>
					<a href="http://<?php echo $this->_var['link']['url']; ?>" target="_blank" title="<?php if ($this->_var['link']['description']): ?><?php echo $this->_var['link']['description']; ?><?php else: ?><?php echo $this->_var['link']['name']; ?><?php endif; ?>"><?php if ($this->_var['link']['img'] != ''): ?><img src='<?php echo $this->_var['link']['img']; ?>' alt="<?php if ($this->_var['link']['description']): ?><?php echo $this->_var['link']['description']; ?><?php else: ?><?php echo $this->_var['link']['name']; ?><?php endif; ?>" /><?php else: ?><?php echo $this->_var['link']['name']; ?><?php endif; ?></a>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				<div class="clear"></div>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			</div>
            <div class=copyright>
				<?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'SHOP_FOOTER',
);
echo $k['name']($k['value']);
?> 
				<div class="blank1"></div>
				<?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'ICP_LICENSE',
);
echo $k['name']($k['value']);
?>&nbsp;&nbsp;
				<?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'COUNT_CODE',
);
echo $k['name']($k['value']);
?>
            </div>
        </div>
    </div>
</div>

</div>
</body>
</html>