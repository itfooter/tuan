<?php
$payment_lang = array(
	'name'	=>	'通联支付',
	'merchant_id'	=>	'商户ID',
	'merchant_key'	=>	'商户密钥',
	'tonglian_url'	=>	'通连网关地址',
	'VALID_ERROR'	=>	'支付验证失败',
	'PAY_FAILED'	=>	'支付失败',
	'issuer_id'	=>	'支持的银行',
	'issuer_id_cmb'	=>	'招商银行',
	'issuer_id_icbc'	=>	'工商银行',
	'issuer_id_ccb'	=>	'建设银行',
	'issuer_id_abc'	=>	'农业银行',
	'issuer_id_spdb'	=>	'浦东发展银行',
	'issuer_id_ceb'	=>	'光大银行',	
	'issuer_id_icbc_4'	=>	'工商银行(企业)',
	'issuer_id_ccb_4'	=>	'建设银行(企业)',
	'issuer_id_abc_4'	=>	'农业银行(企业)',
	'issuer_id_spdb_4'	=>	'浦东发展银行(企业)',
	'issuer_id_ceb_4'	=>	'光大银行(企业)',
	'issuer_id_comm'	=>	'交通银行',
	'issuer_id_boc'	=>	'中国银行',
	'issuer_id_bos'	=>	'上海银行',
	'issuer_id_pingan'	=>	'平安银行',
	'issuer_id_citic'	=>	'中信银行',
	'issuer_id_hxb'	=>	'华夏银行',
	'issuer_id_cib'	=>	'兴业银行',
	'issuer_id_cmbc'	=>	'民生银行',
	'issuer_id_psbc'	=>	'邮政储蓄',
	'publickey_exponent'	=>	'公钥exponent',
	'publickey_modulus'	=>	'公钥modulus',
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
	),
	'publickey_exponent'	=>	array(
		'INPUT_TYPE'	=>	'0'
	),
	'publickey_modulus'	=>	array(
		'INPUT_TYPE'	=>	'0'
	),
	'issuer_id'	=>	array(
		'INPUT_TYPE'	=>	'3',
		'VALUES'	=>	array(
			'cmb',	//'招商银行',
			'icbc',	//'工商银行',
			'ccb',		//'建设银行',
			'abc',		//'农业银行',
			'spdb', 	//'浦东发展银行',
			'ceb',		//'光大银行',	
			'icbc_4',		//'工商银行(企业)',
			'ccb_4',		//'建设银行(企业)',
			'abc_4',		//'农业银行(企业)',
			'spdb_4',		//'浦东发展银行(企业)',
			'ceb_4',		//'光大银行(企业)',
			'comm',			//'交通银行',
			'boc',		//'中国银行',
			'bos',		//'上海银行',
			'pingan',	//'平安银行',
			'citic',	//'中信银行',
			'hxb',		//'华夏银行',
			'cib',		//'兴业银行',
			'cmbc',		//'民生银行',
			'psbc',		//'邮政储蓄',
			)
	), //可选的银行网关
);
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'Tonglian';

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

// 通连支付模型
require_once(APP_ROOT_PATH.'system/libs/payment.php');
class Tonglian_payment implements payment {
	private $payment_lang = array(
		'GO_TO_PAY'	=>	'前往%s支付',
		'issuer_id_cmb'	=>	'招商银行',
		'issuer_id_icbc'	=>	'工商银行',
		'issuer_id_ccb'	=>	'建设银行',
		'issuer_id_abc'	=>	'农业银行',
		'issuer_id_spdb'	=>	'浦东发展银行',
		'issuer_id_ceb'	=>	'光大银行',	
		'issuer_id_icbc_4'	=>	'工商银行(企业)',
		'issuer_id_ccb_4'	=>	'建设银行(企业)',
		'issuer_id_abc_4'	=>	'农业银行(企业)',
		'issuer_id_spdb_4'	=>	'浦东发展银行(企业)',
		'issuer_id_ceb_4'	=>	'光大银行(企业)',
		'issuer_id_comm'	=>	'交通银行',
		'issuer_id_boc'	=>	'中国银行',
		'issuer_id_bos'	=>	'上海银行',
		'issuer_id_pingan'	=>	'平安银行',
		'issuer_id_citic'	=>	'中信银行',
		'issuer_id_hxb'	=>	'华夏银行',
		'issuer_id_cib'	=>	'兴业银行',
		'issuer_id_cmbc'	=>	'民生银行',
		'issuer_id_psbc'	=>	'邮政储蓄',
	);

