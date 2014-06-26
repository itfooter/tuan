<?php
//前后台加载的函数库
require_once 'system_init.php';

//获取真实路径
function get_real_path()
{
    return APP_ROOT_PATH;
}

//获取GMTime
function get_gmtime()
{
    return (time() - date('Z'));
}

function to_date($utc_time, $format = 'Y-m-d H:i:s') {
    if (empty ( $utc_time )) {
        return '';
    }
    $timezone = intval(app_conf('TIME_ZONE'));
    $time = $utc_time + $timezone * 3600;
    return date ($format, $time );
}

function to_timespan($str, $format = 'Y-m-d H:i:s')
{
    $timezone = intval(app_conf('TIME_ZONE'));
    //$timezone = 8;
    $time = intval(strtotime($str));
    if($time!=0)
        $time = $time - $timezone * 3600;
    return $time;
}

//获取相应规格的图片地址
//gen=0:保持比例缩放，不剪裁,如高为0，则保证宽度按比例缩放  gen=1：保证长宽，剪裁
function get_spec_image($img_path,$width=0,$height=0,$gen=0,$is_preview=true)
{
    if($width==0)
        $new_path = $img_path;
    else
    {
        $img_name = substr($img_path,0,-4);
        $img_ext = substr($img_path,-3);
        if($is_preview)
            $new_path = $img_name."_".$width."x".$height.".jpg";
        else
            $new_path = $img_name."o_".$width."x".$height.".jpg";
        if(!file_exists(APP_ROOT_PATH.$new_path))
        {
            require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
            $imagec = new es_imagecls();
            $thumb = $imagec->thumb(APP_ROOT_PATH.$img_path,$width,$height,$gen,true,"",$is_preview);

            if(app_conf("PUBLIC_DOMAIN_ROOT")!='')
            {
                $paths = pathinfo($new_path);
                $path = str_replace("./","",$paths['dirname']);
                $filename = $paths['basename'];
                $pathwithoupublic = str_replace("public/","",$path);
                $syn_url = app_conf("PUBLIC_DOMAIN_ROOT")."/es_file.php?username=".app_conf("IMAGE_USERNAME")."&password=".app_conf("IMAGE_PASSWORD")."&file=".get_domain().APP_ROOT."/".$path."/".$filename."&path=".$pathwithoupublic."/&name=".$filename."&act=0";
                @file_get_contents($syn_url);
            }

        }
    }
    return $new_path;
}


//获取客户端IP
function get_client_ip() {
    if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "unknown" ))
        $ip = getenv ( "HTTP_CLIENT_IP" );
    else if (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "unknown" ))
        $ip = getenv ( "HTTP_X_FORWARDED_FOR" );
    else if (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "unknown" ))
        $ip = getenv ( "REMOTE_ADDR" );
    else if (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "unknown" ))
        $ip = $_SERVER ['REMOTE_ADDR'];
    else
        $ip = "unknown";
    return ($ip);
}


//过滤请求
function filter_request(&$request)
{
    if(MAGIC_QUOTES_GPC)
    {
        foreach($request as $k=>$v)
        {
            if(is_array($v))
            {
                filter_request($v);
            }
            else
            {
                $request[$k] = stripslashes(trim($v));
            }
        }
    }

}


function adddeepslashes(&$request)
{
    foreach($request as $k=>$v)
    {
        if(is_array($v))
        {
            adddeepslashes($v);
        }
        else
        {
            $request[$k] = addslashes(trim($v));
        }
    }
}

//清除缓存
function clear_cache()
{
    syn_dealing();
    clear_dir_file(get_real_path()."admin/Runtime/Cache/");
    clear_dir_file(get_real_path()."admin/Runtime/Data/_fields/");
    clear_dir_file(get_real_path()."admin/Runtime/Temp/");
    clear_dir_file(get_real_path()."admin/Runtime/Logs/");
    @unlink(get_real_path()."admin/Runtime/~app.php");
    @unlink(get_real_path()."admin/Runtime/~runtime.php");
    @unlink(get_real_path()."admin/Runtime/lang.js");
    clear_dir_file(get_real_path()."app/Runtime/db_caches/");
    clear_dir_file(get_real_path()."app/Runtime/data_caches/");
    clear_dir_file(get_real_path()."app/Runtime/tpl_caches/");
    clear_dir_file(get_real_path()."app/Runtime/tpl_compiled/");
    @unlink(get_real_path()."app/Runtime/lang.js");

    clear_dir_file(get_real_path()."wap/Runtime/db_caches/");
    clear_dir_file(get_real_path()."wap/Runtime/tpl_caches/");
    clear_dir_file(get_real_path()."wap/Runtime/tpl_compiled/");
    $GLOBALS['cache']->clear();

}
function clear_dir_file($path)
{
    if ( $dir = opendir( $path ) )
    {
        while ( $file = readdir( $dir ) )
        {
            $check = is_dir( $path. $file );
            if ( !$check )
            {
                @unlink( $path . $file );
            }
            else
            {
                if($file!='.'&&$file!='..')
                {
                    clear_dir_file($path.$file."/");
                }
            }
        }
        closedir( $dir );
        rmdir($path);
        return true;
    }
}

//同步未过期团购的状态
function syn_dealing()
{
    $deals = $GLOBALS['db']->getAll("select id from ".DB_PREFIX."deal where time_status <> 2");
    foreach($deals as $v)
    {
        syn_deal_status($v['id']);
    }
}

function check_install()
{
    if(!file_exists(get_real_path()."public/install.lock"))
    {
        clear_cache();
        header('Location:'.APP_ROOT.'/install');
        exit;
    }
}

