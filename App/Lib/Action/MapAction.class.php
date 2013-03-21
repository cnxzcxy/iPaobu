<?php
/*
 * MapAction.class.php
 * cnxzcxy <cnxzcxy@gmail.com>
 * Tue Nov 08 07:44:24 GMT 2011 
 */
class MapAction extends CommonAction{
  public function index(){
    $this->display();    
  }
  
  public function view(){
    $log = $_GET['id'];
    if (empty($log)) {
    	echo "<script>alert('...');history.back();</script>";
    }
    $log = base64_decode($log);
    $fileModel = D();
    $sql = "select f.id as fid, f.createtime, u.id, u.username from ipb_file as f ";
    $sql .= " join ipb_user as u on f.userid = u.id where f.id=".$log;
    $result = $fileModel->query($sql);
    if ($log < 10000) {
      $sep = '0';    	
    }else{
      $sep = substr($log, 0, 2);
    }
    $filen = 'offset/'.$sep.'/'.$log;
    if (IS_SAE) {
    	$s = new SaeStorage();
    	if ($s->fileExists(C('STORAGE_DOMAIN'), $filen)) {
    		$arr = $s->read(C('STORAGE_DOMAIN'), $filen);
    		$arr = unserialize($arr);
    	}else{
    	  $arr = $this->getGPS($log);
    	  $tmp_arr = serialize($arr);
    	  $s->write(C('STORAGE_DOMAIN'), $filen, $tmp_arr);
    	  unset($tmp_arr);
    	}
    }else{
      $arr = $this->getGPS($log);
    }
    $name = $arr['name'];
    unset($arr['name']);
    /*
    todo: 记录开始点、结束点，并计算距离，每隔1km，用点做标记，这些操作可以放在getGPS函数里面进行处理，以方便写入storage
    */
    if ($arr != 0) {
      $this->assign('uploader', $result);
      $this->assign('name', $name);
    	$this->assign('arr', $arr);
    	$this->assign('arr_json', json_encode($arr));
    	$this->display();
    }else{
      echo "读取过程中出错了";
    }

  }
  
