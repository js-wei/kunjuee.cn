<?php
	/**
	 * 打印函数
	 * @param array $array
	 */
	function p($array){
		dump($array,1,'<pre>',0);	
	}
	// cuplayer.com去除转义字符 
	function stripslashes_array(&$array) { 
	    while(list($key,$var) = each($array)) { 
	        if ($key != 'argc' && $key != 'argv' && (strtoupper($key) != $key || ''.intval($key) == "$key")) { 
	            if (is_string($var)) { 
	                $array[$key] = stripslashes($var); 
	            } 
	            if (is_array($var))  { 
	                $array[$key] = stripslashes_array($var); 
	            } 
	        } 
	    } 
	    return $array; 
	} 
	/**
	 * [split_content 拆分内容]
	 * @param  string $content [内容]
	 * @return array           [description]
	 */
	function split_content($content,$separator="，,。"){
		$separator = explode(',', $separator);
		$result =  array();
		$content = htmlspecialchars_decode($content);
		$str = tagstr(trim_all($content));
		$str=str_replace('，',' ',str_replace('。',' ',$str));
		$result = explode(' ',$str);
		$start_index =0; 
		//return array_filter($result);
		for ($i=0; $i < count($result)-1; $i++){ 

			if($start_index%2==0){
				$_result .= strip_tags($result[$i]).'，';
				$start_index = 1;
			}else{
				$_result .= strip_tags($result[$i]).'。';
				$start_index = 0;
			}
			
		}
		return $_result;
	}
	/**
	 * csv_get_lines 读取CSV文件中的某几行数据
	 * @param $csvfile csv文件路径
	 * @param $lines 读取行数
	 * @param $offset 起始行数
	 * @return array
	 * */
	function csv_get_lines($csvfile, $lines, $offset = 0) {
	    if(!$fp = fopen($csvfile, 'r')) {
	    	return false;
	    }
	    $i = $j = 0;
		while (false !== ($line = fgets($fp))) {
			if($i++ < $offset) {
				continue; 
			}
			break;
		}
		$data = array();
		while(($j++ < $lines) && !feof($fp)) {
			$data[] = fgetcsv($fp);
		}
		fclose($fp);
	    return $data;
	}
	/**
	 * [getDatabaseSize description]
	 * @param [type] $database [description]
	 * @param [type] $db       [description]
	 */
	function getDatabaseSize($database) {
	  $handle = mysql_connect(C('DB_HOST'), C('DB_USER'), C('DB_PWD')); 
	  $tables = mysql_list_tables($database, $handle);
	  if (!$tables) { return -1; }
	  $table_count = mysql_num_rows($tables);
	  $size = 0;
	  for ($i=0; $i < $table_count; $i++) {
	    $tname = mysql_tablename($tables, $i);
	    $r = mysql_query("SHOW TABLE STATUS FROM ".$database." LIKE '".$tname."'");
	    $data = mysql_fetch_array($r);
	    $size += ($data['Index_length'] + $data['Data_length']);
	  };
	  $units = array(' B', ' KB', ' MB', ' GB', ' TB');
	  for ($i = 0; $size > 1024; $i++) { $size /= 1024; }
	  // close connection:
	  mysql_close($handle);
	  return round($size, 2).$units[$i];
		
	}
	/**
	 * [get_next_split_content 拆分诗词]
	 * @param  [type] $content   [诗词]
	 * @param  [type] $query     [查询词]
	 * @param  string $separator [分隔符]
	 * @return [type]            [description]
	 */
	function get_next_split_content($content,$query,$separator="，,。"){
		
		$separator = explode(',', $separator);
		//p($separator);die;
		$result =  array();
		$content = htmlspecialchars_decode($content);
		$str = tagstr(trim_all($content));
		$str=str_replace('，',' ',str_replace('。',' ',$str));
		$result = explode(' ',$str); 
		$result = $temp = array_filter($result);
		$flag = 0;
		$_result ='';
		for($i=0;$i<count($result);$i++){
			if(strpos($result[$i],$query)!==false){
				$flag = $i;
				break;
			}
		}
		$result = array_splice($result,$flag);
		//开始下标
		$start_index = count($temp) - count($result);
	
		for ($i=0; $i < count($result); $i++){ 

			if($start_index%2==0){
				$_result .= strip_tags($result[$i]).$separator[0];
				$start_index = 1;
			}else{
				$_result .= strip_tags($result[$i]).$separator[1];
				$start_index = 0;
			}
			
		}
		
		return $_result;
	}
	/**
	 * [filter_mark 去除标点符号]
	 * @param  [type] $text [description]
	 * @return [type]       [description]
	 */
	function filter_mark($text){
		if(trim($text)=='') return '';
		$text=preg_replace("/[[:punct:]\s]/",' ',$text);
		$text=urlencode($text);
		$text=preg_replace("/(%7E|%60|%21|%40|%23|%24|%25|%5E|%26|%27|%2A|%28|%29|%2B|%7C|%5C|%3D|\-|_|%5B|%5D|%7D|%7B|%3B|%22|%3A|%3F|%3E|%3C|%2C|\.|%2F|%A3%BF|%A1%B7|%A1%B6|%A1%A2|%A1%A3|%A3%AC|%7D|%A1%B0|%A3%BA|%A3%BB|%A1%AE|%A1%AF|%A1%B1|%A3%FC|%A3%BD|%A1%AA|%A3%A9|%A3%A8|%A1%AD|%A3%A4|%A1%A4|%A3%A1|%E3%80%82|%EF%BC%81|%EF%BC%8C|%EF%BC%9B|%EF%BC%9F|%EF%BC%9A|%E3%80%81|%E2%80%A6%E2%80%A6|%E2%80%9D|%E2%80%9C|%E2%80%98|%E2%80%99|%EF%BD%9E|%EF%BC%8E|%EF%BC%88)+/",' ',$text);
		$text=urldecode($text);
		return trim($text);
	} 
	/**
	 * [get_column 获取栏目名]
	 * @param  [type] $column_id [栏目ID]
	 * @return [type]            [description]
	 */
	function get_column($column_id){
		$column = M('column')->find($column_id);
		return $column['title'];
	}
	/**
	 * 去除空格
	 * @param $str          字符串
	 * @return string       结果
	 */
	function trim_all($str){
	    $q=array(" ","　","\t","\n","\r");
	    $h=array("","","","","");
	    return str_replace($q,$h,$str);
	}
	/**
	 * [getMacAddr 生成MAC]
	 */
	function getMacAddr(){
	    $return_array = array();
	    $temp_array = array();
	    $mac_addr = "";
	    
	    @exec("arp -a",$return_array);
	    
	    foreach($return_array as $value)
	    {
	        if(strpos($value,$_SERVER["REMOTE_ADDR"]) !== false &&
	        preg_match("/(:?[0-9a-f]{2}[:-]){5}[0-9a-f]{2}/i",$value,$temp_array))
	        {
	            $mac_addr = $temp_array[0];
	            break;
	        }
	    }
	    
	    return ($mac_addr);
	}

	/**
	 * [build_order_no 生成订单号]
	 * @return [type] [description]
	 */
	function build_order_no(){
        return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }


    function save(){
    	//生成图片 
		$imgDir = 'uploadImg/'; 
		$filename="nissangcj".$mobile.".mp3";///要生成的图片名字 
		  
		$xmlstr = $GLOBALS[HTTP_RAW_POST_DATA]; 
		if(empty($xmlstr)) { 
		  $xmlstr = file_get_contents('php://input'); 
		} 
		   
		$jpg = $xmlstr;//得到post过来的二进制原始数据 
		if(empty($jpg)){ 
		  echo 'nostream'; 
		  exit(); 
		} 
		  
		$file = fopen("./".$imgDir.$filename,"w");//打开文件准备写入 
		fwrite($file,$jpg);//写入 
		fclose($file);//关闭 
		  
		$filePath = './'.$imgDir.$filename; 
		  
		//图片是否存在 
		if(!file_exists($filePath)){ 
		  echo 'createFail'; 
		  exit(); 
		}
    }



	function logger($res,$format=true){
		$path ='./Data/'.date('Y-m-d',time());
		if(!file_exists($path)){
			//mkdir($path,0777,true);
			mkdir($path);
			chmod($path,0777);
			//chmod
		}
		file_put_contents($path.'/'.date('Ymdhis',time()).".txt",$format?json_encode($res):$res);
	}

	/**
	 * 创建二维码
	 * @param $data                         数据源
	 * @param string $path                  保存地址
	 * @param string $filename              保存名称
	 * @param string $logo                  logo地址
	 * @param bool $save_and_print          输出且保存
	 * @param int $size                     点的大小：1到10
	 * @param string $level                 容错等级:L水平7%的字码可被修正,M水平15%的字码可被修正,Q水平25%的字码可被修正,H水平30%的字码可被修正Size表示图片每个黑点的像素。
	 * @param int $padding                  补白大小
	 */
	function Qrcode($data,$logo='',$path='',$filename='',$save_and_print=false,$type=false,$output='json',$size=4,$level='L',$padding=1){

	    vendor('phpqrcode.phpqrcode');
	    
	    if(!is_dir($path)){
	        mkdir($path);
	    }
	    if(empty($logo) || $logo==false){
	        if(empty($filename) || $filename==false){
				$full_name='./Data/QRcode/temp.png';
	            ob_end_clean();
	            \QRcode::png($data,$full_name,$level,$size,$padding,$save_and_print);
				if($type){
                	imagepng($QrCode,$full_name);
                	$base64_image_content = "data:png;base64," . chunk_split(base64_encode(file_get_contents($full_name)));
                	echo $base64_image_content;
                	unlink($full_name);
                }else{
                	\QRcode::png($data,false,$level,$size,$padding,$save_and_print);
                }

	        }else{
	            $full_name=$path.$filename;
	            if($save_and_print){
	            	if($type){
	                	imagepng($QrCode,$full_name);
	                	$base64_image_content = "data:png;base64," . chunk_split(base64_encode(file_get_contents($full_name)));
	                	exit($base64_image_content);
	                }else{
	                	\QRcode::png($data,$full_name,$level,$size,$padding,$save_and_print);
	                }
	                
	            }else{
	            	if($type){
	                	imagepng($QrCode,$full_name);
	                	$base64_image_content = "data:png;base64," . chunk_split(base64_encode(file_get_contents($full_name)));
	                	echo $base64_image_content;
	                	unlink($full_name);
	                }else{
	                	\QRcode::png($data,false,$level,$size,$padding,$save_and_print);
	                	unlink($full_name);
	                }

	               
	            }

	        }
	    }else{
	        if($save_and_print){
	            if((empty($filename) || $filename==false)){
	                switch($output){
	                    case 'json':
	                        header("Content-type:text/html;charset=utf-8");
	                        exit(json_encode(array( 'status'=>0, 'msg'=>'param invalid,please enter parameters:path or filename.')));
	                        break;
	                    case 'xml':
	                        header("Content-type:text/xml;charset=utf-8");
	                        exit("<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<Message>\n\t<status>0</status>\n\t<msg>param invalid,please enter parameters:path or filename.</msg>\n</Message>");
	                        break;
	                    case 'string':
	                    default:
	                        header("Content-type:text/html;charset=utf-8");
	                        exit('param invalid,please enter parameters:path or filename.');
	                }
	            }else{

	                if(strpos($filename,".")===false){
	                    $filename = $filename.'.png';
	                }

	                $full_name=$path.$filename;
	                ob_end_clean();
	                \QRcode::png($data,$full_name,$level,$size,$padding,$save_and_print);

	                $QrCode = imagecreatefromstring(file_get_contents($full_name));
	                $logo = imagecreatefromstring(file_get_contents($logo));

	                $QrCode_width=imagesx($QrCode);
	                $logo_width=imagesx($logo);
	                $logo_height=imagesy($logo);
	                $logo_QrCode_width=$QrCode_width/7;
	                $scale = $logo_width/$logo_QrCode_width;
	                $logo_QrCode_height=$logo_height/$scale;
	                $form_width=($QrCode_width-$logo_QrCode_width)/2;

	                imagecopyresampled($QrCode,$logo,$form_width,$form_width,0,0,$logo_QrCode_width,$logo_QrCode_height,$logo_width,$logo_height);

	                if(!empty($path)){
	                    if($save_and_print){
	                        if($type){
	                        	imagepng($QrCode,$full_name);
	                        	$base64_image_content = "data:png;base64," . chunk_split(base64_encode(file_get_contents($full_name)));
			                	exit($base64_image_content);
			                }else{
			                	imagepng($QrCode,$full_name);
				                header('Content-type:image/png');
				                imagepng($QrCode);
			                }
	                    }else{
	                        imagepng($QrCode,$full_name);
	                    }
	                }
	            }
	        }else{
	            $full_name='./Data/QRcode/temp.png';
	            ob_end_clean();
	            \QRcode::png($data,$full_name,$level,$size,$padding,$save_and_print);
	            $QrCode = imagecreatefromstring(file_get_contents($full_name));
	            $logo = imagecreatefromstring(file_get_contents($logo));

	            $QrCode_width=imagesx($QrCode);
	            $logo_width=imagesx($logo);
	            $logo_height=imagesy($logo);
	            $logo_QrCode_width=$QrCode_width/8;
	            $scale = $logo_width/$logo_QrCode_width;
	            $logo_QrCode_height=$logo_height/$scale;
	            $form_width=($QrCode_width-$logo_QrCode_width)/2;

	            imagecopyresampled($QrCode,$logo,$form_width,$form_width,0,0,$logo_QrCode_width,$logo_QrCode_height,$logo_width,$logo_height);

	           
	            if($save_and_print){
	                if($type){
	                	imagepng($QrCode,$full_name);
	                	//base64
						$base64_image_content = "data:png;base64," . chunk_split(base64_encode(file_get_contents($full_name)));
	                	echo $base64_image_content;
	                }else{
	                	imagepng($QrCode,$full_name);
		                header('Content-type:image/png');
		                imagepng($QrCode);
	                }
	            }else{
	                if($type){
						$base64_image_content = "data:png;base64," . chunk_split(base64_encode(file_get_contents($full_name)));
						echo $base64_image_content;
						unlink($full_name);
	                }else{
	                	header('Content-type:image/png');
	                	imagepng($QrCode);
	                	unlink($full_name);
	                }
	            }
	        }
	    }
	}
    /**
     * 列出目录下的所有文件
     * @param str $path 目录
     * @param str $exts 后缀
     * @param array $list 路径数组
     * @return array 返回路径数组
     */
    function dir_list($path, $exts = '', $list = array()) {
        $path = dir_path($path);
        $files = glob($path . '*');
        foreach($files as $v) {
            if (!$exts || preg_match("/\.($exts)/i", $v)) {
                $list[] = $v;
                if (is_dir($v)) {
                    $list = dir_list($v, $exts, $list);
                }
            }
        }
        return $list;
    }

    /**
     * 组织地址目录
     * @param $path
     * @return mixed|string
     */
    function dir_path($path) {
        $path = str_replace('\\', '/', $path);
        if (substr($path, -1) != '/') $path = $path . '/';
        return $path;
    }

