<?php
/*
 * IndexAction.class.php
 * cnxzcxy <cnxzcxy@gmail.com>
 * Tue Nov 08 07:44:29 GMT 2011 
 */
class HelpAction extends CommonAction 
{
    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function index(){
      $this->assign('current_page', 'Help');
      $this->display();      
    }
    
    public function changeLog(){
      $this->display();
    }
    
    public function HowToGetKml(){
      
    }
    
    /*
    *HelpAction report 生成用户报告页面
    *@param 
    *@return 
    */
    public function report(){
      $this->assign('current_page', 'Help/report');
      $this->display();
    }
    
    public function test(){
      $a = array();
      $a['test'] = '1';
      dump($a);
      unset($a['test']);
      dump($a);
    }


}
?>