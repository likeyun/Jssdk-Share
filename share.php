<?php
// 声明页面header
header("Content-type:charset=utf-8");

// 声明APPID、APPSECRET
$appid = "xxx";
$appsecret = "xxx";

// 获取access_token和jsapi_ticket
function getToken(){
   $file = file_get_contents("access_token.json",true);//读取access_token.json里面的数据
   $result = json_decode($file,true);

//判断access_token是否在有效期内，如果在有效期则获取缓存的access_token
//如果过期了则请求接口生成新的access_token并且缓存access_token.json
if (time() > $result['expires']){
       $data = array();
       $data['access_token'] = getNewToken();
       $data['expires'] = time()+7000;
       $jsonStr =  json_encode($data);
       $fp = fopen("access_token.json", "w");
       fwrite($fp, $jsonStr);
       fclose($fp);
       return $data['access_token'];
   }else{
       return $result['access_token'];
   }
}

//获取新的access_token
function getNewToken($appid,$appsecret){
   global $appid;
   global $appsecret;
   $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret."";
   $access_token_Arr =  file_get_contents($url);
   $token_jsonarr = json_decode($access_token_Arr, true);
   return $token_jsonarr["access_token"];
}

$access_token = getToken();

//缓存jsapi_ticket
function getTicket(){
   $file = file_get_contents("jsapi_ticket.json",true);//读取jsapi_ticket.json里面的数据
   $result = json_decode($file,true);

//判断jsapi_ticket是否在有效期内，如果在有效期则获取缓存的jsapi_ticket
//如果过期了则请求接口生成新的jsapi_ticket并且缓存jsapi_ticket.json
if (time() > $result['expires']){
       $data = array();
       $data['jsapi_ticket'] = getNewTicket();
       $data['expires'] = time()+7000;
       $jsonStr =  json_encode($data);
       $fp = fopen("jsapi_ticket.json", "w");
       fwrite($fp, $jsonStr);
       fclose($fp);
       return $data['jsapi_ticket'];
   }else{
       return $result['jsapi_ticket'];
   }
}

//获取新的access_token
function getNewTicket($appid,$appsecret){
   global $appid;
   global $appsecret;
   $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=".getToken()."";
   $jsapi_ticket_Arr =  file_get_contents($url);
   $ticket_jsonarr = json_decode($jsapi_ticket_Arr, true);
   return $ticket_jsonarr["ticket"];
}

$jsapiTicket = getTicket();

// 动态获取URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// 生成时间戳
$timestamp = time();

// 生成nonceStr
$createNonceStr = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
str_shuffle($createNonceStr);
$nonceStr = substr(str_shuffle($createNonceStr),0,16);

// 按照 key 值 ASCII 码升序排序
$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

// 按顺序排列按sha1加密生成字符串
$signature = sha1($string);

?>
<!DOCTYPE html>
<html lang="zh_cn">
   <head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
       <title>要分享的index模板页面</title>  

   </head>
   <body>
       <h3>已获得updateAppMessageShareData</h3>
       <h3>已获得updateTimelineShareData</h3>
       <h3>这两个API权限</h3>
       <h3>请点击右上角[···]体验</h3>
   </body>
</html>
   <!-- 引入微信分享js-->
   <script src="http://res.wx.qq.com/open/js/jweixin-1.6.0.js"></script>
   <script type="text/javascript">
    // 初始化配置
    wx.config({
       debug: true, // 正式上线后改成false不在弹出调试信息
       appId: '<?php echo $appid;?>',
       timestamp: '<?php echo $timestamp;?>',
       nonceStr: '<?php echo $nonceStr;?>',
       signature: '<?php echo $signature;?>',
       jsApiList: [
         // 所有要调用的 API 都要加到这个列表中
         'updateAppMessageShareData', //分享到朋友圈
         'updateTimelineShareData',//分享给朋友
       ]
     });

    // 配置完成后会调用ready函数
    wx.ready(function (res) {

     //分享到朋友圈
     wx.updateTimelineShareData({
       title: '调用成功！分享到朋友圈！', // 分享标题
       link: 'http://www.likeyunba.com/gzh/jssdk.php', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
       imgUrl: 'http://www.likeyunba.com/gzh/1.jpg', // 分享图标
       success: function (res) {
         // 分享成功
       }
     })

     wx.updateAppMessageShareData({ 
       title: '调用成功！分享给好友！', // 分享标题
       desc: '微信JSSDK1.6新版分享接口成功调用！', // 分享描述
       link: 'http://www.likeyunba.com/gzh/jssdk.php', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
       imgUrl: 'http://www.likeyunba.com/gzh/1.jpg', // 分享图标
       success: function (res) {
         // 分享成功
       }
     })

   });

   //错误返回信息
   wx.error(function(res){    
   // config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。    
       alert(res);
   });    
   
</script>