//同步XXID的团购商品的状态,time_status,buy_status
function syn_deal_status($id)
{
    $deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$id);
    //时间状态
    //1 无开始与结束时间
    if($deal_info['begin_time']==0&&$deal_info['end_time']==0)
    {
        if($deal_info['time_status']!=1)
        {
            $GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 1 where id =".$id);
        }
    }
    //2 无开始时间，有结束时间
    if($deal_info['begin_time']==0&&$deal_info['end_time']!=0)
    {

        //进行中
        if($deal_info['end_time']>get_gmtime())
        {
            if($deal_info['time_status']!=1)
            {
                $GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 1 where id =".$id);
            }
        }
        //过期
        if($deal_info['end_time']<=get_gmtime())
        {
            if($deal_info['time_status']!=2)
            {
                $GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 2 where id =".$id);
            }
        }
    }

    //3 有开始时间，无结束时间
    if($deal_info['begin_time']!=0&&$deal_info['end_time']==0)
    {
        //进行中
        if($deal_info['begin_time']<=get_gmtime())
        {
            if($deal_info['time_status']!=1)
            {
                $GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 1 where id =".$id);
            }
        }
        //未开始
        if($deal_info['begin_time']>get_gmtime())
        {
            if($deal_info['time_status']!=0)
            {
                $GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 0 where id =".$id);
            }
        }
    }

    //4 开始结束都有时间
    if($deal_info['begin_time']!=0&&$deal_info['end_time']!=0)
    {
        //未开始
        if($deal_info['begin_time']>get_gmtime())
        {
            if($deal_info['time_status']!=0)
            {
                $GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 0 where id =".$id);
            }
        }
        //进行中
        if($deal_info['begin_time']<=get_gmtime()&&$deal_info['end_time']>get_gmtime())
        {
            if($deal_info['time_status']!=1)
            {
                $GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 1 where id =".$id);
            }
        }
        //过期

        if($deal_info['end_time']<=get_gmtime())
        {
            if($deal_info['time_status']!=2)
            {
                $GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 2 where id =".$id);
            }
        }
    }

    //开始更新 buy_status

    //未成功
    if($deal_info['buy_count']<$deal_info['min_bought'])
    {
        if($deal_info['buy_status']!=0)
        {
            $GLOBALS['db']->query("update ".DB_PREFIX."deal set buy_status = 0,success_time = 0 where id =".$id);
        }
    }
    //成功未卖光
    if($deal_info['buy_count']>=$deal_info['min_bought']&&(($deal_info['buy_count']<$deal_info['max_bought']&&$deal_info['max_bought']>0)||$deal_info['max_bought']==0))
    {
        if($deal_info['buy_status']!=1)
        {
            $GLOBALS['db']->query("update ".DB_PREFIX."deal set buy_status = 1,success_time=".get_gmtime()." where id =".$id);
        }
    }
    //卖光
    if($deal_info['buy_count']>=$deal_info['max_bought']&&$deal_info['max_bought']>0) //库存零表示不限
    {
        if($deal_info['buy_status']!=2)
        {
            $GLOBALS['db']->query("update ".DB_PREFIX."deal set buy_status = 2 where id =".$id);
        }
    }

    //同步成功后，发相应的团购券发券
    $buy_status = $GLOBALS['db']->getOne("select buy_status from ".DB_PREFIX."deal where id = ".$id);
    if($buy_status > 0)
    {
        //成功后发券, 将user_id <> 0 且 is_valid = 0的发放出去
        $deal_coupons = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_coupon where user_id <> 0 and is_valid = 0 and is_delete = 0 and deal_id = ".$id);
        foreach($deal_coupons as $deal_coupon)
        {
            send_deal_coupon($deal_coupon['id']);
        }
    }

    $GLOBALS['db']->query("update ".DB_PREFIX."deal set reopen = 0 where id = ".$id." and time_status <> 2");
}

//发放团购券
function send_deal_coupon($deal_coupon_id)
{
    $GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set is_valid = 1 where id = ".$deal_coupon_id." and user_id <> 0 and is_delete = 0 and is_valid = 0");
    $rs = $GLOBALS['db']->affected_rows();
    if($rs)
    {
        //发邮件团购券
        send_deal_coupon_mail($deal_coupon_id);
        //发短信团购券
        send_deal_coupon_sms($deal_coupon_id);
    }
}

