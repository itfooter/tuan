<?php
return array(
'DEFAULT_ADMIN'=>'admin',
'URL_MODEL'=>'0',
'AUTH_KEY'=>'tj',
'TIME_ZONE'=>'8',
'ADMIN_LOG'=>'1',
'DB_VERSION'=>'2.1',
'DB_VOL_MAXSIZE'=>'8000000',
'WATER_MARK'=>'./public/attachment/201011/4cdde85a27105.gif',
'CURRENCY_UNIT'=>'￥',
'BIG_WIDTH'=>'500',
'BIG_HEIGHT'=>'350',
'SMALL_WIDTH'=>'200',
'SMALL_HEIGHT'=>'120',
'WATER_ALPHA'=>'75',
'WATER_POSITION'=>'4',
'MAX_IMAGE_SIZE'=>'300000',
'ALLOW_IMAGE_EXT'=>'jpg,gif,png',
'MAX_FILE_SIZE'=>'1',
'ALLOW_FILE_EXT'=>'1',
'BG_COLOR'=>'#ffffff',
'IS_WATER_MARK'=>'0',
'TEMPLATE'=>'new_meituan',
'SCORE_UNIT'=>'积分',
'USER_VERIFY'=>'1',
'SHOP_LOGO'=>'./public/attachment/201011/4cdd501dc023b.png',
'SHOP_LANG'=>'zh-cn',
'SHOP_TITLE'=>'体检吧-智慧你的康检',
'SHOP_KEYWORD'=>'体检吧-智慧你的康检',
'SHOP_DESCRIPTION'=>'体检吧-智慧你的康检',
'SHOP_TEL'=>'0871-63933870',
'SIDE_DEAL_COUNT'=>'9',
'SIDE_MESSAGE_COUNT'=>'3',
'INVITE_REFERRALS'=>'5',
'INVITE_REFERRALS_TYPE'=>'0',
'ONLINE_MSN'=>'',
'ONLINE_QQ'=>'14732058|892592266',
'ONLINE_TIME'=>'周一至周六 9:00-18:00',
'DEAL_PAGE_SIZE'=>'6',
'PAGE_SIZE'=>'6',
'HELP_CATE_LIMIT'=>'4',
'HELP_ITEM_LIMIT'=>'4',
'SHOP_FOOTER'=>'<div style=\"text-align:center;\">云南眼·爱票网(<a href=\"./\">http://piao.ynyan.cn</a>) 吃喝玩乐 一票搞定&nbsp;<br />
地址：云南省昆明市龙泉路237号春园六栋三单元5楼 &nbsp;客服：0871-63933870</div>
',
'USER_MESSAGE_AUTO_EFFECT'=>'0',
'SHOP_REFERRAL_HELP'=>'当好友接受您的邀请，在 云南眼·爱票网] 上首次成功购买，系统会在 1 小时内返还 ¥5 到您的 [云南眼·爱票网] 电子账户，下次团购时可直接用于支付。没有数量限制，邀请越多，返利越多。<br />
<br />
<span style=\"color:#f10b00;\">友情接示：购买部份团购将不会产生返利或返利特定金额，请查看相关团购的具体说明 </span>',
'SHOP_REFERRAL_SIDE_HELP'=>'<div class=\"side-tip referrals-side\"><h3 class=\"first\">在哪里可以看到我的返利？</h3>
<p>如果邀请成功，在本页面会看到成功邀请列表。在\"账户余额\"页，可看到您目前电子账户的余额。返利金额不返现，可在下次团购时用于支付。</p>
<h3>我邀请好友了，什么时候收到返利？</h3>
<p>返利会在 24 小时内返还到您的帐户，并会发邮件通知您。</p>
<h3>哪些情况会导致邀请返利失效？</h3>
<ul class=\"invalid\"><li>好友点击邀请链接后超过 72 小时才购买</li>
<li>好友购买之前点击了其他人的邀请链接</li>
<li>好友的本次购买不是首次购买</li>
<li>由于最终团购人数没有达到人数下限，本次团购取消</li>
</ul>
<h3>自己邀请自己也能获得返利吗？</h3>
<p>不可以。我们会人工核查，对于查实的作弊行为，扣除一切返利，并取消邀请返利的资格。</p>
</div>
',
'MAIL_SEND_COUPON'=>'1',
'SMS_SEND_COUPON'=>'1',
'MAIL_SEND_PAYMENT'=>'1',
'SMS_SEND_PAYMENT'=>'0',
'REPLY_ADDRESS'=>'281608214@qq.com',
'MAIL_SEND_DELIVERY'=>'1',
'SMS_SEND_DELIVERY'=>'0',
'MAIL_ON'=>'1',
'SMS_ON'=>'1',
'REFERRAL_LIMIT'=>'1',
'SMS_COUPON_LIMIT'=>'3',
'MAIL_COUPON_LIMIT'=>'3',
'COUPON_NAME'=>'优惠券',
'BATCH_PAGE_SIZE'=>'500',
'COUPON_PRINT_TPL'=>'<div style=\"font-size:14px;border-top:#000000 1px solid;border-right:#000000 1px solid;border-bottom:#000000 1px solid;padding-bottom:10px;padding-top:10px;padding-left:10px;margin:0px auto;border-left:#000000 1px solid;padding-right:10px;width:600px;\"><table class=\"dataEdit\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><td width=\"400\"><img border=\"0\" alt=\"\" src=\"./public/attachment/201011/4cdd505195d40.gif\" /> </td>
<td style=\"font-size:22px;font-family:verdana;font-weight:bolder;\" width=\"43%\">序列号：{$bond.sn}<br />
密码：{$bond.password} </td>
</tr>
<tr><td height=\"1\" colspan=\"2\"><div style=\"border-bottom:#000000 1px solid;width:100%;\"></div>
</td>
</tr>
<tr><td height=\"8\" colspan=\"2\"><br />
</td>
</tr>
<tr><td style=\"font-size:28px;height:50px;font-family:微软雅黑;font-weight:bolder;padding-bottom:5px;padding-top:5px;padding-left:5px;padding-right:5px;\" colspan=\"2\">{$bond.name}</td>
</tr>
<tr><td style=\"line-height:22px;padding-right:20px;\" width=\"400\">{$bond.user_name}<br />
生效时间:{$bond.begin_time_format}<br />
过期时间:{$bond.end_time_format}<br />
商家电话：<br />
{$bond.tel}<br />
商家地址:<br />
{$bond.address}<br />
交通路线:<br />
{$bond.route}<br />
营业时间：<br />
{$bond.open_time}<br />
</td>
<td><div id=\"container\" style=\"height:255px;width:255px;\"></div>
<br />
</td>
</tr>
</tbody>
</table>
</div>
',
'PUBLIC_DOMAIN_ROOT'=>'',
'SHOW_DEAL_CATE'=>'1',
'REFERRAL_IP_LIMIT'=>'0',
'UNSUBSCRIBE_MAIL_TIP'=>'您收到此邮件是因为您订阅了%s每日推荐更新。如果您不想继续接收此类邮件，可随时%s',
'CART_ON'=>'1',
'REFERRALS_DELAY'=>'1',
'SUBMIT_DELAY'=>'1',
'APP_MSG_SENDER_OPEN'=>'0',
'ADMIN_MSG_SENDER_OPEN'=>'0',
'SHOP_OPEN'=>'1',
'SHOP_CLOSE_HTML'=>'<p>亲~！升级中~！分分钟就好！ 云南眼 <a href=\"http://www.ynya.cn/\">www.ynya.cn</a></p>
<p>&nbsp;</p>
',
'FOOTER_LOGO'=>'./public/attachment/201011/4cdd50ed013ec.png',
'GZIP_ON'=>'1',
'INTEGRATE_CODE'=>'',
'INTEGRATE_CFG'=>'',
'SHOP_SEO_TITLE'=>'体检吧-智慧你的康检',
'CACHE_ON'=>'0',
'EXPIRED_TIME'=>'0',
'USER_AVATAR'=>'1',
'STYLE_OPEN'=>'1',
'STYLE_DEFAULT'=>'1',
'TMPL_DOMAIN_ROOT'=>'',
'CACHE_TYPE'=>'File',
'MEMCACHE_HOST'=>'127.0.0.1:11211',
'IMAGE_USERNAME'=>'fl',
'IMAGE_PASSWORD'=>'admin',
'MOBILE_MUST'=>'1',
'ATTR_SELECT'=>'1',
'ICP_LICENSE'=>'浙ICP备13003942号',
'COUNT_CODE'=>'',
'DEAL_MSG_LOCK'=>'0',
'PROMOTE_MSG_LOCK'=>'0',
'LIST_TYPE'=>'0',
'CN_LANG'=>'0',
'SUPPLIER_DETAIL'=>'1',
'KUAIDI_APP_KEY'=>'',
'KUAIDI_TYPE'=>'2',
'SEND_SPAN'=>'2',
'MAIL_USE_COUPON'=>'1',
'SMS_USE_COUPON'=>'1',
'LOTTERY_SMS_VERIFY'=>'0',
'LOTTERY_SN_SMS'=>'0',
'EDM_ON'=>'0',
'EDM_USERNAME'=>'',
'EDM_PASSWORD'=>'admin861204mysql',
'USER_LOGIN_SCORE'=>'0',
'USER_LOGIN_MONEY'=>'0',
'USER_REGISTER_SCORE'=>'0',
'USER_REGISTER_MONEY'=>'0',
'BROADCAST'=>'1',
);
 ?>