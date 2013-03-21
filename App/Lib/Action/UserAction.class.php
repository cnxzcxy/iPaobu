<?php
/*
 * UserAction.class.php
 * cnxzcxy <cnxzcxy@gmail.com>
 * Tue Nov 08 08:15:58 GMT 2011 
 */
class UserAction extends CommonAction{
  /*
  *UserAction index
  *$@param 
  *$@return 
  */
  public function index(){
    if ($this->isLogin()) {
      $fileModel = D('File');
      $result = $fileModel->where('userid = '.$this->cgetcookie('ipb_ui'))->select();
      $userModel = D('User');
      $profile = $userModel->where('id = '.$this->cgetcookie('ipb_ui'))->select();
      $this->assign('profile', $profile);
      $this->assign('upload', $result);
      $this->assign('username', $this->cgetcookie('ipb_un'));
      $this->display();
    }else{
      echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>"; 
      echo "<script>alert('请先登录');history.back();</script>";
    }
    
    
  }
  
  /*
  *UserAction register
  *$@param 
  *$@return 
  */
  public function register(){
    if ($this->isLogin()) {
      echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>"; 
    	echo "<script>alert('您已经登录了，如果要注册新的帐号，请先退出');history.back();</script>";
    }else{
      $sinaWeibo = $this->sinaWeibo();
      $renren = $this->renren();
      $this->assign('renren', $renren);
      $this->assign('sinaWeibo', $sinaWeibo);
      $this->display();
    }
  }
  
  /*
  *UserAction doRegister 注册信息处理
  *$@param $_POST
  *$@return
  */
  public function doRegister(){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    if (empty($username) or empty($password) or empty($email)) {
    	exit("2");
    }
    $userModel = D("User");
    $result = $userModel->where("username = '".$username."' or email = '".$email."'")->count();
    if ($result > 0) {
    	echo "0";
    }else {
      $data['username'] = $username;
      $data['password'] = md5($password);
      $data['email'] = $email;
      $data['createtime'] = time();
      $rs = $userModel->data($data)->add();
      if ($rs > 0) {
        $this->csetcookie('ipb_ui', $rs, time()+3600000);
        $this->csetcookie('ipb_un', $username, time()+3600000);
      	echo "1";
      }else{
        echo "2";
      }
    }
    
  }
  
  /*
  *UserAction login 判断用户是否已经登录，并显示页面
  *@param 
  *@return 
  */
  public function login(){
    if ($this->isLogin()) {
      echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>"; 
    	echo "<script>alert('您已经登录了，如果要切换别的帐号，请先退出');history.back();</script>";
    }else{
      $sinaWeibo = $this->sinaWeibo();
      $renren = $this->renren();
      $this->assign('renren', $renren);
      $this->assign('sinaWeibo', $sinaWeibo);
      $this->display();
    }
  }
  
  /*
  *UserAction doLogin 登录数据处理
  *@param 
  *@return 
  */
  public function doLogin(){
    $email = $_POST['email'];
    $password = $_POST['password'];
    if (empty($email) or empty($password)) {
    	exit("2");
    }
    $userModel = D("User");
    $result = $userModel->field('id, username')->where("email = '".$email."' and password = '".md5($password)."'")->select();
    if ($result[0]['id'] > 0) {
      $this->csetcookie('ipb_ui', $result[0]['id'], time()+3600000);
      $this->csetcookie('ipb_un', $result[0]['username'], time()+3600000);      
      echo "1";    	
    }else{
      echo "2";
    }
  
  }
  