  private function getGPS($log){
    //$log 为kml 文件id ，从数据库存取
    $fileModel = D("File");
    $path = $fileModel->where('id = '.$log)->select();
    $file = $path[0]['path'];
    $name = $path[0]['name'];
    $type = $path[0]['type'];
    $domain = 'ipaobu';
    if (IS_SAE) {
    	$stor = new SaeStorage();
    	$content = $stor->read($domain, $file);
    	$xml = simplexml_load_string($content);
        if($type == '.kml'){
      		$ns = $xml->getDocNamespaces();
      	if(isset($ns[""])){
       		$xml->registerXPathNamespace("default",$ns[""]);
      	}
      	$xml= $xml->xpath('//default:Placemark');
      	$pois = (array)$xml;
      	$pois = (array)$pois[0];
      	$name = $pois['name'];
      	$pois = (array)$pois['MultiGeometry'];
      	$pois = (array)$pois['LineString'];
      	$pois = $pois['coordinates'];
      	preg_match_all('/(\d+).(\d+),(\d+).(\d+)/', $pois, $value);
      	$arr = Array();
      	foreach ($value[0] as $key=>$val){
      		$val = explode(',', $val);
      		$coors = $val[0].','.$val[1];
      		$coors = explode(",", $coors);
      		$key = MAPABC_KEY;
      		$url = "http://search1.mapabc.com/sisserver?config=RGC&resType=xml&x1=".$coors[0]."&y1=".$coors[1]."&cr=0&a_k=".$key;
  //    		$content = file_get_contents($url);
      		$f = new SaeFetchurl();
      		$content = $f->fetch($url);
      		$content = get_object_vars(simplexml_load_string($content)); 
      		$content = get_object_vars($content['Item']);
      		$content['o_x'] = $coors[0];
        	$content['o_y'] = $coors[1];
      		$arr[] = $content;
      	}
      }elseif ($type == ".gpx"){
        $ns = $xml->getDocNamespaces();
         if(isset($ns[""])){
         	 $xml->registerXPathNamespace("default",$ns[""]);
         }
	       $xml= $xml->xpath('//default:trkpt');
	       $pois = (array)$xml;
	       foreach ($pois as $key=>$value){
	         $value = (array)$value;
	         $coors[0] = $value['@attributes']['lon'];
	         $coors[1] = $value['@attributes']['lat'];
	         $ele = $value['ele'];
	         $time = $value['time'];
	         $key = MAPABC_KEY;
        		$url = "http://search1.mapabc.com/sisserver?config=RGC&resType=xml&x1=".$coors[0]."&y1=".$coors[1]."&cr=0&a_k=".$key;
//        		$content = file_get_contents($url);
        		$f = new SaeFetchurl();
        		$content = $f->fetch($url);
        		$content = get_object_vars(simplexml_load_string($content)); 
        		$content = get_object_vars($content['Item']);
        		$content['o_x'] = $coors[0];
        		$content['o_y'] = $coors[1];
        		$content['ele'] = $ele;
        		$content['time'] = $time;
  	        $arr[] = $content;
	       }
      }
    	$arr['name'] = $name;
    	return $arr;
    	
    }else{
      if (file_exists($file)) {
      	$xml = simplexml_load_file($file);
          if($type == '.kml'){
          	$ns = $xml->getDocNamespaces();
          	if(isset($ns[""])){
           		$xml->registerXPathNamespace("default",$ns[""]);
          	}
        	
          	$xml= $xml->xpath('//default:Placemark');
          	$pois = (array)$xml;
          	$pois = (array)$pois[0];
          	$name = $pois['name'];
          	$pois = (array)$pois['MultiGeometry'];
          	$pois = (array)$pois['LineString'];
          	$pois = $pois['coordinates'];
          	preg_match_all('/(\d+).(\d+),(\d+).(\d+)/', $pois, $value);
          	$arr = Array();
          	foreach ($value[0] as $key=>$val){
          		$val = explode(',', $val);
          		$coors = $val[0].','.$val[1];
          		$coors = explode(",", $coors);
          		$key = MAPABC_KEY;
          		$url = "http://search1.mapabc.com/sisserver?config=RGC&resType=xml&x1=".$coors[0]."&y1=".$coors[1]."&cr=0&a_k=".$key;
          		$content = file_get_contents($url);
    //      		$f = new SaeFetchurl();
    //      		$content = $f->fetch($url);
          		$content = get_object_vars(simplexml_load_string($content)); 
          		$content = get_object_vars($content['Item']);
          		$content['o_x'] = $coors[0];
        		  $content['o_y'] = $coors[1];
          		$arr[] = $content;
          	}
    	     }elseif ($type == '.gpx'){
    	       $ns = $xml->getDocNamespaces();
             if(isset($ns[""])){
             	 $xml->registerXPathNamespace("default",$ns[""]);
             }
    	       $xml= $xml->xpath('//default:trkpt');
    	       $pois = (array)$xml;
    	       foreach ($pois as $key=>$value){
    	         $value = (array)$value;
    	         $coors[0] = $value['@attributes']['lon'];
    	         $coors[1] = $value['@attributes']['lat'];
    	         $ele = $value['ele'];
    	         $time = $value['time'];
    	         $key = MAPABC_KEY;
            		$url = "http://search1.mapabc.com/sisserver?config=RGC&resType=xml&x1=".$coors[0]."&y1=".$coors[1]."&cr=0&a_k=".$key;
            		$content = file_get_contents($url);
      //      		$f = new SaeFetchurl();
      //      		$content = $f->fetch($url);
            		$content = get_object_vars(simplexml_load_string($content)); 
            		$content = get_object_vars($content['Item']);
            		$content['o_x'] = $coors[0];
        		    $content['o_y'] = $coors[1];
            		$content['ele'] = $ele;
            		$content['time'] = $time;
      	        $arr[] = $content;
    	       }
    	     }
      	$arr['name'] = $name;
      	return $arr;
      } else {
          return 0;
      }
    }
  }
}