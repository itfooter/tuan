<div class="deal-consult sbox">
	<div class=sbox-bubble></div>
	<div class=sbox-top></div>
	<div class=sbox-content>		
		<div class=deal-consult-tip>
			<H2><?php echo $this->_var['LANG']['SIDE_DEAL_MESSAGE']; ?></H2>
			<p class=nav>
				<a href="<?php if ($this->_var['deal']): ?><?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'message#deal',
  'id' => $this->_var['deal']['id'],
);
echo $k['name']($k['value'],$k['id']);
?><?php else: ?><?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'deals#comment',
);
echo $k['name']($k['value']);
?><?php endif; ?>" target=_blank><?php echo $this->_var['LANG']['ALL_MESSAGE']; ?>(<span id="new-message-count"><?php echo $this->_var['side_deal_message']['count']; ?></span>)</a> 
				|
				<a href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'message#deal',
  'id' => $this->_var['deal']['id'],
);
echo $k['name']($k['value'],$k['id']);
?>#consult-form-head" target=_blank><?php echo $this->_var['LANG']['DEAL_ASK']; ?></a>
			</p>
			<ul class="list" id="new-message-ul">
				<?php $_from = $this->_var['side_deal_message']['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'message');if (count($_from)):
    foreach ($_from AS $this->_var['message']):
?>
				<li><?php if ($this->_var['message']['point'] > 0): ?><span class='comment_<?php echo $this->_var['message']['point']; ?>'><?php echo $GLOBALS['lang']['COMMENT'.$this->_var['message']['point']];?></span><?php endif; ?><a href='<?php echo $this->_var['message']['url']; ?>' title="<?php echo $this->_var['message']['content']; ?>"><?php 
$k = array (
  'name' => 'msubstr',
  'value' => $this->_var['message']['content'],
  'b' => '0',
  'e' => '30',
);
echo $k['name']($k['value'],$k['b'],$k['e']);
?></a></li>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			</ul>

			
		</div>
	</div>

	<div class=sbox-bottom></div>
</div>