  /*
  *UserAction sinaWeibo 生成新浪微博登录链接及显示文字
  *@param 
  *@return string 新浪微博登陆链接
  */
  public function sinaWeibo(){
	  session_start();
	  import("@.ORG.Weibooauth");
	  $WB_AKEY = C('WB_AKEY');
	  $WB_SKEY = C('WB_SKEY');
	  $o = new WeiboOAuth( $WB_AKEY , $WB_SKEY  );
	  $keys = $o->getRequestToken();
	  $callbackAddr = C('SITE_URL').'User/sinaWeiboCallback';
	  $aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callbackAddr);
	  $_SESSION['keys'] = $keys;
	  return "<a href='".$aurl."'>新浪微博帐号登录</a>";
	}
	
	/*
	*UserAction sinaWeiboCallback 用户通过新浪微博帐号登录成功后数据进行处理
	*@param 
	*@return 
	*/
	public function sinaWeiboCallback(){
	  session_start();
	  import("@.ORG.Weibooauth");
	  $WB_AKEY = C('WB_AKEY');
	  $WB_SKEY = C('WB_SKEY');
	  $o = new WeiboOAuth( $WB_AKEY , $WB_SKEY , $_SESSION['keys']['oauth_token'] , $_SESSION['keys']['oauth_token_secret']  );
	  $last_key = $o->getAccessToken(  $_REQUEST['oauth_verifier'] ) ;
	  $_SESSION['last_key'] = $last_key;
	  $c = new WeiboClient( $WB_AKEY , $WB_SKEY , $_SESSION['last_key']['oauth_token'] , $_SESSION['last_key']['oauth_token_secret']  );
//    $ms  = $c->home_timeline(); // 获取sina weibo timeline
    $me = $c->verify_credentials(); //获取sina weibo account 
//    dump($me);	  
    $username = $me['name'];
    $sinaweiboid = $me['id'];
    $userModel = D('User');
    $result = $userModel->where("sinaweiboid = '".$sinaweiboid."'")->select();
    if ($result[0]['id'] > 0) {
    	$this->csetcookie('ipb_ui', $result[0]['id'], time()+3600000);
      $this->csetcookie('ipb_un', $result[0]['username'], time()+3600000);
      echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>"; 
      echo "<script>alert('直接登录成功，跳转至首页');window.location.href='".__APP__."/Index';</script>";
    }else{
      $data['username'] = $username;
      $data['sinaweiboid'] = $sinaweiboid;
      $data['createtime'] =time();
      $rs = $userModel->data($data)->add();
      if ($rs > 0) {
        $this->csetcookie('ipb_ui', $rs, time()+3600000);
        $this->csetcookie('ipb_un', $username, time()+3600000);
        echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>"; 
      	echo "<script>alert('您是第一次登录本站，登录成功，跳转至首页');window.location.href='".__APP__."';</script>";
      }else{
        echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>"; 
        echo "<script>alert('抱歉，登录失败，请重新登录');window.location.href='".__APP__."/User/login';</script>";
      }
    }
    
	}
	
	/*
	*UserAction renren 生成人人网登陆链接及显示文字
	*@param 
	*@return string
	*/
	public function renren(){
	  $api_key = C('RENREN_API_KEY');
	  $callbackAddr = C('SITE_URL').'User/renrenCallback';
	  $url = "https://graph.renren.com/oauth/authorize?client_id=".$api_key."&response_type=code&redirect_uri=".$callbackAddr;
	  return "<a href='".$url."'>人人网帐号登录</a>";
	}
	
	/*
	*UserAction renrenCallback 用户通过人人网帐号登录后回调函数
	*@param 
	*@return 
	*/
	public function renrenCallback(){
	  $code = $_GET['code'];
	  $api_key = C('RENREN_API_KEY');
	  $api_skey = C('RENREN_API_SKEY');
	  $callbackAddr = C('SITE_URL').'User/renrenCallback';
	  $url = "https://graph.renren.com/oauth/token?client_id=".$api_key."&client_secret=".$api_skey."&redirect_uri=".$callbackAddr."&grant_type=authorization_code&code=".$code;
	  $content = file_get_contents($url);
	  $content = json_decode($content);
	  dump($content);
//	  header('Location: '.$url);
//	  echo $code;
	}
  
  /*
  *UserAction logout 退出
  *$@param 
  *$@return 
  */
  public function logout(){
    if ($this->isLogin()) {
    	setcookie('ipb_ui', '', time() -3600, '/');
    	setcookie('ipb_un', '', time() -3600, '/');
    	echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>"; 
    	echo "<script>alert('退出成功，跳转至首页');window.location.href='".__APP__."/Index';</script>";
    }else{
      echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>"; 
      echo "<script>alert('您还没有登录呢，着急什么');history.back();</script>";
    }
  }
  
  /*
  *UserAction upload 上传页面
  *@param 
  *@return 
  */
  public function upload(){
    if ($this->isLogin()) {
    	$this->display();
    }else{
      echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>"; 
      echo "<script>alert('请先登录');history.back();</script>";
    }
    
  }
  
  /*
  *UserAction doUpload 上传数据处理，分为SAE和非SAE主机两种
  *@param 
  *@return 
  */
  public function doUpload(){
    $inName = $_POST['name'];
    $inType = $_POST['gps_file_type'];
    $name = microtime();
		$name = md5($name).rand(100,200);
		$path = "data";
		$domain = C('STORAGE_DOMAIN');
		if (isset($_FILES["gps_file"]) && is_uploaded_file($_FILES["gps_file"]["tmp_name"]) && $_FILES["gps_file"]["error"] == 0){
		  if (IS_SAE) {
        $upload_path = SAE_TMP_PATH.$name;
        move_uploaded_file($_FILES['gps_file']["tmp_name"],$upload_path);
        $content = file_get_contents($upload_path);
        $temp = new SaeStorage();
        $filename = $path."/".$name;
        $temp->write($domain,$filename,$content);//写入文件
        $fileModel = D("File");
        $data['name'] = $inName;
        $data['type'] = $inType;
    		$data['path'] = $filename;
    		$data['userid'] = $this->cgetcookie('ipb_ui');
    		$data['createtime'] = time();
    		$rs = $fileModel->data($data)->add();
    		if ($rs > 0) {
    		  $rs = base64_encode($rs);
    		  echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>"; 
    			echo "<script>alert('上传成功，即将跳转至显示页面，第一次打开速度会比较慢');window.location.href='".__APP__."/Map/view/id/".$rs."'</script>";
    		}else{
    		  echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>"; 
    		  echo "<script>alert('很抱歉，上传出错了，请重新上传');history.back();</script>";
    		}
    	
      }else {
        $filepath = 'data/'.date('Y').date('m')."/".date('d')."/".date('H')."/";
    		if(!is_dir($filepath)){
    			mkdir($filepath,0777,true);
    		}
    		$save_path = $filepath.$name;
    		move_uploaded_file($_FILES["gps_file"]["tmp_name"], $save_path);
    		$fileModel = D("File");
    		$data['name'] = $inName;
        $data['type'] = $inType;
    		$data['path'] = $save_path;
    		$data['userid'] = $this->cgetcookie('ipb_ui');
    		$data['createtime'] = time();
    		$rs = $fileModel->data($data)->add();
    		if ($rs > 0) {
    		  $rs = base64_encode($rs);
    		  echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>"; 
    			echo "<script>alert('上传成功，即将跳转至显示页面，第一次打开速度会比较慢');window.location.href='".__APP__."/Map/view/id/".$rs."'</script>";
    		}else{
    		  echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>"; 
    		  echo "<script>alert('很抱歉，上传出错了，请重新上传');history.back();</script>";
    		}
    		
      }
		}
    
    
  }

}