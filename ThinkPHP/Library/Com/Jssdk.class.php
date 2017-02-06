<?php
/**
 * 官方文档：http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html
 * 官方示例：http://demo.open.weixin.qq.com/jssdk/sample.zip
 * 
 * 微信JSSDK类,主要修改了保存会话信息机制，示例中使用的是文件，这里使用了ThinkPHP的缓存机制，参考官方提供的示例文档 
 * 新增了调试模式,调用示例如下：
 *      $jssdk = new JSSDK(C('WX_APPID'), C('WX_SECRET'));
 *      $jssdk->debug = true;    //启用本地调试模式,将官方的两个json文件放到入口文件index.php同级目录即可!
 *      $signPackage = $jssdk->GetSignPackage();
 * @命名空间版本
 * @author 阿甘 (QQ:33808 624)
 * @date 2015-1-10 14:10
 */
namespace Com;

class Jssdk {
  private $appId;
  private $appSecret;
  public $debug = false;

  public function __construct($appId, $appSecret) {
    $this->appId = $appId;
    $this->appSecret = $appSecret;
  }

  public function getSignPackage() {
    $jsapiTicket = $this->getJsApiTicket();
    $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $timestamp = time();
    $nonceStr = $this->createNonceStr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr×tamp=$timestamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
      "appId"     => $this->appId,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return $signPackage; 
  }

  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

  private function getJsApiTicket() {
      //debug模式
      if ($this->debug) {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(file_get_contents("jsapi_ticket.json"));
      } else {
          //从cache中读取，基于ThinkPHP的缓存机制
          $data = (object)(S('jsapi_ticket_json'));
      }

    if ($data->expire_time < time()) {       
      $accessToken = $this->getAccessToken();
      $url = "http://api.weixin.qq.com/cgi-bin/ticket/getticket?type=1&access_token=$accessToken";
      $res = json_decode($this->httpGet($url));
      $ticket = $res->ticket;
      
      if ($ticket) {
        $data->expire_time = time() + 7200;
        $data->jsapi_ticket = $ticket;
               
        //debug模式
        if ($this->debug) {
            $fp = fopen("jsapi_ticket.json", "w");
            fwrite($fp, json_encode($data));
            fclose($fp);
        } else {
            //将对象以数组的形式进行缓存
            S('jsapi_ticket_json', (array)$data);
        }

      }
    } else {
      $ticket = $data->jsapi_ticket;
    }

    return $ticket;
  }

  private function getAccessToken() {

      //debug模式
      if ($this->debug) {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
          $data = json_decode(file_get_contents("access_token.json"));
          dump($data);
          die();
      } else {
          //从缓存中读取数组并转成对象
        $data = (Object)(S('access_token.json'));
      }
    
    if ($data->expire_time < time()) { 
      $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
      $res = json_decode($this->httpGet($url));
      $access_token = $res->access_token;

      if ($access_token) {
        $data->expire_time = time() + 7000;
        $data->access_token = $access_token;

        //debug模式
        if ($this->debug) {
            $fp = fopen("access_token.json", "w");
            fwrite($fp, json_encode($data));
            fclose($fp);
        } else {
            //缓存数组
            S('access_token.json', (array)$data);            
        }
        
      }
    } else {
      $access_token = $data->access_token;
    }
    return $access_token;
  }

  private function httpGet($url) {
      
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    
    //错误检测
    $error = curl_error($curl);
    
    curl_close($curl);
    
    //发生错误，抛出异常
    if($error) throw new \Exception('请求发生错误(表检查是否在授权域名下访问)：' . $error);
    
    return $res;
  }
}