	public function get_name($bank_id='')
	{
		$bank_id = trim(addslashes($bank_id));
		return $this->payment_lang['issuer_id_'.$bank_id];
	}
	public function get_payment_code($payment_notice_id)
	{
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
		$money = round($payment_notice['money'],2);
		$payment_info = $GLOBALS['db']->getRow("select id,config,logo from ".DB_PREFIX."payment where id=".intval($payment_notice['payment_id']));
		$payment_info['config'] = unserialize($payment_info['config']);

		$order_sn = $order_info['order_sn'];
		
		$arr = explode("_",$order_info['bank_id']);
		
		
		$data_return_url = get_domain().APP_ROOT.'/payment.php?act=return&class_name=Tonglian';
		$data_notify_url = get_domain().APP_ROOT.'/payment.php?act=return&class_name=Tonglian';

        
		//数据整理
		//页面编码要与参数inputCharset一致，否则服务器收到参数值中的汉字为乱码而导致验证签名失败。	
		$inputCharset=1;  //默认1为 utf-8
		$pickupUrl=$data_return_url;
		$receiveUrl=$data_notify_url;
		$version="v1.0";
		$language="1";
		$signType="1";
		$merchantId= $payment_info['config']['merchant_id'] ;
		$payerName= ""; //付款人姓名
		$payerEmail=""; //付款人Email	
		$payerTelephone=""; //付款人电话
		$payerIDCard="";	//付款人身份证
		$pid="";		//合作伙伴ID
		$orderNo= $payment_notice['notice_sn'];	//唯 一标识的订单号(支付单号)
		$orderAmount=round($payment_notice['money'],2)*100;  //以分为单位的支付总额
		$orderDatetime= to_date($payment_notice['create_time'],"YmdHis");   //数字串，一共14 位   格式为：年[4 位]月[2 位]日[2 位]时[2位]分[2 位]秒[2 位]例如：20071117020101
		$orderCurrency="0";	//0为人民币
		$orderExpireDatetime="";
		$productName="";
		$productId="";
		$productPrice="";
		$productNum="";
		$productDescription="";
		$ext1="";
		$ext2="";
		$issuerId = $arr[0];
		$payType = isset($arr[1])?$arr[1]:"1";
		//测试时用
//		$issuerId = "";
//		$payType = "0";

		$pan="";
		
		$key=$payment_info['config']['merchant_key']; 
		
	
		// 生成签名字符串。
		$bufSignSrc="";
		if($inputCharset != "")
		$bufSignSrc=$bufSignSrc."inputCharset=".$inputCharset."&";		
		if($pickupUrl != "")
		$bufSignSrc=$bufSignSrc."pickupUrl=".$pickupUrl."&";		
		if($receiveUrl != "")
		$bufSignSrc=$bufSignSrc."receiveUrl=".$receiveUrl."&";		
		if($version != "")
		$bufSignSrc=$bufSignSrc."version=".$version."&";		
		if($language != "")
		$bufSignSrc=$bufSignSrc."language=".$language."&";		
		if($signType != "")
		$bufSignSrc=$bufSignSrc."signType=".$signType."&";		
		if($merchantId != "")
		$bufSignSrc=$bufSignSrc."merchantId=".$merchantId."&";		
		if($payerName != "")
		$bufSignSrc=$bufSignSrc."payerName=".$payerName."&";		
		if($payerEmail != "")
		$bufSignSrc=$bufSignSrc."payerEmail=".$payerEmail."&";		
		if($payerTelephone != "")
		$bufSignSrc=$bufSignSrc."payerTelephone=".$payerTelephone."&";			
		if($payerIDCard != "")
		$bufSignSrc=$bufSignSrc."payerIDCard=".$payerIDCard."&";			
		if($pid != "")
		$bufSignSrc=$bufSignSrc."pid=".$pid."&";		
		if($orderNo != "")
		$bufSignSrc=$bufSignSrc."orderNo=".$orderNo."&";
		if($orderAmount != "")
		$bufSignSrc=$bufSignSrc."orderAmount=".$orderAmount."&";
		if($orderCurrency != "")
		$bufSignSrc=$bufSignSrc."orderCurrency=".$orderCurrency."&";
		if($orderDatetime != "")
		$bufSignSrc=$bufSignSrc."orderDatetime=".$orderDatetime."&";
		if($orderExpireDatetime != "")
		$bufSignSrc=$bufSignSrc."orderExpireDatetime=".$orderExpireDatetime."&";
		if($productName != "")
		$bufSignSrc=$bufSignSrc."productName=".$productName."&";
		if($productPrice != "")
		$bufSignSrc=$bufSignSrc."productPrice=".$productPrice."&";
		if($productNum != "")
		$bufSignSrc=$bufSignSrc."productNum=".$productNum."&";
		if($productId != "")
		$bufSignSrc=$bufSignSrc."productId=".$productId."&";
		if($productDescription != "")
		$bufSignSrc=$bufSignSrc."productDescription=".$productDescription."&";
		if($ext1 != "")
		$bufSignSrc=$bufSignSrc."ext1=".$ext1."&";
		if($ext2 != "")
		$bufSignSrc=$bufSignSrc."ext2=".$ext2."&";	
		if($payType != "")
		$bufSignSrc=$bufSignSrc."payType=".$payType."&";		
		if($issuerId != "")
		$bufSignSrc=$bufSignSrc."issuerId=".$issuerId."&";
		if($pan != "")
		$bufSignSrc=$bufSignSrc."pan=".$pan."&";	
		$bufSignSrc=$bufSignSrc."key=".$key; //key为MD5密钥，密钥是在通联支付网关会员服务网站上设置。
		
		//签名，设为signMsg字段值。
		$signMsg = strtoupper(md5($bufSignSrc));	
		//end数据整理
		if(!empty($payment_info['logo']))
		{
			$payLinks = "<input type='image' src='".APP_ROOT.$payment_info['logo']."' style='border:solid 1px #ccc;'><div class='blank'></div>";
		}
		
		$payLinks .= '<form name="pay_form" action="'.$payment_info['config']['tonglian_url'].'" method="post" target="_blank">
		<input type="hidden" name="inputCharset" id="inputCharset" value="'.$inputCharset.'" />
		<input type="hidden" name="pickupUrl" id="pickupUrl" value="'.$pickupUrl.'"/>
		<input type="hidden" name="receiveUrl" id="receiveUrl" value="'.$receiveUrl.'" />
		<input type="hidden" name="version" id="version" value="'.$version.'"/>
		<input type="hidden" name="language" id="language" value="'.$language.'" />
		<input type="hidden" name="signType" id="signType" value="'.$signType.'"/>
		<input type="hidden" name="merchantId" id="merchantId" value="'.$merchantId.'" />
		<input type="hidden" name="payerName" id="payerName" value="'.$payerName.'"/>
		<input type="hidden" name="payerEmail" id="payerEmail" value="'.$payerEmail.'" />
		<input type="hidden" name="payerTelephone" id="payerTelephone" value="'.$payerTelephone.'" />
		<input type="hidden" name="payerIDCard" id="payerIDCard" value="'.$payerIDCard.'" />
		<input type="hidden" name="pid" id="pid" value="'.$pid.'"/>
		<input type="hidden" name="orderNo" id="orderNo" value="'.$orderNo.'" />
		<input type="hidden" name="orderAmount" id="orderAmount" value="'.$orderAmount.'"/>
		<input type="hidden" name="orderCurrency" id="orderCurrency" value="'.$orderCurrency.'" />
		<input type="hidden" name="orderDatetime" id="orderDatetime" value="'.$orderDatetime.'" />
		<input type="hidden" name="orderExpireDatetime" id="orderExpireDatetime" value="'.$orderExpireDatetime.'"/>
		<input type="hidden" name="productName" id="productName" value="'.$productName.'" />
		<input type="hidden" name="productPrice" id="productPrice" value="'.$productPrice.'" />
		<input type="hidden" name="productNum" id="productNum" value="'.$productNum.'"/>
		<input type="hidden" name="productId" id="productId" value="'.$productId.'" />
		<input type="hidden" name="productDescription" id="productDescription" value="'.$productDescription.'" />
		<input type="hidden" name="ext1" id="ext1" value="'.$ext1.'" />
		<input type="hidden" name="ext2" id="ext2" value="'.$ext2.'" />
		<input type="hidden" name="payType" value="'.$payType.'" />
		<input type="hidden" name="issuerId" value="'.$issuerId.'" />
		<input type="hidden" name="pan" value="'.$pan.'" />
		<input type="hidden" name="signMsg" id="signMsg" value="'.$signMsg.'" />
	    <input type="submit" class="paybutton" value="'.sprintf($this->payment_lang['GO_TO_PAY'],$this->get_name($order_info['bank_id'])).'">
	
		</form>';


		
		$payLinks.="<br /><div style='text-align:center' class='red'>".$GLOBALS['lang']['PAY_TOTAL_PRICE'].":".format_price($money)."</div>";
        return $payLinks;
	}
	