//发邮件团购券
function send_deal_coupon_mail($deal_coupon_id)
{
    if(app_conf("MAIL_ON")==1&&app_conf("MAIL_SEND_COUPON")==1)
    {
        $coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$deal_coupon_id);
        if($coupon_data)
        {
            $tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_COUPON'");
            $tmpl_content = $tmpl['content'];
            $coupon_data['begin_time_format'] = $coupon_data['begin_time']==0?$GLOBALS['lang']['NO_BEGIN_TIME']:to_date($coupon_data['begin_time'],'Y-m-d');
            $coupon_data['end_time_format'] = $coupon_data['end_time']==0?$GLOBALS['lang']['NO_END_TIME']:to_date($coupon_data['end_time'],'Y-m-d');
            $user_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."user where id = ".$coupon_data['user_id']);
            $coupon_data['user_name'] = $user_info['user_name'];
            $coupon_data['deal_name'] = $GLOBALS['db']->getOneCached("select name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
            $coupon_data['deal_sub_name'] = $GLOBALS['db']->getOneCached("select sub_name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
            $deal_id = $coupon_data['deal_id'];
            if(!$coupon_data['deal_name']||!$coupon_data['deal_sub_name'])
            {
                $deal_info = $GLOBALS['db']->getRow("select name,sub_name from ".DB_PREFIX."deal where id = ".$deal_id);
                if(!$coupon_data['deal_name'])
                    $coupon_data['deal_name'] = $deal_info['name'];
                if(!$coupon_data['deal_sub_name'])
                    $coupon_data['deal_sub_name'] = $deal_info['sub_name'];
            }
            $order_item = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_order_item where id = ".intval($coupon_data['order_deal_id']));
            $deal_type = intval($GLOBALS['db']->getOneCached("select deal_type from ".DB_PREFIX."deal where id = ".intval($order_item['deal_id'])));
            if($deal_type == 1&&$order_item)
            {
                $coupon_data['deal_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
                $coupon_data['deal_sub_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
            }
            $supplier_id = $GLOBALS['db']->getOne("select supplier_id from ".DB_PREFIX."deal where id = ".$deal_id);
            $coupon_data['supplier_tel'] = $GLOBALS['db']->getOne("select tel from ".DB_PREFIX."supplier_location where supplier_id = ".$supplier_id." and is_main = 1");
            $coupon_data['supplier_address'] = $GLOBALS['db']->getOne("select address from ".DB_PREFIX."supplier_location where supplier_id = ".$supplier_id." and is_main = 1");

            $GLOBALS['tmpl']->assign("coupon",$coupon_data);
            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
            $msg_data['dest'] = $user_info['email'];
            $msg_data['send_type'] = 1;
            $msg_data['title'] = $GLOBALS['lang']['YOU_GOT_COUPON'];
            $msg_data['content'] = addslashes($msg);
            $msg_data['send_time'] = 0;
            $msg_data['is_send'] = 0;
            $msg_data['create_time'] = get_gmtime();
            $msg_data['user_id'] = $user_info['id'];
            $msg_data['is_html'] = $tmpl['is_html'];
            $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入

        }
    }
}

//发短信团购券
function send_deal_coupon_sms($deal_coupon_id)
{
    if(app_conf("SMS_ON")==1&&app_conf("SMS_SEND_COUPON")==1)
    {
        $coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$deal_coupon_id);
        if($coupon_data)
        {
            $forbid_sms = intval($GLOBALS['db']->getOneCached("select forbid_sms from ".DB_PREFIX."deal where id = ".$coupon_data['deal_id']));
            if($forbid_sms==0)
            {
                $user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$coupon_data['user_id']);
                if($user_info['mobile']!='')
                {
                    $tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_COUPON'");
                    $tmpl_content = $tmpl['content'];
                    $coupon_data['begin_time_format'] = $coupon_data['begin_time']==0?$GLOBALS['lang']['NO_BEGIN_TIME']:to_date($coupon_data['begin_time'],'Y-m-d');
                    $coupon_data['end_time_format'] = $coupon_data['end_time']==0?$GLOBALS['lang']['NO_END_TIME']:to_date($coupon_data['end_time'],'Y-m-d');
                    $coupon_data['user_name'] = $user_info['user_name'];
                    $coupon_data['deal_name'] = $GLOBALS['db']->getOneCached("select name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
                    $coupon_data['deal_sub_name'] = $GLOBALS['db']->getOneCached("select sub_name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
                    $deal_id = $coupon_data['deal_id'];
                    if(!$coupon_data['deal_name']||!$coupon_data['deal_sub_name'])
                    {
                        $deal_info = $GLOBALS['db']->getRow("select name,sub_name from ".DB_PREFIX."deal where id = ".$deal_id);
                        if(!$coupon_data['deal_name'])
                            $coupon_data['deal_name'] = $deal_info['name'];
                        if(!$coupon_data['deal_sub_name'])
                            $coupon_data['deal_sub_name'] = $deal_info['sub_name'];
                    }
                    $order_item = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_order_item where id = ".intval($coupon_data['order_deal_id']));
                    $deal_type = intval($GLOBALS['db']->getOneCached("select deal_type from ".DB_PREFIX."deal where id = ".intval($order_item['deal_id'])));
                    if($deal_type == 1&&$order_item)
                    {
                        $coupon_data['deal_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
                        $coupon_data['deal_sub_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
                    }

                    $supplier_id = $GLOBALS['db']->getOne("select supplier_id from ".DB_PREFIX."deal where id = ".$deal_id);
                    $coupon_data['supplier_tel'] = $GLOBALS['db']->getOne("select tel from ".DB_PREFIX."supplier_location where supplier_id = ".$supplier_id." and is_main = 1");
                    $coupon_data['supplier_address'] = $GLOBALS['db']->getOne("select address from ".DB_PREFIX."supplier_location where supplier_id = ".$supplier_id." and is_main = 1");

                    $GLOBALS['tmpl']->assign("coupon",$coupon_data);
                    $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
                    $msg_data['dest'] = $user_info['mobile'];
                    $msg_data['send_type'] = 0;
                    $msg_data['content'] = addslashes($msg);;
                    $msg_data['send_time'] = 0;
                    $msg_data['is_send'] = 0;
                    $msg_data['create_time'] = get_gmtime();
                    $msg_data['user_id'] = $user_info['id'];
                    $msg_data['is_html'] = $tmpl['is_html'];
                    $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
                }
            }
        }
    }
}


//发团购券确认使用的短信
function send_use_coupon_sms($deal_coupon_id)
{
    if(app_conf("SMS_ON")==1&&app_conf("SMS_USE_COUPON")==1)
    {
        $coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$deal_coupon_id);
        if($coupon_data)
        {
            $user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$coupon_data['user_id']);
            if($user_info['mobile']!='')
            {
                $tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_USE_COUPON'");
                $tmpl_content = $tmpl['content'];
                $coupon_data['confirm_time_format'] = to_date($coupon_data['confirm_time'],'Y-m-d H:i:s');
                $coupon_data['user_name'] = $user_info['user_name'];
                $coupon_data['deal_name'] = $GLOBALS['db']->getOneCached("select name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
                $coupon_data['deal_sub_name'] = $GLOBALS['db']->getOneCached("select sub_name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
                $deal_id = $coupon_data['deal_id'];
                if(!$coupon_data['deal_name']||!$coupon_data['deal_sub_name'])
                {
                    $deal_info = $GLOBALS['db']->getRow("select name,sub_name from ".DB_PREFIX."deal where id = ".$deal_id);
                    if(!$coupon_data['deal_name'])
                        $coupon_data['deal_name'] = $deal_info['name'];
                    if(!$coupon_data['deal_sub_name'])
                        $coupon_data['deal_sub_name'] = $deal_info['sub_name'];
                }
                $order_item = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_order_item where id = ".intval($coupon_data['order_deal_id']));
                $deal_type = intval($GLOBALS['db']->getOneCached("select deal_type from ".DB_PREFIX."deal where id = ".intval($order_item['deal_id'])));
                if($deal_type == 1&&$order_item)
                {
                    $coupon_data['deal_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
                    $coupon_data['deal_sub_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
                }

                $supplier_id = $GLOBALS['db']->getOne("select supplier_id from ".DB_PREFIX."deal where id = ".$deal_id);
                $coupon_data['supplier_tel'] = $GLOBALS['db']->getOne("select tel from ".DB_PREFIX."supplier_location where supplier_id = ".$supplier_id." and is_main = 1");
                $coupon_data['supplier_address'] = $GLOBALS['db']->getOne("select address from ".DB_PREFIX."supplier_location where supplier_id = ".$supplier_id." and is_main = 1");

                $GLOBALS['tmpl']->assign("coupon",$coupon_data);
                $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
                $msg_data['dest'] = $user_info['mobile'];
                $msg_data['send_type'] = 0;
                $msg_data['content'] = addslashes($msg);;
                $msg_data['send_time'] = 0;
                $msg_data['is_send'] = 0;
                $msg_data['create_time'] = get_gmtime();
                $msg_data['user_id'] = $user_info['id'];
                $msg_data['is_html'] = $tmpl['is_html'];
                $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
            }
        }
    }
}


//发团购券确认使用的邮件
function send_use_coupon_mail($deal_coupon_id)
{
    if(app_conf("MAIL_ON")==1&&app_conf("MAIL_USE_COUPON")==1)
    {
        $coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$deal_coupon_id);
        if($coupon_data)
        {
            $user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$coupon_data['user_id']);

            $tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_USE_COUPON'");
            $tmpl_content = $tmpl['content'];
            $coupon_data['confirm_time_format'] = to_date($coupon_data['confirm_time'],'Y-m-d H:i:s');
            $coupon_data['user_name'] = $user_info['user_name'];
            $coupon_data['deal_name'] = $GLOBALS['db']->getOneCached("select name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
            $coupon_data['deal_sub_name'] = $GLOBALS['db']->getOneCached("select sub_name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);


            $deal_id = $coupon_data['deal_id'];
            if(!$coupon_data['deal_name']||!$coupon_data['deal_sub_name'])
            {
                $deal_info = $GLOBALS['db']->getRow("select name,sub_name from ".DB_PREFIX."deal where id = ".$deal_id);
                if(!$coupon_data['deal_name'])
                    $coupon_data['deal_name'] = $deal_info['name'];
                if(!$coupon_data['deal_sub_name'])
                    $coupon_data['deal_sub_name'] = $deal_info['sub_name'];
            }
            $order_item = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_order_item where id = ".intval($coupon_data['order_deal_id']));
            $deal_type = intval($GLOBALS['db']->getOneCached("select deal_type from ".DB_PREFIX."deal where id = ".intval($order_item['deal_id'])));
            if($deal_type == 1&&$order_item)
            {
                $coupon_data['deal_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
                $coupon_data['deal_sub_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
            }
            $GLOBALS['tmpl']->assign("coupon",$coupon_data);
            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
            $msg_data['dest'] = $user_info['email'];
            $msg_data['send_type'] = 1;
            $msg_data['content'] = addslashes($msg);;
            $msg_data['send_time'] = 0;
            $msg_data['is_send'] = 0;
            $msg_data['create_time'] = get_gmtime();
            $msg_data['user_id'] = $user_info['id'];
            $msg_data['is_html'] = $tmpl['is_html'];
            $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入

        }
    }
}


//发短信抽奖
function send_lottery_sms($lottery_id)
{
    if(app_conf("SMS_ON")==1&&app_conf("LOTTERY_SN_SMS")==1&&$lottery_id>0)
    {
        $lottery_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."lottery where id = ".$lottery_id);
        if($lottery_data)
        {
            $user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$lottery_data['user_id']);

            $tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_LOTTERY'");
            $tmpl_content = $tmpl['content'];
            $lottery_data['user_name'] = $user_info['user_name'];
            $lottery_data['deal_name'] = $GLOBALS['db']->getOneCached("select name from ".DB_PREFIX."deal where id = ".$lottery_data['deal_id']);
            $lottery_data['deal_sub_name'] = $GLOBALS['db']->getOneCached("select sub_name from ".DB_PREFIX."deal where id = ".$lottery_data['deal_id']);

            $GLOBALS['tmpl']->assign("lottery",$lottery_data);
            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
            $msg_data['dest'] = $lottery_data['mobile'];
            $msg_data['send_type'] = 0;
            $msg_data['content'] = addslashes($msg);;
            $msg_data['send_time'] = 0;
            $msg_data['is_send'] = 0;
            $msg_data['create_time'] = get_gmtime();
            $msg_data['user_id'] = $user_info['id'];
            $msg_data['is_html'] = $tmpl['is_html'];
            $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入

        }
    }
}

//发注册验证邮件
function send_user_verify_mail($user_id)
{
    if(app_conf("MAIL_ON")==1)
    {
        $verify_code = rand(111111,999999);
        $GLOBALS['db']->query("update ".DB_PREFIX."user set verify = '".$verify_code."' where id = ".$user_id);
        $user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);
        if($user_info)
        {
            $tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_USER_VERIFY'");
            $tmpl_content=  $tmpl['content'];
            $user_info['verify_url'] = get_domain().url_pack("user#verify",$user_info['id']."&code=".$user_info['verify']);
            $GLOBALS['tmpl']->assign("user",$user_info);
            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
            $msg_data['dest'] = $user_info['email'];
            $msg_data['send_type'] = 1;
            $msg_data['title'] = $GLOBALS['lang']['REGISTER_SUCCESS'];
            $msg_data['content'] = addslashes($msg);;
            $msg_data['send_time'] = 0;
            $msg_data['is_send'] = 0;
            $msg_data['create_time'] = get_gmtime();
            $msg_data['user_id'] = $user_info['id'];
            $msg_data['is_html'] = $tmpl['is_html'];
            $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
        }
    }
}


//发密码验证邮件
function send_user_password_mail($user_id)
{
    if(app_conf("MAIL_ON")==1)
    {
        $verify_code = rand(111111,999999);
        $GLOBALS['db']->query("update ".DB_PREFIX."user set password_verify = '".$verify_code."' where id = ".$user_id);
        $user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);
        if($user_info)
        {
            $tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_USER_PASSWORD'");
            $tmpl_content=  $tmpl['content'];
            $user_info['password_url'] = get_domain().url_pack("user#modify_password&code=".$user_info['password_verify']."&id=".$user_info['id']);
            $GLOBALS['tmpl']->assign("user",$user_info);
            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
            $msg_data['dest'] = $user_info['email'];
            $msg_data['send_type'] = 1;
            $msg_data['title'] = $GLOBALS['lang']['RESET_PASSWORD'];
            $msg_data['content'] = addslashes($msg);
            $msg_data['send_time'] = 0;
            $msg_data['is_send'] = 0;
            $msg_data['create_time'] = get_gmtime();
            $msg_data['user_id'] = $user_info['id'];
            $msg_data['is_html'] = $tmpl['is_html'];
            $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
        }
    }
}


//发短信收款单
function send_payment_sms($notice_id)
{
    if(app_conf("SMS_ON")==1&&app_conf("SMS_SEND_PAYMENT")==1)
    {
        $notice_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$notice_id);
        if($notice_data)
        {
            $user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$notice_data['user_id']);
            $order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$notice_data['order_id']);
            if($user_info['mobile']!=''||$order_info['mobile']!='')
            {
                $tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_PAYMENT'");
                $tmpl_content = $tmpl['content'];
                $notice_data['user_name'] = $user_info['user_name'];
                $notice_data['order_sn'] = $GLOBALS['db']->getOneCached("select order_sn from ".DB_PREFIX."deal_order where id = ".$notice_data['order_id']);
                $notice_data['pay_time_format'] = to_date($notice_data['pay_time']);
                $notice_data['money_format'] = format_price($notice_data['money']);
                $GLOBALS['tmpl']->assign("payment_notice",$notice_data);
                $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
                if($user_info['mobile']!='')
                {
                    $msg_data['dest'] = $user_info['mobile'];
                }
                else
                {
                    $msg_data['dest'] = $order_info['mobile'];
                }
                $msg_data['send_type'] = 0;
                $msg_data['content'] = addslashes($msg);;
                $msg_data['send_time'] = 0;
                $msg_data['is_send'] = 0;
                $msg_data['create_time'] = get_gmtime();
                $msg_data['user_id'] = $user_info['id'];
                $msg_data['is_html'] = $tmpl['is_html'];
                $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入

            }
        }
    }
}

//发邮件收款单
function send_payment_mail($notice_id)
{
    if(app_conf("MAIL_ON")==1&&app_conf("MAIL_SEND_PAYMENT")==1)
    {
        $notice_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$notice_id);
        if($notice_data)
        {
            $user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$notice_data['user_id']);
            if($user_info['email']!='')
            {
                $tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_PAYMENT'");
                $tmpl_content = $tmpl['content'];
                $notice_data['user_name'] = $user_info['user_name'];
                $notice_data['order_sn'] = $GLOBALS['db']->getOneCached("select order_sn from ".DB_PREFIX."deal_order where id = ".$notice_data['order_id']);
                $notice_data['pay_time_format'] = to_date($notice_data['pay_time']);
                $notice_data['money_format'] = format_price($notice_data['money']);
                $GLOBALS['tmpl']->assign("payment_notice",$notice_data);
                $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
                $msg_data['dest'] = $user_info['email'];
                $msg_data['send_type'] = 1;
                $msg_data['title'] = $GLOBALS['lang']['PAYMENT_NOTICE'];
                $msg_data['content'] = addslashes($msg);;
                $msg_data['send_time'] = 0;
                $msg_data['is_send'] = 0;
                $msg_data['create_time'] = get_gmtime();
                $msg_data['user_id'] = $user_info['id'];
                $msg_data['is_html'] = $tmpl['is_html'];
                $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
            }
        }
    }
}