/*
* 得到客户端ip
* @param $type
*/
	function get_browse_ip($type = 0) {
	    $type       =  $type ? 1 : 0;
	    static $ip  =   NULL;
	    if ($ip !== NULL) return $ip[$type];
	    if($_SERVER['HTTP_X_REAL_IP']){//nginx 代理模式下，获取客户端真实IP
	        $ip=$_SERVER['HTTP_X_REAL_IP'];     
	    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {//客户端的ip
	        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
	    }elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {//浏览当前页面的用户计算机的网关
	        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	        $pos    =   array_search('unknown',$arr);
	        if(false !== $pos) unset($arr[$pos]);
	        $ip     =   trim($arr[0]);
	    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
	        $ip     =   $_SERVER['REMOTE_ADDR'];//浏览当前页面的用户计算机的ip地址
	    }else{
	        $ip=$_SERVER['REMOTE_ADDR'];
	    }
	    // IP地址合法验证
	    $long = sprintf("%u",ip2long($ip));
	    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
	    return $ip[$type];
	}
	/**
	 * [download 下载文件]
	 * @param  string $url      [文件地址]
	 * @param  string $filename [显示名称]
	 * @return [type]           [description]
	 */
	function download($url='',$filename=''){
		import("ORG.Net.Http");
		$url = !empty($url) ? $url : './Data/dirty_words_formart_config.xml';
		Http::download($url,$filename);
	}

	/**
	 * 判断黑白天
	 * @return bool
	 */
	function day_or_night(){
	    date_default_timezone_set('PRC');   //设定时区，PRC就是天朝
	    $hour = date('H');
	    if($hour <= 18 && $hour > 6){
	        return true;
	    }else{
	        return false;
	    }
}


	
	function unicode_encode ($word){
		$word0 = iconv('gbk', 'utf-8', $word);
		$word1 = iconv('utf-8', 'gbk', $word0);
		$word = ($word1 == $word) ? $word0 : $word;
		$word = json_encode($word);
		$word = preg_replace_callback('/\\\\u(\w{4})/', create_function('$hex', 'return \'&#\'.hexdec($hex[1]).\';\';'), substr($word, 1, strlen($word)-2));
		return $word;
	}
	function unicode_decode ($uncode)
	{
		$word = json_decode(preg_replace_callback('/&#(\d{5});/', create_function('$dec', 'return \'\\u\'.dechex($dec[1]);'), '"'.$uncode.'"'));
		return $word;
	}
	/**
	 * 检测访问的ip是否为规定的允许的ip
	 * Enter description here ...
	 */
	function check_ip($id=''){
		if(!empty($id)){
			$ALLOWED_IP=explode(',', $id);
		}else{
			$ALLOWED_IP=array('192.168.2.*','127.0.0.1','192.168.2.49');
		}
		
		$IP=get_browse_ip();
		$check_ip_arr= explode('.',$IP);	//要检测的ip拆分成数组
		//p($IP);p($ALLOWED_IP);die;
		#限制IP
		if(!in_array($IP,$ALLOWED_IP)) {
			foreach ($ALLOWED_IP as $val){
			    if(strpos($val,'*')!==false){//发现有*号替代符
			    	 $arr=array();//
			    	 $arr=explode('.', $val);
			    	 $bl=true;//用于记录循环检测中是否有匹配成功的
			    	 for($i=0;$i<4;$i++){
			    	 	if($arr[$i]!='*'){//不等于*  就要进来检测，如果为*符号替代符就不检查
			    	 		if($arr[$i]!=$check_ip_arr[$i]){
			    	 			$bl=false;
			    	 			break;//终止检查本个ip 继续检查下一个ip
			    	 		}
			    	 	}
			    	 }//end for 
			    	 if($bl){//如果是true则找到有一个匹配成功的就返回
			    	 	return;
			    	 	die;
			    	 }
			    }
			}//end foreach
			header('HTTP/1.1 403 Forbidden');
			echo "Access forbidden";
			die;
		}
	}
	/**
	 * 转换彩虹字
	 * @param string $str
	 * @param int $size
	 * @param bool $bold
	 * @return string
	 */
	function color_txt($str,$size=20,$bold=false){
		$len = mb_strlen($str);
		$colorTxt   = '';
		if($bold){
			$bold="bolder";
			$bolder="font-weight:".$bold;
		}
		for($i=0; $i<$len; $i++) {
			$colorTxt .=  '<span style="font-size:'.$size.'px;'.$bolder.'; color:'.rand_color().'">'.mb_substr($str,$i,1,'utf-8').'</span>';
		}
		return $colorTxt;
	}
	
	function rand_color(){
		return '#'.sprintf("%02X",mt_rand(0,255)).sprintf("%02X",mt_rand(0,255)).sprintf("%02X",mt_rand(0,255));
	}
	/**
	 * 替换表情
	 * @param string $content
	 * @return string
	 */
	function replace_phiz($content){
		preg_match_all('/\[.*?\]/is', $content, $arr);
		/**
		 * 替换表情
		 */
		if($arr[0]){
			$phiz=F('phiz','','./data/');
			foreach ($arr[0] as $v){
				foreach ($phiz as $key =>$value){
					if($v=='['.$value.']'){
						$content=str_repeat($v, '<img src="'.__ROOT__.'/Public/Images/phiz/'.$key.'.gif"/>',$content);
						break;
					}
				}
			}
			return $content;
		}
	}
	/**
	 * 截取字符串
	 * @param string $str
	 * @param int $start
	 * @param int $length
	 * @param string $charset
	 * @param bool $suffix
	 * @return string|string
	 */
	function sub_str($str,$start=0,$length,$charset="utf-8",$suffix=true){
		if(strlen($str)==0){
			return ;
		}
		$l=strlen($str);

		if(function_exists("mb_substr"))
			return 	!$suffix?mb_substr($str,$start,$length,$charset):mb_substr($str,$start,$length,$charset)."…";
		else if(function_exists('iconv_substr')){
			return  !$suffix?iconv_substr($str,$start,$length,$charset):iconv_substr($str,$start,$length,$charset)."…";
		}
		$re['utf-8']="/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312']="/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk']="/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5']="/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all($re[$charset],$str,$match);
		$slice = join("",array_slice($match[0],$start,$length));

		if($suffix){
			if($l>$length){
				return $slice."…";
			}else{
				return $slice;
			}
		} 
	}

	//获取栏目类型
	function getPosition($type){

		switch ($type) {
			case 1:
				$t='头部';
				break;
			case 2:
				$t='中部';
				break;
			case 3:
				$t='左侧';
				break;
			case 4:
				$t='右侧';
				break;
			case 5:
				$t='底部';
				break;
		}
		return $t;
	}

	/**
	 * [paichu 去掉指定的字符串]
	 * @param  [type] $mub [description]
	 * @param  [type] $zhi [description]
	 * @param  [type] $a   [description]
	 * @return [type]      [description]
	 */
	function paichu($mub,$zhi,$a='l'){
	    if(!$mub){
	        return "被替换的字符串不存在";
	    }

	    $mub = mb_convert_encoding($mub,'GB2312','UTF-8');
	    $zhi = mb_convert_encoding($zhi,'GB2312','UTF-8');
	     
	    if($a==""){
	    	$last = str_replace($mub,"",$zhi);
	    }elseif($a=="r"){
	        $last = substr($mub, strrpos($mub,$zhi));
	    }elseif($a=="l"){
	        //$last = preg_replace("/[\d\D\w\W\s\S]*[".$mub."]+/","",$zhi);
	        $last = substr($mub,0,strrpos($mub,$zhi));
	    }
	    //$last =  mb_convert_encoding($last,'UTF-8','GB2312'); 
	    return $last;

    }

	//获取img
	function get_images($str){
		/*preg_match_all('/\s+src\s?\=\s?[\'|"]([^\'|"]*)/is', $str, $match);
		//print_r( );*/
		$pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/"; 
		preg_match_all($pattern,$str,$match); 
		return $match; 
	}
	//高亮关键词
	function heigLine($key,$content){
		return preg_replace('/'.$key.'/i', '<font color="red"><b>'.$key.'</b></font>', $content);
	}
	
	function get_img($src){
		$str="";
		$img=split(',',$src);
		for ($i=0; $i <= count($img)-2; $i++) { 
			$str.= '<img style="margin-left:10px;" height="50" src="'.$img[$i].'">';
		}
		return $str;
	}
	/**
	 * [get_image 得到图片]
	 * @param  [type] $img [图片资源字符串]
	 * @return [type]      [description]
	 */
	function get_image($img){
		
		$arr=explode(',', $img);
		$str="";
		for ($i=0; $i <=count($arr)-1; $i++) { 
			$str.='<img src="__IMAGE__/'.$arr[$i].'">';
		}
		
		return $str;
	}
	function get_first($img){
		
		$arr=explode(',', $img);
		$str=$arr[0];
		return $str;
	}
	
	function reg($str){		 
		return  _strip_tags(array("p", "br"),$str); 

	}
  
	/**   
	* PHP去掉特定的html标签
	* @param array $string   
	* @param bool $str  
	* @return string
	*/  
	function _strip_tags($tagsArr,$str) {   
	    foreach ($tagsArr as $tag) {  
	        $p[]="/(<(?:\/".$tag."|".$tag.")[^>]*>)/i";  
	    }  
	    $return_str = preg_replace($p,"",$str);  
	    return $return_str;  
	}  
	/**
	 * [tag 截取字符串]
	 * @param  [type] $资源字符串
	 * @param  [type] $开始位置
	 * @param  [type] $截取长度
	 * @return [type] 结果字符串
	 */
	function tagstr($str,$start=0,$length=250){	
		$str=strip_tags(htmlspecialchars_decode($str));
		$temp=mb_substr($str,$start,$length,'utf-8');
		//return (strlen($str)>$length*1.5)?$temp.'...':$temp;
		return $temp;
	}


	/**  
	 * * 系统邮件发送函数  
	 * @param string $to    接收邮件者邮箱  
	 * @param string $name  接收邮件者名称  
	 * @param string $subject 邮件主题   
	 * @param string $body    邮件内容  
	 * @param string $attachment 附件列表 
	 * @return boolean   
	 */ 
	function think_send_mail($to, $name, $subject = '', $body = '', $attachment = null){     
		$config = C('THINK_EMAIL');     
		vendor('PHPMailer.class#phpmailer'); 
		//从PHPMailer目录导class.phpmailer.php类文件     
		$mail             = new PHPMailer(); //PHPMailer对象     
		$mail->CharSet    = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码     
		$mail->IsSMTP();  // 设定使用SMTP服务     
		$mail->SMTPDebug  = 0;                     // 关闭SMTP调试功能,1 = errors and messages,2 = messages only     
		$mail->SMTPAuth   = false;                  // 启用 SMTP 验证功能     
		$mail->SMTPSecure = 'ssl';                 // 使用安全协议     
		$mail->Host       = $config['SMTP_HOST'];  // SMTP 服务器     
		$mail->Port       = $config['SMTP_PORT'];  // SMTP服务器的端口号     
		$mail->Username   = $config['SMTP_USER'];  // SMTP服务器用户名     
		$mail->Password   = $config['SMTP_PASS'];  // SMTP服务器密码     
		$mail->SetFrom($config['FROM_EMAIL'], $config['FROM_NAME']);     
		$replyEmail       = $config['REPLY_EMAIL']?$config['REPLY_EMAIL']:$config['FROM_EMAIL'];     
		$replyName        = $config['REPLY_NAME']?$config['REPLY_NAME']:$config['FROM_NAME'];     
		$mail->AddReplyTo($replyEmail, $replyName);     
		$mail->Subject    = $subject;     
		$mail->MsgHTML($body);     
		$mail->AddAddress($to, $name);     
		if(is_array($attachment)){ // 添加附件         
			foreach ($attachment as $file){             
				is_file($file) && $mail->AddAttachment($file);         
			}     
		}     
		return $mail->Send() ? true : $mail->ErrorInfo; 
	}

	/*
     * 邮件发送
     * @param string $to 收件人邮箱，多个邮箱用,分开
     * @param string $title 标题
     * @param string $content 内容
     */

    function send_email($to,$title,$content,$webname="官方网站"){
        import('Class.Mail',APP_PATH);
        //邮件相关变量
        $cfg_smtp_server = 'smtp.163.com';
        $cfg_ask_guestview = '8';
        $cfg_smtp_port = '25';
        $cfg_ask_guestanswer = '8';
        $cfg_smtp_usermail = 'js_weiwei_100@163.com';//你的邮箱
        $cfg_smtp_user = 'js_weiwei_100@163.com';//你的邮箱号
        $cfg_smtp_password = 'wei110120';//你的邮箱密码

        $smtp = new smtp($cfg_smtp_server,$cfg_smtp_port,true,$cfg_smtp_usermail,$cfg_smtp_password);
        $smtp->debug = false;
        
        $cfg_webname=$webname;
        $mailtitle=$title;//邮件标题
        $mailbody=$content;//邮件内容 
                //$to 多个邮箱用,分隔
        $mailtype='html';
        return $smtp->sendmail($to,$cfg_webname,$cfg_smtp_usermail, $mailtitle, $mailbody, $mailtype);
    }
    /**
     * [NoRand 不重复随机数]
     * @param integer $begin [description]
     * @param integer $end   [description]
     * @param integer $limit [description]
     */
    function NoRand($begin=0,$end=20,$limit=4){
		$rand_array=range($begin,$end);
		shuffle($rand_array);//调用现成的数组随机排列函数
		return implode('',array_slice($rand_array,0,$limit));//截取前$limit个
	}
	/**
	 * [zeroize 数字补足]
	 * @param  int $num    		[带补足数字]
	 * @param  int $length 		[补足长度]
	 * @param  string $fill   	[补足字符]
	 * @param  int $fill   	  	[补足字符]
	 * @return [type]         	[description]
	 */
	function zeroize($num,$length=10,$type=1,$fill='0'){
		$type=$type?STR_PAD_LEFT:STR_PAD_RIGHT;
		return str_pad($num,$length,$fill,$type);
	}
	
	
	//////////////////////////////////////////////////////
    //Orderlist数据表，用于保存用户的购买订单记录；
    /* Orderlist数据表结构；
    CREATE TABLE `tb_orderlist` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `userid` int(11) DEFAULT NULL,购买者userid
      `username` varchar(255) DEFAULT NULL,购买者姓名
      `ordid` varchar(255) DEFAULT NULL,订单号
      `ordtime` int(11) DEFAULT NULL,订单时间
      `productid` int(11) DEFAULT NULL,产品ID
      `ordtitle` varchar(255) DEFAULT NULL,订单标题
      `ordbuynum` int(11) DEFAULT '0',购买数量
      `ordprice` float(10,2) DEFAULT '0.00',产品单价
      `ordfee` float(10,2) DEFAULT '0.00',订单总金额
      `ordstatus` int(11) DEFAULT '0',订单状态
      `payment_type` varchar(255) DEFAULT NULL,支付类型
      `payment_trade_no` varchar(255) DEFAULT NULL,支付接口交易号
      `payment_trade_status` varchar(255) DEFAULT NULL,支付接口返回的交易状态
      `payment_notify_id` varchar(255) DEFAULT NULL,
      `payment_notify_time` varchar(255) DEFAULT NULL,
      `payment_buyer_email` varchar(255) DEFAULT NULL,
      `ordcode` varchar(255) DEFAULT NULL,      
      `isused` int(11) DEFAULT '0',
      `usetime` int(11) DEFAULT NULL,
      `checkuser` int(11) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
    */
    //在线交易订单支付处理函数
    //函数功能：根据支付接口传回的数据判断该订单是否已经支付成功；
    //返回值：如果订单已经成功支付，返回true，否则返回false；
    function checkorderstatus($ordid){
        $Ord=M('orderlist');
        $ordstatus=$Ord->where('ordid='.$ordid)->getField('ordstatus');
        if($ordstatus==1){
            return true;
        }else{
            return false;    
        }
    }
    //处理订单函数
    //更新订单状态，写入订单支付后返回的数据
    function orderhandle($parameter){
        $ordid=$parameter['out_trade_no'];
        $data['payment_trade_no']      =$parameter['trade_no'];
        $data['payment_trade_status']  =$parameter['trade_status'];
        $data['payment_notify_id']     =$parameter['notify_id'];
        $data['payment_notify_time']   =$parameter['notify_time'];
        $data['payment_buyer_email']   =$parameter['buyer_email'];
        $data['ordstatus']             =1;
        $Ord=M('Orderlist');
        $Ord->where('ordid='.$ordid)->save($data);
    } 
    /*-----------------------------------
    2013.8.13更正
    下面这个函数，其实不需要，大家可以把他删掉，
    具体看我下面的修正补充部分的说明
    ------------------------------------*/
    //获取一个随机且唯一的订单号；
    function getordcode(){
        $Ord=M('Orderlist');
        $numbers = range (10,99);
        shuffle ($numbers); 
        $code=array_slice($numbers,0,4); 
        $ordcode=$code[0].$code[1].$code[2].$code[3];
        $oldcode=$Ord->where("ordcode='".$ordcode."'")->getField('ordcode');
        if($oldcode){
            getordcode();
        }else{
            return $ordcode;
        }
    }
    /**
     * [getKey 根据value得到数组key]
     * @param  [type] $arr   [数组]
     * @param  [type] $value [值]
     * @return [type]        [description]
     */
    function getKey($arr,$value) {
	 	if(!is_array($arr)) return null;
			foreach($arr as $k =>$v) {
			  $return = getKey($v, $value);
			  if($v == $value){
			   	return $k;
			  }
			  if(!is_null($return)){
			   return $return;
			}
		}
	}


	/**
	 * [php2class 转换成Think默认命名规则类]
	 * e.g:
	 * 修改文件夹下所有的php文件:.php --> .class.php
	 * php2class(__FILE__,'Action\MemberAction.class.php','Tool');
	 * @param [type] $path     		[文件夹路劲]
	 * @param [type] $reg_path 		[要替换文件夹]
	 * @param [type] $sea_path 		[待替换文件夹]
	 * @param  boolean $print    	[是否输出]
	 * @return [type]            	[description]
	 */
	function php2class($path,$reg_path,$sea_path,$print=false){
		$hostdir=!empty($path)?$path:__FILE__;

        if(!empty($reg_path) && !empty($sea_path)){
        	 $hostdir=str_replace($reg_path,$sea_path,$hostdir);
        } 

        $filesnames = scandir($hostdir);
        foreach ($filesnames as $k => $v) {
            if($k>1){ //修改类名
                if(strpos($v,'class')===false){
                    $temp=explode('.', $v);
                    $n=$hostdir.'\\'.$temp[0].'.class.php';
                    $o=$hostdir.'\\'.$v;
                    rename($o,$n);
                    if($print){
                    	p($n);
                    }
                }else{
                	if($print){
                		p($v);
                	}
                }
            }
         }     
	}