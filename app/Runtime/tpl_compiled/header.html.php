<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="Generator" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->_var['page_title']; ?><?php if (! $this->_var['hide_end_title']): ?> - <?php echo $this->_var['shop_info']['SHOP_TITLE']; ?><?php if ($this->_var['city_title']): ?> - <?php echo $this->_var['city_title']; ?><?php echo $this->_var['LANG']['SITE']; ?><?php endif; ?><?php endif; ?> - <?php if ($this->_var['deal_city']['seo_title'] == ''): ?><?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'SHOP_SEO_TITLE',
);
echo $k['name']($k['value']);
?><?php else: ?><?php echo $this->_var['deal_city']['seo_title']; ?><?php endif; ?></title>
<meta name="keywords" content="<?php echo $this->_var['page_keyword']; ?><?php echo $this->_var['city_title']; ?><?php echo $this->_var['shop_info']['SHOP_KEYWORD']; ?>" />
<meta name="description" content="<?php echo $this->_var['page_description']; ?><?php echo $this->_var['city_title']; ?><?php echo $this->_var['shop_info']['SHOP_DESCRIPTION']; ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->_var['TMPL']; ?>/css/style.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->_var['TMPL']; ?>/css/weebox.css" />
<script type="text/javascript">
var APP_ROOT = '<?php echo $this->_var['APP_ROOT']; ?>';
var CART_URL = '<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'cart',
);
echo $k['name']($k['value']);
?>';
var CART_CHECK_URL = '<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'cart#check',
);
echo $k['name']($k['value']);
?>';
var LOADER_IMG = '<?php echo $this->_var['TMPL']; ?>/images/loading.gif';
</script>
<script type="text/javascript" src="<?php echo $this->_var['TMPL']; ?>/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $this->_var['TMPL']; ?>/js/script.js"></script>
<script type="text/javascript" src="<?php echo $this->_var['TMPL']; ?>/js/jquery.bgiframe.js"></script>
<script type="text/javascript" src="<?php echo $this->_var['TMPL']; ?>/js/jquery.weebox.js"></script>
<script type="text/javascript" src="<?php echo $this->_var['TMPL']; ?>/js/jquery.pngfix.js"></script>
<script type="text/javascript" src="<?php echo $this->_var['APP_ROOT']; ?>/app/Runtime/lang.js"></script>
<script type="text/javascript" src="<?php echo $this->_var['TMPL']; ?>/js/lazyload.js"></script>
<?php if (app_conf ( "APP_MSG_SENDER_OPEN" ) == 1): ?>
<script type="text/javascript" src="<?php echo $this->_var['TMPL']; ?>/js/msg_sender.js"></script>
<?php endif; ?>
</head>

<body class="bg-alt">
<div id="doc">
	<div id="hdw">
		<div class="head">
        <div id="hd">
            <div id="logo">
				<a class="link" href="<?php echo $this->_var['APP_ROOT']; ?>/"><img src="<?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'SHOP_LOGO',
);
echo $k['name']($k['value']);
?>" /></a>
			</div>
			<?php if (count ( $this->_var['deal_city_list'] ) > 1): ?>
            <div class="guides">
                <div class="city">
                    <h2><?php echo $this->_var['deal_city']['name']; ?></h2>

                </div>
                <div id="guides-city-change" class="change"><?php echo $this->_var['LANG']['SWITCH_CITY']; ?></div>
                <div id="guides-city-list" class="city-list">
                     <?php $_from = $this->_var['deal_city_list_zm']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'deal_city_group');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['deal_city_group']):
?>
						<div class="city_group">
						<table>
						<tr>
						<td class="zm">
						<?php echo $this->_var['key']; ?>
						</td>
						<td>
						<?php $_from = $this->_var['deal_city_group']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'deal_city_item');if (count($_from)):
    foreach ($_from AS $this->_var['deal_city_item']):