//发邮件发货单
function send_delivery_mail($notice_sn,$deal_names = '',$order_id)
{
    if(app_conf("MAIL_ON")==1&&app_conf("MAIL_SEND_DELIVERY")==1)
    {
        $notice_data = $GLOBALS['db']->getRow("select dn.* from ".DB_PREFIX."delivery_notice as dn left join ".DB_PREFIX."deal_order_item as doi on dn.order_item_id = doi.id where dn.notice_sn = '".$notice_sn."' and doi.order_id = ".$order_id);
        if($notice_data)
        {
            $user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$notice_data['user_id']);
            if($user_info['email']!='')
            {
                $tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_DELIVERY'");
                $tmpl_content = $tmpl['content'];
                $notice_data['user_name'] = $user_info['user_name'];
                $notice_data['order_sn'] = $GLOBALS['db']->getOneCached("select do.order_sn from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.id = ".$notice_data['order_item_id']);
                $notice_data['delivery_time_format'] = to_date($notice_data['delivery_time']);
                $notice_data['deal_names'] = $deal_names;
                $GLOBALS['tmpl']->assign("delivery_notice",$notice_data);
                $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
                $msg_data['dest'] = $user_info['email'];
                $msg_data['send_type'] = 1;
                $msg_data['title'] = $GLOBALS['lang']['DELIVERY_NOTICE'];
                $msg_data['content'] = addslashes($msg);;
                $msg_data['send_time'] = 0;
                $msg_data['is_send'] = 0;
                $msg_data['create_time'] = get_gmtime();
                $msg_data['user_id'] = $user_info['id'];
                $msg_data['is_html'] = $tmpl['is_html'];
                $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
            }
        }
    }
}

