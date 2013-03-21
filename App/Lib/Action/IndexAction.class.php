<?php
/*
 * IndexAction.class.php
 * cnxzcxy <cnxzcxy@gmail.com>
 * Tue Nov 08 07:44:29 GMT 2011 
 */
class IndexAction extends CommonAction 
{
    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function index(){
      $fileModel = D();
      $sql = "select f.id as fid, f.path, f.userid, f.createtime, u.id as uid, u.username from ";
      $sql .= " ipb_file as f join ipb_user as u on f.userid = u.id order by f.createtime desc limit 10";
      $result = $fileModel->query($sql);
      $this->assign('data', $result);
      $this->display();
      
    }
    
    public function test(){
      $a = array();
      $a['test'] = '1';
      dump($a);
      unset($a['test']);
      dump($a);
    }

    /**
    +----------------------------------------------------------
    * 探针模式
    +----------------------------------------------------------
    */
    public function checkEnv()
    {
        load('pointer',THINK_PATH.'/Tpl/Autoindex');//载入探针函数
        $env_table = check_env();//根据当前函数获取当前环境
        echo $env_table;
    }

}
?>