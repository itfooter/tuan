<div class="deal-consult sbox">
	<div class=sbox-bubble></div>
	<div class=sbox-top></div>
	<div class=sbox-content>		
		<div class=deal-consult-tip>
			<H2><?php echo $this->_var['LANG']['DEAL_MESSAGE']; ?></H2>
			<p class=nav>
				<a href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'message#faq',
);
echo $k['name']($k['value']);
?>" target=_blank><?php echo $this->_var['LANG']['ALL_MESSAGE']; ?>(<span id="new-message-count"><?php echo $this->_var['side_message']['count']; ?></span>)</a> 
				|
				<a href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'message#faq',
);
echo $k['name']($k['value']);
?>#consult-form-head" target=_blank><?php echo $this->_var['LANG']['ASK']; ?></a>
			</p>
			<ul class="list" id="new-message-ul">
				<?php $_from = $this->_var['side_message']['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'message_0_33591300_1403786365');if (count($_from)):
    foreach ($_from AS $this->_var['message_0_33591300_1403786365']):
?>
				<li><?php if ($this->_var['message_0_33591300_1403786365']['point'] > 0): ?><span class='comment_<?php echo $this->_var['message_0_33591300_1403786365']['point']; ?>'><?php echo $GLOBALS['lang']['COMMENT'.$this->_var['message']['point']];?></span><?php endif; ?><a href='<?php echo $this->_var['message_0_33591300_1403786365']['url']; ?>' title="<?php echo $this->_var['message_0_33591300_1403786365']['content']; ?>"><?php 
$k = array (
  'name' => 'msubstr',
  'value' => $this->_var['message_0_33591300_1403786365']['content'],
  'b' => '0',
  'e' => '30',
);
echo $k['name']($k['value'],$k['b'],$k['e']);
?></a></li>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			</ul>

			<div class=custom-service>
				<p class=im >
				<?php $_from = $this->_var['online_msn']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'msn');if (count($_from)):
    foreach ($_from AS $this->_var['msn']):
?>
				<?php if ($this->_var['msn'] != ''): ?>
				<a id=service-msn-help href="msnim:chat?contact=<?php echo $this->_var['msn']; ?>" target=_blank>
					<img src="<?php echo $this->_var['TMPL']; ?>/images/button-custom-msn.gif">
				</a> 
				<?php endif; ?>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				<?php $_from = $this->_var['online_qq']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'qq');if (count($_from)):
    foreach ($_from AS $this->_var['qq']):
?>
				<?php if ($this->_var['qq'] != ''): ?>
				<a href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $this->_var['qq']; ?>&site=qq&menu=yes" target=_blank>
					<img alt="" src="<?php echo $this->_var['TMPL']; ?>/images/button-custom-qq.gif">
				</a>
				<?php endif; ?>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				</p>
				<p class=time><?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'ONLINE_TIME',
);
echo $k['name']($k['value']);
?></p>
			</div>
		</div>
	</div>

	<div class=sbox-bottom></div>
</div>