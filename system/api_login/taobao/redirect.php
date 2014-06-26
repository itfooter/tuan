<?php
		@session_start();
		require_once "alipay_service.class.php";
		$aliapy_config['partner']		= $_SESSION['taobao_app_key'];
		$aliapy_config['key']			=  $_SESSION['taobao_app_secret'];
		$aliapy_config['return_url']   = $_SESSION['taobao_callback'];
		$aliapy_config['sign_type']    = 'MD5';
		$aliapy_config['input_charset']      = 'utf-8';
		$aliapy_config['transport']    = 'http';
		$anti_phishing_key  = '';
		$exter_invoke_ip = '';
		
		$parameter = array(
		        //扩展功能参数——防钓鱼
		        "anti_phishing_key"	=> $anti_phishing_key,
				"exter_invoke_ip"	=> $exter_invoke_ip,
		);
		
		//构造快捷登录接口
		$alipayService = new AlipayService($aliapy_config);
		$html_text = $alipayService->alipay_auth_authorize($parameter);
		echo $html_text;
?>