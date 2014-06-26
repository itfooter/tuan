<?php
$payment_lang = array(
	'name'	=>	'通联预付费卡支付',
	'merchant_id'	=>	'商户ID',
	'merchant_key'	=>	'商户密钥',
	'tonglian_url'	=>	'通连网关地址',
	'VALID_ERROR'	=>	'支付验证失败',
	'PAY_FAILED'	=>	'支付失败'
);
$config = array(
	'merchant_id'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //商户ID
	'merchant_key'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //商户密钥
	'tonglian_url'	=>	array(
		'INPUT_TYPE'	=>	'0'
	)
);
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'TonglianCard';

    /* 名称 */
    $module['name']    = $payment_lang['name'];


    /* 支付方式：1：在线支付；0：线下支付 */
    $module['online_pay'] = '1';

    /* 配送 */
    $module['config'] = $config;
    
    $module['lang'] = $payment_lang;
    $module['reg_url'] = '';
    return $module;
}

// 通连预付费卡支付模型
require_once(APP_ROOT_PATH.'system/libs/payment.php');
class TonglianCard_payment implements payment {
	public function get_payment_code($payment_notice_id)
	{
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
		$order_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
		$money = round($payment_notice['money'],2);
		$payment_info = $GLOBALS['db']->getRow("select id,config,logo from ".DB_PREFIX."payment where id=".intval($payment_notice['payment_id']));
		$payment_info['config'] = unserialize($payment_info['config']);

		$order_sn = $order_info['order_sn'];
		
		
		$data_return_url = get_domain().APP_ROOT.'/payment.php?act=return&class_name=TonglianCard';
		$data_notify_url = get_domain().APP_ROOT.'/payment.php?act=notify&class_name=TonglianCard';

       
		//数据整理		
		
		$merCode = $payment_info['config']['merchant_id'];
		$billNo = $payment_notice['notice_sn'];
		$merDate = to_date($payment_notice['create_time'],"Ymd");
		$payType = "01";
		$signMd5 = md5($merCode.$billNo.$money.$merDate.$payType.$data_return_url.$payment_info['config']['merchant_key']);
        //MD5(MerCode+BillNo+Amount+MerDate +PayType+MerUrl+商户密码)
        
		 
		$payLinks = '<form name="pay_form" action="'.$payment_info['config']['tonglian_url'].'" method="post" target="_blank">
		<input type="hidden" name="MerCode" id="MerCode" value="'.$merCode.'" />		
		<input type="hidden" name="BillNo" id="BillNo" value="'.$billNo.'"/>		
		<input type="hidden" name="Amount" id="Amount" value="'.$money.'" />		
		<input type="hidden" name="MerDate" id="MerDate" value="'.$merDate.'"/>		
		<input type="hidden" name="PayType" id="PayType" value="'.$payType.'" />		
		<input type="hidden" name="MerUrl" id="MerUrl" value="'.$data_return_url.'"/>		
		<input type="hidden" name="backStageUrl" id="backStageUrl" value="'.$data_notify_url.'" />		
		<input type="hidden" name="SignMD5" id="SignMD5" value="'.$signMd5.'" />
	    <input type="submit" class="paybutton" value="立即使用通连预付费卡支付">	
		</form>';


		
		$payLinks.="<br /><div style='text-align:center' class='red'>".$GLOBALS['lang']['PAY_TOTAL_PRICE'].":".format_price($money)."</div>";
        return $payLinks;
	}
	
	public function response($request)
	{

		$payment = $GLOBALS['db']->getRow("select id,config from ".DB_PREFIX."payment where class_name='TonglianCard'");  
    	$payment['config'] = unserialize($payment['config']);   	
		
		
    	$BillNo = $request['BillNo'];
    	$BillAmount = $request['BillAmount'];
    	$SUCC = $request['SUCC'];
    	$SignMD5 = $request['SignMD5'];
    	
    	$verifySignMD5 = md5($BillNo.$BillAmount.$SUCC.$payment['config']['merchant_key']);
    	//MD5(BillNo+BillAmount+SUCC +商户密码)
    	
		$pay_result = false;
		if($SignMD5==$verifySignMD5){
			if($SUCC=='Y')
			{
				$pay_result=true;
			}

		}else{

		}
	
		if($pay_result){
			//TODO 商户订单处理，显示客户订单付款成功页面
			$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where notice_sn = '".$BillNo."'");
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
			require_once APP_ROOT_PATH."system/libs/cart.php";
			
			$rs = payment_paid($payment_notice['id']);						
			if($rs)
			{
				$rs = order_paid($payment_notice['order_id']);
				if($rs)
				{
					if($order_info['type']==0)
					app_redirect(url_pack("payment#done",$payment_notice['order_id'])); //支付成功
					else
					app_redirect(url_pack("payment#incharge_done",$payment_notice['order_id'])); //支付成功
				}
				else 
				{
					if($order_info['pay_status'] == 2)
					{
						if($order_info['type']==0)
						app_redirect(url_pack("payment#done",$payment_notice['order_id'])); //支付成功
						else
						app_redirect(url_pack("payment#incharge_done",$payment_notice['order_id'])); //支付成功
					}
					else
					app_redirect(url_pack("payment#pay",$payment_notice['id'])); 
				}
			}
			else
			{
				app_redirect(url_pack("payment#pay",$payment_notice['id'])); 
			}
		}
		else{
		    showErr($GLOBALS['payment_lang']["PAY_FAILED"]);
		}  
		
		//end	
	}
	
	public function notify($request)
	{
		$payment = $GLOBALS['db']->getRow("select id,config from ".DB_PREFIX."payment where class_name='TonglianCard'");  
    	$payment['config'] = unserialize($payment['config']);   	
		
		
    	$BillNo = $request['BillNo'];
    	$BillAmount = $request['BillAmount'];
    	$SUCC = $request['SUCC'];
    	$SignMD5 = $request['SignMD5'];
    	
    	$verifySignMD5 = md5($BillNo.$BillAmount.$SUCC.$payment['config']['merchant_key']);
    	//MD5(BillNo+BillAmount+SUCC +商户密码)
    	
		$pay_result = false;
		if($SignMD5==$verifySignMD5){
			if($SUCC=='Y')
			{
				$pay_result=true;
			}

		}else{

		}
	
		if($pay_result){
			//TODO 商户订单处理，显示客户订单付款成功页面
			$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where notice_sn = '".$BillNo."'");
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
			require_once APP_ROOT_PATH."system/libs/cart.php";
			
			$rs = payment_paid($payment_notice['id']);						
			if($rs)
			{
				$rs = order_paid($payment_notice['order_id']);				
			}
			else
			{
			 
			}
		}
		else{
		    
		}  
		
		//end	
	}
	
	public function get_display_code()
	{
		$payment_item = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."payment where class_name='TonglianCard'");
		if($payment_item)
		{
			$html = "<div style='float:left;'>".
					"<input type='radio' name='payment' value='".$payment_item['id']."' />&nbsp;".
					$payment_item['name'].
					"：</div>";
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