//发短信发货单
function send_delivery_sms($notice_sn,$deal_names = '',$order_id)
{
    if(app_conf("SMS_ON")==1&&app_conf("SMS_SEND_DELIVERY")==1)
    {
        $notice_data = $GLOBALS['db']->getRow("select dn.* from ".DB_PREFIX."delivery_notice as dn left join ".DB_PREFIX."deal_order_item as doi on dn.order_item_id = doi.id where dn.notice_sn = '".$notice_sn."' and doi.order_id = ".$order_id);
        if($notice_data)
        {
            $order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
            $user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$notice_data['user_id']);
            if($user_info['mobile']!=''||$order_info['mobile']!='')
            {
                $tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_DELIVERY'");
                $tmpl_content = $tmpl['content'];
                $notice_data['user_name'] = $user_info['user_name'];
                $notice_data['order_sn'] = $GLOBALS['db']->getOneCached("select do.order_sn from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.id = ".$notice_data['order_item_id']);
                $notice_data['delivery_time_format'] = to_date($notice_data['delivery_time']);
                $notice_data['deal_names'] = $deal_names;
                $GLOBALS['tmpl']->assign("delivery_notice",$notice_data);
                $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
                if($user_info['mobile']!='')
                {
                    $msg_data['dest'] = $user_info['mobile'];
                    $msg_data['send_type'] = 0;
                    $msg_data['content'] = addslashes($msg);;
                    $msg_data['send_time'] = 0;
                    $msg_data['is_send'] = 0;
                    $msg_data['create_time'] = get_gmtime();
                    $msg_data['user_id'] = $user_info['id'];
                    $msg_data['is_html'] = $tmpl['is_html'];
                    $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
                }

                if($order_info['mobile']!=''&&$order_info['mobile']!=$user_info['mobile'])
                {
                    $msg_data['dest'] = $order_info['mobile'];
                    $msg_data['send_type'] = 0;
                    $msg_data['content'] = addslashes($msg);;
                    $msg_data['send_time'] = 0;
                    $msg_data['is_send'] = 0;
                    $msg_data['create_time'] = get_gmtime();
                    $msg_data['user_id'] = $user_info['id'];
                    $msg_data['is_html'] = $tmpl['is_html'];
                    $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
                }
            }
        }
    }
}


//发短信验证码
function send_verify_sms($mobile,$code)
{
    if(app_conf("SMS_ON")==1)
    {

        $tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_VERIFY_CODE'");
        $tmpl_content = $tmpl['content'];
        $verify['mobile'] = $mobile;
        $verify['code'] = $code;
        $GLOBALS['tmpl']->assign("verify",$verify);
        $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
        $msg_data['dest'] = $mobile;
        $msg_data['send_type'] = 0;
        $msg_data['content'] = addslashes($msg);;
        $msg_data['send_time'] = 0;
        $msg_data['is_send'] = 0;
        $msg_data['create_time'] = get_gmtime();
        $msg_data['user_id'] = $user_info['id'];
        $msg_data['is_html'] = $tmpl['is_html'];
        $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
    }
}


//发邮件退订验证
function send_unsubscribe_mail($email)
{
    if(app_conf("MAIL_ON")==1)
    {
        if($email)
        {
            $GLOBALS['db']->query("update ".DB_PREFIX."mail_list set code = '".rand(1111,9999)."' where mail_address='".$email."' and code = ''");
            $email_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mail_list where mail_address = '".$email."' and code <> ''");
            if($email_item)
            {
                $tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_UNSUBSCRIBE'");
                $tmpl_content = $tmpl['content'];
                $mail = $email_item;
                $mail['url'] = get_domain().url_pack("subscribe#dounsubscribe&code=".base64_encode($mail['code']."|".$mail['mail_address']));
                $GLOBALS['tmpl']->assign("mail",$mail);
                $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
                $msg_data['dest'] = $mail['mail_address'];
                $msg_data['send_type'] = 1;
                $msg_data['title'] = $GLOBALS['lang']['MAIL_UNSUBSCRIBE'];
                $msg_data['content'] = addslashes($msg);;
                $msg_data['send_time'] = 0;
                $msg_data['is_send'] = 0;
                $msg_data['create_time'] = get_gmtime();
                $msg_data['user_id'] = 0;
                $msg_data['is_html'] = $tmpl['is_html'];
                $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
            }
        }
    }
}