?>
						<span <?php if ($this->_var['deal_city_item']['id'] == $this->_var['deal_city']['id']): ?>class="current"<?php endif; ?>><a href="<?php echo $this->_var['deal_city_item']['url']; ?>" <?php if ($this->_var['deal_city_item']['is_open'] == 1): ?>class="opencity"<?php endif; ?>><?php echo $this->_var['deal_city_item']['name']; ?></a></span>
						<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
						</td>
						</tr>
						</table>
						</div>
					<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

                </div>
            </div>
			<?php endif; ?>
            <ul class="nav cf">
				<?php $_from = $this->_var['nav_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'nav_item');if (count($_from)):
    foreach ($_from AS $this->_var['nav_item']):
?>
				<li><a href="<?php echo $this->_var['nav_item']['url']; ?>"  target="<?php if ($this->_var['nav_item']['blank'] == 1): ?>_blank<?php endif; ?>" <?php if ($this->_var['nav_item']['current'] == 1): ?>class="current"<?php endif; ?>><?php echo $this->_var['nav_item']['name']; ?></a></li>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>      
			</ul>

            <div class="refer">
		    <?php if (app_conf ( "SMS_ON" ) == 1): ?>
			» <a href="javascript:void(0)" onclick="submit_sms();"><?php echo $this->_var['LANG']['SMS_SUBSCRIBE']; ?></a>&nbsp;&nbsp;
			» <a href="javascript:void(0)" onclick="unsubmit_sms();"><?php echo $this->_var['LANG']['SMS_UNSUBSCRIBE']; ?></a>&nbsp;&nbsp;
			<?php endif; ?>
			» <a href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'subscribe#mail',
);
echo $k['name']($k['value']);
?>"><?php echo $this->_var['LANG']['MAIL_SUBSCRIBE']; ?></a>&nbsp;&nbsp;
			» <a href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'coupon#verify',
);
echo $k['name']($k['value']);
?>"><?php echo $this->_var['LANG']['VERIFY_COUPON']; ?></a>&nbsp;&nbsp;
			<?php if (app_conf ( "CN_LANG" ) == 1): ?>
			<script type="text/javascript" src="<?php echo $this->_var['TMPL']; ?>/js/cn-tw.js"></script>
			» <a href="javascript:lang_load();"><?php echo $this->_var['LANG']['CN_LANG']; ?></a>&nbsp;&nbsp;
			<?php endif; ?>
			</div>
            <div class="logins">
            <?php if ($this->_var['user_info']): ?>
			<ul rel="" id="account">
			<li title="<?php echo $this->_var['user_info']['user_name']; ?>" class="username"><?php echo $this->_var['LANG']['WELCOME']; ?>，<?php 
$k = array (
  'name' => 'msubstr',
  'value' => $this->_var['user_info']['user_name'],
  'start' => '0',
  'length' => '4',
);
echo $k['name']($k['value'],$k['start'],$k['length']);
?>！</li>
			<li class="account"><a class="account" id="myaccount" href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'uc_account#index',
);
echo $k['name']($k['value']);
?>"><?php echo $this->_var['LANG']['MY_ACCOUNT']; ?></a></li>
			<li class="logout"><a href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'user#loginout',
);
echo $k['name']($k['value']);
?>"><?php echo $this->_var['LANG']['LOGINOUT']; ?></a></li>
			</ul>
			<?php else: ?>
			<ul id="account" rel="">
			<li class="login"><a href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'user#login',
);
echo $k['name']($k['value']);
?>"><?php echo $this->_var['LANG']['LOGIN']; ?></a></li>
            <li class="signup"><a href="<?php 