	public function response($request)
	{
		require_once APP_ROOT_PATH."system/payment/Tonglian/php_rsa.php"; //请修改参数为php_rsa.php文件的实际位置
		require_once APP_ROOT_PATH."system/payment/Tonglian/php_sax.php";  //请修改参数为php_sax.php文件的实际位置

		$payment = $GLOBALS['db']->getRow("select id,config from ".DB_PREFIX."payment where class_name='Tonglian'");  
    	$payment['config'] = unserialize($payment['config']);   	
		
		$merchantId=$_POST["merchantId"];
		$version=$_POST['version'];
		$language=$_POST['language'];
		$signType=$_POST['signType'];
		$payType=$_POST['payType'];
		$issuerId=$_POST['issuerId'];
		$paymentOrderId=$_POST['paymentOrderId'];
		$orderNo=$_POST['orderNo'];
		$orderDatetime=$_POST['orderDatetime'];
		$orderAmount=$_POST['orderAmount'];
		$payDatetime=$_POST['payDatetime'];
		$payAmount=$_POST['payAmount'];
		$ext1=$_POST['ext1'];
		$ext2=$_POST['ext2'];
		$payResult=$_POST['payResult'];
		$errorCode=$_POST['errorCode'];
		$returnDatetime=$_POST['returnDatetime'];
		$signMsg=$_POST["signMsg"];
		
		
		$bufSignSrc="";
		if($merchantId != "")
		$bufSignSrc=$bufSignSrc."merchantId=".$merchantId."&";		
		if($version != "")
		$bufSignSrc=$bufSignSrc."version=".$version."&";		
		if($language != "")
		$bufSignSrc=$bufSignSrc."language=".$language."&";		
		if($signType != "")
		$bufSignSrc=$bufSignSrc."signType=".$signType."&";		
		if($payType != "")
		$bufSignSrc=$bufSignSrc."payType=".$payType."&";
		if($issuerId != "")
		$bufSignSrc=$bufSignSrc."issuerId=".$issuerId."&";
		if($paymentOrderId != "")
		$bufSignSrc=$bufSignSrc."paymentOrderId=".$paymentOrderId."&";
		if($orderNo != "")
		$bufSignSrc=$bufSignSrc."orderNo=".$orderNo."&";
		if($orderDatetime != "")
		$bufSignSrc=$bufSignSrc."orderDatetime=".$orderDatetime."&";
		if($orderAmount != "")
		$bufSignSrc=$bufSignSrc."orderAmount=".$orderAmount."&";
		if($payDatetime != "")
		$bufSignSrc=$bufSignSrc."payDatetime=".$payDatetime."&";
		if($payAmount != "")
		$bufSignSrc=$bufSignSrc."payAmount=".$payAmount."&";
		if($ext1 != "")
		$bufSignSrc=$bufSignSrc."ext1=".$ext1."&";
		if($ext2 != "")
		$bufSignSrc=$bufSignSrc."ext2=".$ext2."&";
		if($payResult != "")
		$bufSignSrc=$bufSignSrc."payResult=".$payResult."&";
		if($errorCode != "")
		$bufSignSrc=$bufSignSrc."errorCode=".$errorCode."&";
		if($returnDatetime != "")
		$bufSignSrc=$bufSignSrc."returnDatetime=".$returnDatetime;
		
		$keylength = 1024;

		//验签结果
	 	$verify_result = rsa_verify($bufSignSrc,$signMsg, $payment['config']['publickey_exponent'], $payment['config']['publickey_modulus'], $keylength,"sha1");
		
		$value = null;
		if($verify_result){
			$value = "victory";
		}
		else{
			$value = "failed";
		}
		
		//验签成功，还需要判断订单状态，为"1"表示支付成功。
		$payvalue = null;
		$pay_result = false;
		if($verify_result and $payResult == 1){
			$pay_result = true;
			$payvalue = "victory";
		}else{
		        $payvalue = "failed";
		}
	
		if($pay_result){
			//TODO 商户订单处理，显示客户订单付款成功页面
			$payment_notice_id = $orderNo;
			$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where notice_sn = '".$payment_notice_id."'");
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
			require_once APP_ROOT_PATH."system/libs/cart.php";
			$GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set outer_notice_sn = '".$paymentOrderId."' where id = ".$payment_notice['id']);
			
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
		return false;
	}
	
	public function get_display_code()
	{
		$payment_item = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."payment where class_name='Tonglian'");
		if($payment_item)
		{
			$payment_cfg = unserialize($payment_item['config']);

			$html = "";
        	$html .="<script type='text/javascript'>function set_bank(bank_id)";
			$html .="{";
			$html .="$(\"input[name='bank_id']\").val(bank_id);";
			$html .="}</script>";
			foreach ($payment_cfg['issuer_id'] AS $key=>$val)
	        {
	        	$arr = explode("_",$key);
	        	$issuer_id = $arr[0];
	        	$pay_type = isset($arr[1])?$arr[1]:"1";
	            $html.= "<label style='display:block; float:left; width:155px; height:40px;' title='".$this->payment_lang["issuer_id_".$key]."'>";
	            $html.="<input type='radio' name='payment' value='".$payment_item['id']."' rel='".$key."' onclick='set_bank(\"".$key."\")' />";
	            $html.="<img src='".APP_ROOT."/system/payment/Tonglian/bank_".$issuer_id.".gif' style='margin-left:5px;' />";
	            $html.="</label>";
	        }
	        $html .= "<input type='hidden' name='bank_id' />";
			return $html;
		}
		else
		{
			return '';
		}
	}
}
?>