function get_deal_cate_name($cate_id)
{
    return $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate where id =".$cate_id);
}

function get_deal_city_name($city_id)
{
    return $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_city where id =".$city_id);
}

function get_deal_area_name($area_id)
{
    return $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_city_area where id =".$area_id);
}

function format_price($price)
{
    return app_conf("CURRENCY_UNIT")."".(round($price,2));
}
function format_score($score)
{
    return intval($score)."".app_conf("SCORE_UNIT");
}

//utf8 字符串截取
function msubstr($str, $start=0, $length=15, $charset="utf-8", $suffix=true)
{
    if(function_exists("mb_substr"))
    {
        $slice =  mb_substr($str, $start, $length, $charset);
        if($suffix&$slice!=$str) return $slice."…";
        return $slice;
    }
    elseif(function_exists('iconv_substr')) {
        return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix&&$slice!=$str) return $slice."…";
    return $slice;
}


//字符编码转换
if(!function_exists("iconv"))
{
    function iconv($in_charset,$out_charset,$str)
    {
        require 'libs/iconv.php';
        $chinese = new Chinese();
        return $chinese->Convert($in_charset,$out_charset,$str);
    }
}

//JSON兼容
if(!function_exists("json_encode"))
{
    function json_encode($data)
    {
        require_once 'libs/json.php';
        $JSON = new JSON();
        return $JSON->encode($data);
    }
}
if(!function_exists("json_decode"))
{
    function json_decode($data)
    {
        require_once 'libs/json.php';
        $JSON = new JSON();
        return $JSON->decode($data,1);
    }
}

//邮件格式验证的函数
function check_email($email)
{
    if(!preg_match("/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/",$email))
    {
        return false;
    }
    else
        return true;
}

//验证手机号码
function check_mobile($mobile)
{
    if(!empty($mobile) && !preg_match( "/^(13\d{9}|14\d{9}|18\d{9}|15\d{9})|(0\d{9}|9\d{8})$/", $mobile))
    {
        return false;
    }
    else
        return true;
}

//跳转
function app_redirect($url,$time=0,$msg='')
{
    //多行URL地址支持
    $url = str_replace(array("\n", "\r"), '', $url);
    if(empty($msg))
        $msg    =   "系统将在{$time}秒之后自动跳转到{$url}！";
    if (!headers_sent()) {
        // redirect
        if(0===$time) {
            if(substr($url,0,1)=="/")
            {
                header("Location:".get_domain().$url);
            }
            else
            {
                header("Location:".$url);
            }

        }else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    }else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if($time!=0)
            $str   .=   $msg;
        exit($str);
    }
}


function get_http()
{
    return (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';
}
function get_domain()
{
    /* 协议 */
    $protocol = get_http();

    /* 域名或IP地址 */
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST']))
    {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    }
    elseif (isset($_SERVER['HTTP_HOST']))
    {
        $host = $_SERVER['HTTP_HOST'];
    }
    else
    {
        /* 端口 */
        if (isset($_SERVER['SERVER_PORT']))
        {
            $port = ':' . $_SERVER['SERVER_PORT'];

            if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol))
            {
                $port = '';
            }
        }
        else
        {
            $port = '';
        }

        if (isset($_SERVER['SERVER_NAME']))
        {
            $host = $_SERVER['SERVER_NAME'] . $port;
        }
        elseif (isset($_SERVER['SERVER_ADDR']))
        {
            $host = $_SERVER['SERVER_ADDR'] . $port;
        }
    }

    return $protocol . $host;
}
/**
 * 验证访问IP的有效性
 * @param ip地址 $ip_str
 * @param 访问页面 $module
 * @param 时间间隔 $time_span
 * @param 数据ID $id
 */
function check_ipop_limit($ip_str,$module,$time_span=0,$id=0)
{
    if(empty($_SESSION[$module."_".$id."_ip"]))
    {
        $check['ip']	=	 get_client_ip();
        $check['time']	=	get_gmtime();
        $_SESSION[$module."_".$id."_ip"] = $check;

        return true;  //不存在session时验证通过
    }
    else
    {
        $check['ip']	=	 get_client_ip();
        $check['time']	=	get_gmtime();
        $origin	=	$_SESSION[$module."_".$id."_ip"];

        if($check['ip']==$origin['ip'])
        {
            if($check['time'] - $origin['time'] < $time_span)
            {
                return false;
            }
            else
            {
                $_SESSION[$module."_".$id."_ip"] = $check;
                return true;  //不存在session时验证通过
            }
        }
        else
        {
            $_SESSION[$module."_".$id."_ip"] = $check;
            return true;  //不存在session时验证通过
        }
    }
}

//发放返利的函数
function pay_referrals($id)
{
    $referrals_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."referrals where id = ".$id);
    if($referrals_data)
    {
        $sql = "update ".DB_PREFIX."referrals set pay_time = ".get_gmtime()." where id = ".$id." and pay_time = 0 ";
        $GLOBALS['db']->query($sql);
        $rs = $GLOBALS['db']->affected_rows();
        if($rs)
        {
            //开始发放返利
            require_once APP_ROOT_PATH."system/libs/user.php";
            $order_sn = $GLOBALS['db']->getOneCached("select order_sn from ".DB_PREFIX."deal_order where id = ".$referrals_data['order_id']);
            $user_name = $GLOBALS['db']->getOneCached("select user_name from ".DB_PREFIX."user where id = ".$referrals_data['user_id']);
            $rel_user_name = $GLOBALS['db']->getOneCached("select user_name from ".DB_PREFIX."user where id = ".$referrals_data['rel_user_id']);
            $referral_amount = $referrals_data['money']>0?format_price($referrals_data['money']):format_score($referrals_data['score']);
            $msg = sprintf($GLOBALS['lang']['REFERRALS_LOG'],$order_sn,$rel_user_name,$referral_amount);
            modify_account(array('money'=>$referrals_data['money'],'score'=>$referrals_data['score']),$referrals_data['user_id'],$msg);
            return true;
        }
        else
        {
            return false;
        }
    }
    else
    {
        return false;
    }
}
//发货的通用函数
/**
 *
 * @param $order_id 订单ID
 * @param $order_deal_id  发货的订单商品ID
 * @param $delivery_sn  发货号
 */
