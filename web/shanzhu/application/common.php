<?php
use think\facade\log;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 原样输出变量
 * @param $var mixed 变量
 */
function p($var) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

/**
 * 原样输出变量并停止
 * @param $var mixed 变量
 */
function pe($var) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
    exit(__FILE__ . ' ON LINE ' . __LINE__);
}

/**
 * 获取图片，没有图片时使用默认图片，有原始图片时还可以指定宽高
 * @param $sourceImagesUrl string 目标图片url地址，一般为数据库中的某个字段值
 * @param $defaultImageUrl string 默认图片url地址，目标图片地址为空时取该值，该值也可时取system.default_image
 * @param $suffix  string 可以对$sourceImagesUrl图片进行样式编码 400*300*1 宽*高*模式（暂支持 oss和qiniu）
 * @param $uploadType string 可以指定原图片的存储类型，为空时自动获取配置文件的 app.upload_type
 * @return string
 */
function img($sourceImagesUrl, $defaultImageUrl = '', $suffix = '', $uploadType='') {
    $defaultImageUrl = $defaultImageUrl ? $defaultImageUrl : config('system.default_image');
    $objectUrl = $sourceImagesUrl ?: $defaultImageUrl;
    if ($suffix) {
        $mode = 0;
        $arr = explode('*', $suffix);
        if (sizeof($arr) == 1) {
            $width = intval($arr[0]);
            $height = intval($arr[0]);
        } else if (sizeof($arr) == 2) {
            $width = intval($arr[0]);
            $height = intval($arr[1]);
        } else {
            $width = intval($arr[0]);
            $height = intval($arr[1]);
            $mode = intval($arr[2]);
        }
        if(!$uploadType){
            $uploadType = strtolower(config('upload_type'));
        }
        switch ($uploadType) {
            case 'qiniu':
                $objectUrl .= "?imageView2/{$mode}/w/{$width}/h/{$height}";
                break;
            case 'oss':
                $objectUrl .= "?x-oss-process=image/resize,w_{$width},h_{$height}"; //image/resize,w_200/rotate,90
                break;
            case 'cos':
                //腾讯云cos不支持图片缩放，只有万象优图服务（需要先在网页控制台先创建样式直接传入样式）
                $objectUrl .= $suffix;
                break;
            default :
                //暂未实现本地缩放
                break;
        }
    }
    return $objectUrl;
}

function get_file_size($size) {
    if($size >= 1073741824) {
        $size = round($size / 1073741824 * 100) / 100 . ' GB';
    } elseif($size >= 1048576) {
        $size = round($size / 1048576 * 100) / 100 . ' MB';
    } elseif($size >= 1024) {
        $size = round($size / 1024 * 100) / 100 . ' KB';
    } else {
        $size = $size . ' B';
    }
    return $size;
}

function get_price($amount){
    return number_format($amount / 100, 2, '.', '');
}

function set_value_both_ends($string, $split='"'){
    return $split . $string . $split;
}

/**
 * 写日志到数据库
 * @param $message string 日志详情
 * @param $type string 类型
 * @return mixed 日志id
 */
function write_log($message, $type) {
    $data['type'] = $type;
    $data['message'] = $message;
    $data['admin_name'] = session(config('auth.LOGIN_SESSION'))['admin_name'] ? session(config('auth.LOGIN_SESSION'))['admin_name'] : '';
    $data['create_time'] = time();
    $data['create_ip'] = $_SERVER['REMOTE_ADDR'];
    $Log = new \app\common\model\Log();
    $log = $Log::create($data);
    return $log->id;
}

/**
 * 根据ip地址获得ip所在地
 * @param string $ip
 * @param int $showIp 是否显示ip 0-不显示 1-全显示 2-最后一段用*号代替
 * @return string 返回ip所在地址（或者加上ip）
 * @todo 根据本地ip库查询，使用网络查询
 */
function get_local_by_ip($ip, $showIp = 2) {
    if (empty ($ip)) {
        return '';
    }
    $Ip = new \ip\IpLocation('UTFWry.dat');
    $area = $Ip->getlocation($ip);
    $address = $area ['country'];
    $address = iconv('gbk', 'utf-8', $address);
    if ($showIp == 2) {
        $ipArray = explode('.', $ip);
        unset ($ipArray [3]);
        $ip = implode('.', $ipArray) . '.*';
        return $ip . '(' . $address . ')';
    } else if ($showIp == 1) {
        return $ip . '(' . $address . ')';
    } else {
        return $address;
    }
}

function get_log_data($message, $data=[]) {
    if(empty($data) && is_string($message)){
        return $message;
    }
    $result = [
        'message' => $message,
        'data' => $data
    ];
    return var_export($result, 1);
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0,$adv=false) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if($adv){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 对html和js进行转义 如 <font color=red>红色</font>  => &lt;font color=red&gt;红色的&lt;/font&gt;gt;
 * @param $str string 原始字符串
 * @return string 转义后的字符串
 */
function get_escape_string($str){
    $str = $str ? trim($str) : $str;
    return htmlentities($str);
}

/**
 * 对非文本的字符串进行过滤 如 <font color=red>红色的</font> => 红色的
 * @param $str string 原始字符串
 * @return string 去除html后的文本
 */
function get_text_string($str){
    $str = $str ? trim($str) : $str;
    return strip_tags($str);
}



function curl_get($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $errorMessage = serialize(curl_error($ch));
        Log::error("访问网络出错(POST)：【{$errorMessage}】");
        return false;
    }
    curl_close($ch);
    return $result;
}

/**
 * @param $url
 * @param string $dataStr 提交
 * @param array $headers 头部信息
 * @param string $cert 证书
 * @param string $certKey 证书秘钥
 * @return bool|mixed
 */
function curl_post($url, $dataStr, $headers = [], $cert = '', $certKey = '') {
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL, $url);  //抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, $headers); //设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    //证书默认格式为PEM
    if($cert && $certKey){
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT, $cert);

        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY, $certKey);
    }

    curl_setopt($ch, CURLOPT_POST, 1); //post提交方式

    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataStr);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $errorMessage = serialize(curl_error($ch));
        Log::error("访问网络出错(POST)：【{$errorMessage}】" . var_export($dataStr, 1));
        return false;
    }
    curl_close($ch);
    return $result;
}
