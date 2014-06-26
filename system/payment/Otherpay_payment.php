<?php
$payment_lang = array(
	'name'	=>	'自定义收款方式',
	'pay_name'	=>	'收款方式名称',
	'pay_account'	=>	'收款帐号',
	'pay_account_name'	=>	'收款人',

);
$config = array(
	'pay_name'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //收款方式名称
	'pay_account'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //收款帐号
	'pay_account_name'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //收款人
);
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'Otherpay';

    /* 名称 */
    $module['name']    = $payment_lang['name'];


    /* 支付方式：1：在线支付；0：线下支付 */
    $module['online_pay'] = '0';

    /* 配送 */
    $module['config'] = $config;
    
    $module['lang'] = $payment_lang;
     $module['reg_url'] = '';
    return $module;
}

// 其他自定义支付模型
require_once(APP_ROOT_PATH.'system/libs/payment.php');
class Otherpay_payment implements payment {

	public function get_payment_code($payment_notice_id)
	{
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);		
		$money = round($payment_notice['money'],2);
		$payment_info = $GLOBALS['db']->getRow("select id,config,logo from ".DB_PREFIX."payment where id=".intval($payment_notice['payment_id']));
		$payment_info['config'] = unserialize($payment_info['config']);
		$code = $GLOBALS['payment_lang']['pay_account'].":".$payment_info['config']['pay_account']."<br /><br />".$GLOBALS['payment_lang']['pay_account_name'].":".$payment_info['config']['pay_account_name'];
		$code.="<br /><br /><div style='text-align:center' class='red'>".$GLOBALS['lang']['PAY_TOTAL_PRICE'].":".format_price($money)."</div>";
        return $code;
	}
	
	public function response($request)
	{
        
		return false;
	}
	
	public function notify($request)
	{
		return false;
	}
	
	public function get_display_code()
	{
		$payment_item = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."payment where class_name='Otherpay'");
		if($payment_item)
		{
			$payment_item['config'] = unserialize($payment_item['config']);
			$html = "<div style='float:left;'>".
					"<input type='radio' name='payment' value='".$payment_item['id']."' />&nbsp;".
					$payment_item['config']['pay_name'].
					"：</div>"."收款人：".$payment_item['config']['pay_account_name'].",收款帐号：".$payment_item['config']['pay_account'];
			if($payment_item['logo']!='')
			{
				$html .= "<div style='float:left; padding-left:10px;'><img src='".APP_ROOT.$payment_item['logo']."' /></div>";
			}
			$html .= "<div style='float:left; padding-left:10px;'>".nl2br($payment_item['description'])."</div>";
			return $html;
		}
		else
		{
			return '';
		}
	}
}
?>