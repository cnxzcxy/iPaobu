<?php
/*
 * CommonAction.class.php
 * cnxzcxy <cnxzcxy@gmail.com>
 * Tue Nov 08 07:40:13 GMT 2011
 */

class CommonAction extends Action {
  
  /*
  *CommonAction initialize 初始化
  *$@param 
  *$@return 
  */
  public function _initialize(){
    define('KEY', '*');
    define('MAPABC_KEY', '*');
    $this->assign('current_module', MODULE_NAME);
    $this->assign('current_action', ACTION_NAME);
    if ($this->isLogin()) {
    	$this->assign('username', $this->cgetcookie('ipb_un'));
    }else{
      $this->assign('username', 0);
    }
        
  }
  /*
  *CommonAction isLogin 判断用户是否登录
  *$@param 
  *$@return bool(true, false)
  */
  public function isLogin(){
    $username = $this->cgetcookie('ipb_un');
    $userid = $this->cgetcookie('ipb_ui');
    if (!empty($userid) && !empty($username)) {
      return true;    	
    }else {
      return false;
    }    
  }
  
  /*
  *CommonAction thinkphp 默认空function
  *$@param 
  *$@return string
  */
  public function _empty(){
    echo "<script>alert('@#$%%^&*-+');history.back();</script>";
  }
  
  /*
  *CommonAction csetcookie 自定义设置cookie函数，集成authcode对name, value加密
  *$@param string name, string value
  *$@return bool(true, false)
  */
  public function csetcookie($name, $value, $expires){
    $value = $this->authcode($value, 'ENCODE');
    setcookie($name, $value, $expires, '/');
  }
  
  /*
  *CommonAction cgetcookie 自定义获取cookie函数，集成authcode解密
  *$@param string name
  *$@return string value
  */
  public function cgetcookie($name){
    $value = $this->authcode($_COOKIE[$name]);
    return $value;    
  }
  
  /*
  *CommonAction authcode 取自discuz加密函数
  *$@param string
  *$@return string
  */
  public function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
		$ckey_length = 4;     
  	$key = md5($key ? $key : KEY);     
  	$keya = md5(substr($key, 0, 16));     
  	$keyb = md5(substr($key, 16, 16));    
  	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length):  substr(md5(microtime()), -$ckey_length)) : '';    
  	$cryptkey = $keya.md5($keya.$keyc);
  	$key_length = strlen($cryptkey);    
  	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :  
  	sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;     
  	$string_length = strlen($string);
  	$result = '';
    $box = range(0, 255);
    $rndkey = array();     
    for($i = 0; $i <= 255; $i++) {
         $rndkey[$i] = ord($cryptkey[$i % $key_length]);     
     }    
    for($j = $i = 0; $i < 256; $i++) {     
         $j = ($j + $box[$i] + $rndkey[$i]) % 256;     
        $tmp = $box[$i];     
         $box[$i] = $box[$j];     
         $box[$j] = $tmp;     
     }     
     // 核心加解密部分     
     for($a = $j = $i = 0; $i < $string_length; $i++) {     
         $a = ($a + 1) % 256;     
         $j = ($j + $box[$a]) % 256;     
         $tmp = $box[$a];     
         $box[$a] = $box[$j];     
        $box[$j] = $tmp;        
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));     
     }     
     if($operation == 'DECODE') {         
    if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) 
    		&& substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {             return substr($result, 26);     
      } else {     
             return '';     
        }     
     } else {         
        return $keyc.str_replace('=', '', base64_encode($result));
     }
  } 
}