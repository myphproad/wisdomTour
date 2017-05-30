<?php
/**
 * Created by phpmystorm
 * User: 云清(sean)
 * Date: 2017/5/30
 * Time: 12:13
 * Company:亿次元科技
 * Email:ma.running@foxmail.com
 *  功能:
 */
//----------------------------------
// 历史上的今天调用示例代码 － 聚合数据
// 在线接口文档：http://www.juhe.cn/docs/63
//----------------------------------

header('Content-type:text/html;charset=utf-8');


//配置您申请的appkey
$appkey = "*********************";




//************1.事件列表************
$url = "http://api.juheapi.com/japi/toh";
$params = array(
    "key" => $appkey,//应用APPKEY(应用详细页查询)
    "v" => "",//版本，当前：1.0
    "month" => "",//月份，如：10
    "day" => "",//日，如：1
);
$paramstring = http_build_query($params);
$content = juhecurl($url,$paramstring);
$result = json_decode($content,true);
if($result){
    if($result['error_code']=='0'){
        print_r($result);
    }else{
        echo $result['error_code'].":".$result['reason'];
    }
}else{
    echo "请求失败";
}
//**************************************************




//************2.根据ID查询事件详情************
$url = "http://api.juheapi.com/japi/tohdet";
$params = array(
    "key" => $appkey,//应用APPKEY(应用详细页查询)
    "v" => "",//版本，当前：1.0
    "id" => "",//事件ID
);
$paramstring = http_build_query($params);
$content = juhecurl($url,$paramstring);
$result = json_decode($content,true);
if($result){
    if($result['error_code']=='0'){
        print_r($result);
    }else{
        echo $result['error_code'].":".$result['reason'];
    }
}else{
    echo "请求失败";
}
//**************************************************





/**
 * 请求接口返回内容
 * @param  string $url [请求的URL地址]
 * @param  string $params [请求的参数]
 * @param  int $ipost [是否采用POST形式]
 * @return  string
 */
function juhecurl($url,$params=false,$ispost=0){
    $httpInfo = array();
    $ch = curl_init();

    curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
    curl_setopt( $ch, CURLOPT_USERAGENT , 'JuheData' );
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 60 );
    curl_setopt( $ch, CURLOPT_TIMEOUT , 60);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if( $ispost )
    {
        curl_setopt( $ch , CURLOPT_POST , true );
        curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
        curl_setopt( $ch , CURLOPT_URL , $url );
    }
    else
    {
        if($params){
            curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
        }else{
            curl_setopt( $ch , CURLOPT_URL , $url);
        }
    }
    $response = curl_exec( $ch );
    if ($response === FALSE) {
        //echo "cURL Error: " . curl_error($ch);
        return false;
    }
    $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
    $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
    curl_close( $ch );
    return $response;
}