<?php
class es_cookie 
{
    // 判断Cookie是否存在
    static function is_set($name) {
        return isset($_COOKIE[$name]);
    }

    // 获取某个Cookie值
    static function get($name) {
        $value   = $_COOKIE[$name];
        //$value   =  unserialize(base64_decode($value));
        return $value;
    }

    // 设置某个Cookie值
    static function set($name,$value,$expire='',$path='',$domain='') {   
    	$path = APP_ROOT."/";    
        $expire =   !empty($expire)?    get_gmtime()+$expire   :  0;
        //$value   =  base64_encode(serialize($value));
        setcookie($name, $value,$expire,$path,$domain);
        $_COOKIE[$name]  =   $value;
    }

    // 删除某个Cookie值
    static function delete($name) {
        es_cookie::set($name,'',time()-3600);
        unset($_COOKIE[$name]);
    }

    // 清空Cookie值
    static function clear() {
        unset($_COOKIE);
    }
}
?>