function make_delivery_notice($order_id,$order_deal_id,$delivery_sn,$memo='',$express_id = 0)
{
    //先删除原先相关的发货单号
    $GLOBALS['db']->query("delete from ".DB_PREFIX."delivery_notice where order_item_id = ".$order_deal_id);
    $order_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
    $delivery_notice['notice_sn'] = $delivery_sn;
    $delivery_notice['delivery_time'] = get_gmtime();
    $delivery_notice['order_item_id'] = $order_deal_id;
    $delivery_notice['user_id'] = $order_info['user_id'];
    $adm_session = $_SESSION[md5(app_conf("AUTH_KEY"))];
    $adm_id = intval($adm_session['adm_id']);
    $delivery_notice['admin_id'] = $adm_id;
    $delivery_notice['memo'] = $memo;
    $delivery_notice['express_id'] = $express_id;
    $GLOBALS['db']->autoExecute(DB_PREFIX."delivery_notice",$delivery_notice,'INSERT','','SILENT');
    return $GLOBALS['db']->insert_id();
}

//邮件团购模板的变量
//$deal.city_name   //当前团购的城市
//$deal.send_date   //发送时间
//$deal.url		  //团购的完整URL
//$deal.current_price	//现价
//$deal.origin_price	//原价
//$deal.discount_off	//xx折
//$deal.save_money	//节省xx钱
//$deal.saler_name	//商户名称
//$deal.saler_address	//商户默认的地址
//$deal.saler_map  //商户地图
//$deal.saler_tel		//电话
//$deal.referrals     //返利
//$deal.referrals_url	//返利的地址
//封装url
function url_pack_4mail($url,$id = 0,$deal_city)
{
    $citys = $GLOBALS['db']->getAllCached("select id,name,is_open from ".DB_PREFIX."deal_city where is_effect = 1 and is_delete = 0");
    $show_city = count($citys)>1?true:false;  //有多个城市时显示城市名称到url


    $arr = explode("#",$url);
    $module = $arr[0];
    $action = $arr[1];

    if($module=='deal')
    {
        $deal_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal where id = ".$id);

        if($deal_info['buy_type']==1)
        {
            $module = "score";
            $action = "show";
        }

    }

    if(app_conf("URL_MODEL")==0)
    {
        //原始
        $url = APP_ROOT."/".$module.".php?";
        if($show_city)
        {
            $city_uname = $deal_city['uname'];
            $url = $url."city=".$city_uname."&";
        }

        if($action&&$action!='')
            $url .= "act=".$action."&";
        if(intval($id)!=0)
            $url .= "id=".$id."&";
    }
    else
    {
        //重写
        if($show_city)
        {
            $city_uname = $deal_city['uname'];
            $url = APP_ROOT."/".$city_uname;
        }
        else
        {
            $url = APP_ROOT;
        }
        if($module!='index')
            $url = $url."/".$module;
        if($action&&$action!=''&&$action!='index')
            $url .= "/".$action;
        if(intval($id)!=0)
            $url .= "/".$id;
    }
    return $url;

}
function get_deal_mail_content($deal_rs)
{
    $tmpl_content = file_get_contents(APP_ROOT_PATH."app/Tpl/".app_conf("TEMPLATE")."/deal_mail.html");
    $GLOBALS['tmpl']->assign("APP_ROOT",APP_ROOT);

    if($deal_rs)
    {
        foreach($deal_rs as $k=>$deal)
        {
            $deal_city = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_city where id = ".$deal['city_id']);
            $deal['city_name'] = $deal_city['name'];

            $send_date = to_date(get_gmtime(),'Y年m月d日');
            $weekarray = array("日","一","二","三","四","五","六");
            $send_date .= " 星期".$weekarray[to_date(get_gmtime(),"w")];
            $deal['send_date'] = $send_date;

            $deal['url'] = get_domain().url_pack_4mail("deal",$deal['id'],$deal_city);

            if($deal['origin_price']>0&&floatval($deal['discount'])==0) //手动折扣
                $deal['save_money'] = $deal['origin_price'] - $deal['current_price'];
            else
                $deal['save_money'] = $deal['origin_price']*((10-$deal['discount'])/10);

            if($deal['origin_price']>0&&floatval($deal['discount'])==0)
                $deal['discount'] = round(($deal['current_price']/$deal['origin_price'])*10,2);

            $deal['discount'] = round($deal['discount'],2);


            $supplier_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."supplier where id = ".$deal['supplier_id']);
            $supplier_address_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."supplier_location where supplier_id = ".$deal['supplier_id']." and is_main = 1");
            $deal['saler_name'] = $supplier_info['name'];
            $deal['saler_address'] = $supplier_address_info['address'];

            $deal['saler_map'] = "http://ditu.google.cn/maps?f=q&source=s_q&hl=zh-CN&geocode=&q=".$supplier_address_info['api_address'];
            $deal['saler_tel'] = $supplier_address_info['tel'];

            if(app_conf("INVITE_REFERRALS_TYPE")==0)
            {
                $deal['referrals'] = format_price(app_conf("INVITE_REFERRALS"));
            }
            else
            {
                $deal['referrals'] = format_score(app_conf("INVITE_REFERRALS"));
            }

            $deal['referrals_url'] = get_domain().url_pack_4mail("referral",$deal['deal_id'],$deal_city);
            $deal_rs[$k]=$deal;
        }
        $GLOBALS['tmpl']->assign("deal_rs",$deal_rs);
        $content = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);

        $tmpl_path = app_conf("TMPL_DOMAIN_ROOT")==''?get_domain().APP_ROOT."/app/Tpl/":app_conf("TMPL_DOMAIN_ROOT")."/";
        $content = str_replace("deal_mail/",$tmpl_path.app_conf("TEMPLATE")."/deal_mail/",$content);
        return $content;
    }
    else
        return '';
}

/**
 * $notice.site_name
 * $notice.deal_name
 * $notice.site_url
 * @param $deal_id
 */
function get_deal_sms_content($deal_id)
{
    $tmpl_content = $GLOBALS['db']->getOneCached("select content from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_NOTICE_SMS'");
    $deal = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal where id = ".$deal_id);
    if($deal)
    {
        $notice['site_name'] = app_conf("SHOP_TITLE");
        $notice['deal_name'] = $deal['sub_name'];
        $notice['site_url'] = get_domain().APP_ROOT;
        $GLOBALS['tmpl']->assign("notice",$notice);
        $content = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
        return $content;
    }
    else
        return '';
}

