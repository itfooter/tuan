<div id="sidebar">
				<div class="sbox">
					<div class="sbox-top"></div>
					<div class="sbox-content">
						<div class="side-tip">
							<h3 class="first"><?php echo $this->_var['LANG']['USER_INFO']; ?></h3>
							<p><?php echo $this->_var['LANG']['USER_GROUP']; ?>：<?php echo $this->_var['user_info']['user_group']; ?></p>
							<p><?php echo $this->_var['LANG']['USER_SCORE']; ?>：<?php 
$k = array (
  'name' => 'format_score',
  'value' => $this->_var['user_info']['score'],
);
echo $k['name']($k['value']);
?></p>
							<p><?php echo $this->_var['LANG']['USER_MONEY']; ?>：<?php 
$k = array (
  'name' => 'format_price',
  'value' => $this->_var['user_info']['money'],
);
echo $k['name']($k['value']);
?></p>
							<p><?php echo $this->_var['LANG']['USER_REFERRAL_TOTAL']; ?>：<?php echo $this->_var['user_info']['referral_total']; ?></p>
							<p><?php echo $this->_var['LANG']['LAST_LOGIN_IP']; ?>：<?php echo $this->_var['user_info']['login_ip']; ?></p>
							
						</div>
					</div>
					<div class="sbox-bottom"></div>
				</div>
</div>