$k = array (
  'name' => 'url_pack',
  'value' => 'user#register',
);
echo $k['name']($k['value']);
?>"><?php echo $this->_var['LANG']['REGISTER']; ?></a></li>
			</ul>
			<?php endif; ?>
			
			
			
			
            <div class="line"></div>
            </div>
			<ul id="myaccount-menu">
			<?php $_from = $this->_var['user_menu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'menu_item');if (count($_from)):
    foreach ($_from AS $this->_var['menu_item']):
?>
			<li><a href="<?php echo $this->_var['menu_item']['url']; ?>"><?php echo $this->_var['menu_item']['name']; ?></a></li>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			 </ul>
			<div id="head-tel"><?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'SHOP_TEL',
);
echo $k['name']($k['value']);
?></div>        
			</div>
		</div>
    </div>
	
    
    <?php if ($this->_var['is_index'] == 1): ?>
	<script type="text/javascript">
		function set_sort(type)
		{
			var ajaxurl = APP_ROOT+"/ajax.php?act=set_sort_idx&type="+type;
			$.ajax({ 
				url: ajaxurl,
				success: function(text){
					location.reload();
				},
				error:function(ajaxobj)
				{
					if(ajaxobj.responseText!='')
					alert(ajaxobj.responseText);
				}
			});
		}
	</script>
	<div class="filter_contain cf">
		<div class="cate_row">
			<b><?php echo $this->_var['LANG']['CATE_DEAL']; ?>：</b>
			<?php $_from = $this->_var['bcate_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'bcate');if (count($_from)):
    foreach ($_from AS $this->_var['bcate']):
?>
			<a href="<?php echo $this->_var['bcate']['url']; ?>" title="<?php echo $this->_var['bcate']['name']; ?>" <?php if ($this->_var['bcate']['current'] == 1): ?>class="current"<?php endif; ?>><?php echo $this->_var['bcate']['name']; ?>[<?php echo $this->_var['bcate']['count']; ?>]</a>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		</div>
		
		<div class="sort_row">
			<a href="javascript:void(0);" onclick="set_sort('begin_time');" class="<?php if ($this->_var['sort_field'] == 'begin_time'): ?>current idx_<?php echo $this->_var['sort_type']; ?><?php endif; ?>"><?php echo $this->_var['LANG']['SORT_BEGIN_TIME']; ?></a>
			<a href="javascript:void(0);" onclick="set_sort('current_price');" class="<?php if ($this->_var['sort_field'] == 'current_price'): ?>current idx_<?php echo $this->_var['sort_type']; ?><?php endif; ?>"><?php echo $this->_var['LANG']['SORT_CURRENT_PRICE']; ?></a>
			<a href="javascript:void(0);" onclick="set_sort('buy_count');" class="<?php if ($this->_var['sort_field'] == 'buy_count'): ?>current idx_<?php echo $this->_var['sort_type']; ?><?php endif; ?>"><?php echo $this->_var['LANG']['SORT_BUY_COUNT']; ?></a>
			<a href="javascript:void(0);" onclick="set_sort('sort');" class="<?php if ($this->_var['sort_field'] == 'sort'): ?>current idx_<?php echo $this->_var['sort_type']; ?><?php endif; ?>"><?php echo $this->_var['LANG']['SORT_SORT']; ?></a>
		</div>
		
		<?php if ($this->_var['scate_list']): ?>
		<div class="blank1"></div>
		
		<div class="sub_cate_row">	
			<?php $_from = $this->_var['scate_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'scate');if (count($_from)):
    foreach ($_from AS $this->_var['scate']):
?>		
			<a href="<?php echo $this->_var['scate']['url']; ?>" title="<?php echo $this->_var['scate']['name']; ?>" <?php if ($this->_var['scate']['current'] == 1): ?>class="current"<?php endif; ?>><?php echo $this->_var['scate']['name']; ?>[<?php echo $this->_var['scate']['count']; ?>]</a>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>
	<div class="blank"></div>
	
	<!--adBox-->
	<adv adv_id="头部广告位" />
	<!--end advBox-->
	
	<div id="sysmsg-error-box">
		<div class="sysmsgw hidd" id="sysmsg-error">
			<div class="sysmsg"><span></span><span class="close"><?php echo $this->_var['LANG']['CLOSE']; ?></span></div>
		</div>		

		<div class="sysmsgw hidd" id="sysmsg-success">
				<div class="sysmsg"><span></span><span class="close"><?php echo $this->_var['LANG']['CLOSE']; ?></span></div>
		</div>
	</div>
	<script type="text/javascript">
		<?php if ($this->_var['success']): ?>
		$.showSuccess("<?php echo $this->_var['success']; ?>");
		<?php endif; ?>
		<?php if ($this->_var['error']): ?>
		$.showErr("<?php echo $this->_var['error']; ?>");
		<?php endif; ?>
	</script>