/**
 * $bond.sn
 * $bond.password
 * $bond.name
 * $bond.user_name
 * $bond.begin_time_format
 * $bond.end_time_format
 * $bond.tel
 * $bond.address
 * $bond.route
 * $bond.open_time
 * @param $coupon_id
 * @param $location_id
 */
function get_coupon_content($coupon_id,$location_id)
{
    $tmpl_content = app_conf("COUPON_PRINT_TPL");
    $coupon_data = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_coupon where id =".$coupon_id." and user_id = ".intval($GLOBALS['user_info']['id']));
    if(!$coupon_data)
        return '';
    $location_info = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."supplier_location where id=".$location_id);

    $bond['sn'] = $coupon_data['sn'];
    $bond['password'] = $coupon_data['password'];
    $order_item = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_order_item where id = ".intval($coupon_data['order_deal_id']));
    $deal_type = intval($GLOBALS['db']->getOneCached("select deal_type from ".DB_PREFIX."deal where id = ".intval($order_item['deal_id'])));

    $bond['name'] = $order_item['name'];
    $bond['sub_name'] = $order_item['sub_name'];
    if($deal_type == 1)
    {
        $bond['name'].= "&nbsp;&nbsp;".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
        $bond['sub_name'].= "&nbsp;&nbsp;".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
    }
    $bond['user_name'] = $GLOBALS['user_info']['user_name'];
    $bond['begin_time_format'] = to_date($coupon_data['begin_time']);
    $bond['end_time_format'] = to_date($coupon_data['end_time']);
    $bond['tel'] = $location_info['tel'];
    $bond['address'] = $location_info['address'];
    $bond['route'] = $location_info['route'];
    $bond['open_time'] = $location_info['open_time'];

    $GLOBALS['tmpl']->assign("bond",$bond);
    $content = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
    return $content;

}


function gzip_out($content)
{
    header("Content-type: text/html; charset=utf-8");
    header("Cache-control: private");  //支持页面回跳
    $gzip = app_conf("GZIP_ON");
    if( intval($gzip)==1 )
    {
        if(!headers_sent()&&extension_loaded("zlib")&&preg_match("/gzip/i",$_SERVER["HTTP_ACCEPT_ENCODING"]))
        {
            $content = gzencode($content,9);
            header("Content-Encoding: gzip");
            header("Content-Length: ".strlen($content));
            echo $content;
        }
        else
            echo $content;
    }else{
        echo $content;
    }

}

function order_log($log_info,$order_id)
{
    $data['id'] = 0;
    $data['log_info'] = $log_info;
    $data['log_time'] = get_gmtime();
    $data['order_id'] = $order_id;
    $GLOBALS['db']->autoExecute(DB_PREFIX."deal_order_log", $data);
}


/**
 * 保存图片
 * @param array $upd_file  即上传的$_FILES数组
 * @param array $key $_FILES 中的键名 为空则保存 $_FILES 中的所有图片
 * @param string $dir 保存到的目录
 * @param array $whs
可生成多个缩略图
数组 参数1 为宽度，
参数2为高度，
参数3为处理方式:0(缩放,默认)，1(剪裁)，
参数4为是否水印 默认为 0(不生成水印)
array(
'thumb1'=>array(300,300,0,0),
'thumb2'=>array(100,100,0,0),
'origin'=>array(0,0,0,0),  宽与高为0为直接上传
...
)，
 * @param array $is_water 原图是否水印
 * @return array
array(
'key'=>array(
'name'=>图片名称，
'url'=>原图web路径，
'path'=>原图物理路径，
有略图时
'thumb'=>array(
'thumb1'=>array('url'=>web路径,'path'=>物理路径),
'thumb2'=>array('url'=>web路径,'path'=>物理路径),
...
)
)
....
)
 */
//$img = save_image_upload($_FILES,'avatar','temp',array('avatar'=>array(300,300,1,1)),1);
function save_image_upload($upd_file, $key='',$dir='temp', $whs=array(),$is_water=false)
{
    require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
    $image = new es_imagecls();
    $image->max_size = intval(app_conf("MAX_IMAGE_SIZE"));
    $list = array();

    if(empty($key))
    {
        foreach($upd_file as $fkey=>$file)
        {
            $list[$fkey] = false;
            $image->init($file,$dir);
            if($image->save())
            {
                $list[$fkey] = array();
                $list[$fkey]['url'] = $image->file['target'];
                $list[$fkey]['path'] = $image->file['local_target'];
                $list[$fkey]['name'] = $image->file['prefix'];
            }
            else
            {
                if($image->error_code==-105)
                {
                    echo "上传的图片太大";
                }
                elseif($image->error_code==-104||$image->error_code==-103||$image->error_code==-102||$image->error_code==-101)
                {
                    echo "非法图像";
                }
                exit;
            }
        }
    }
    else
    {
        $list[$key] = false;
        $image->init($upd_file[$key],$dir);
        if($image->save())
        {
            $list[$key] = array();
            $list[$key]['url'] = $image->file['target'];
            $list[$key]['path'] = $image->file['local_target'];
            $list[$key]['name'] = $image->file['prefix'];
        }
        else
        {
            if($image->error_code==-105)
            {
                echo "上传的图片太大";
            }
            elseif($image->error_code==-104||$image->error_code==-103||$image->error_code==-102||$image->error_code==-101)
            {
                echo "非法图像";
            }
            exit;
        }
    }

    $water_image = APP_ROOT_PATH.app_conf("WATER_MARK");
    $alpha = app_conf("WATER_ALPHA");
    $place = app_conf("WATER_POSITION");

    foreach($list as $lkey=>$item)
    {
        //循环生成规格图
        foreach($whs as $tkey=>$wh)
        {
            $list[$lkey]['thumb'][$tkey]['url'] = false;
            $list[$lkey]['thumb'][$tkey]['path'] = false;
            if($wh[0] > 0 || $wh[1] > 0)  //有宽高度
            {
                $thumb_type = isset($wh[2]) ? intval($wh[2]) : 0;  //剪裁还是缩放， 0缩放 1剪裁
                if($thumb = $image->thumb($item['path'],$wh[0],$wh[1],$thumb_type))
                {
                    $list[$lkey]['thumb'][$tkey]['url'] = $thumb['url'];
                    $list[$lkey]['thumb'][$tkey]['path'] = $thumb['path'];
                    if(isset($wh[3]) && intval($wh[3]) > 0)//需要水印
                        $image->water($list[$lkey]['thumb'][$tkey]['path'],$water_image,$alpha, $place);
                }
            }
        }
        if($is_water)
        {
            $image->water($item['path'],$water_image,$alpha, $place);
        }
    }
    return $list;
}
?>