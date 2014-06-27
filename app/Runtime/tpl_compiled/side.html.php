<div id="sidebar">
<?php if ($this->_var['module'] == 'index' && app_conf ( 'STYLE_OPEN' ) == 1): ?>
<div style="text-align:center;"><a href="<?php echo $this->_var['style_url']; ?>" title="<?php echo $this->_var['switch_style_tip']; ?>" style="display:block; background:#fff; border:#f60 1px dotted; font-size:12px; padding:3px; "><?php echo $this->_var['switch_style_tip']; ?></a></div>
<div class="blank"></div>
<?php endif; ?>
<?php if ($this->_var['deal_city']['notice']): ?>

<?php echo $this->fetch('inc/side_notice.html'); ?>
<div class="blank"></div>
<?php endif; ?>

<?php if ($this->_var['site_notice_list']): ?>
<?php echo $this->fetch('inc/side_site_notice.html'); ?>
<div class="blank"></div>
<?php endif; ?>

<?php if ($this->_var['side_deal_list']): ?>

<?php echo $this->fetch('inc/side_deal.html'); ?>
<div class="blank"></div>
<?php endif; ?>
<adv adv_id="right_adv" />
<?php if (! $this->_var['deal'] || $this->_var['deal']['is_referral'] == 1): ?>

<?php echo $this->fetch('inc/side_referrals.html'); ?>
<div class="blank"></div>
<?php endif; ?>
<?php if ($this->_var['side_deal_message']['list'] || $this->_var['deal']): ?>

<?php echo $this->fetch('inc/side_deal_message.html'); ?>
<div class="blank"></div>
<?php endif; ?>

<?php echo $this->fetch('inc/side_message.html'); ?>
<div class="blank"></div>

<?php echo $this->fetch('inc/side_submit.html'); ?>
<?php if ($this->_var['vote']): ?>
<div class=blank></div>

<?php echo $this->fetch('inc/side_vote.html'); ?>
<?php endif; ?>
<div class=blank></div>

<?php echo $this->fetch('inc/side_supplier.html'); ?>
</div>