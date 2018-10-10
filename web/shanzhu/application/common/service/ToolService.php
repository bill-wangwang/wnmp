<?php

namespace app\common\service;

use app\common\exception\NetworkException;
use think\facade\Log;

class ToolService extends CommonService {
    const ENCRYPTION_FACTOR = "MALL_2018";

    /**
     * 从一段文字里面获取图片地址
     * @param $content string 文字
     * @param int $index 索引，从0开始
     * @return string 图片url
     */
    public static function getPic($content, $index = 0) {
        $picUrl = '';
        if ($content != '') {
            $pattern = "/<img.*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/i";
            $match_times = preg_match_all($pattern, $content, $match);
            if ($match_times > 0) {
                $picUrl = $match[1][$index];
            }
        }
        return $picUrl;
    }

    /**
     * curl get 请求
     * @param $url string 需要请求的url地址
     * @return mixed
     * @throws NetworkException
     */
    public static function curlGet($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $errorMessage = serialize(curl_error($ch));
            Log::error("访问网络[{$url}]出错：[{$errorMessage}]");
            throw new NetworkException();
        }
        curl_close($ch);
        return $result;
    }

    /**
     * curl post 请求
     * @param $url string 需要请求的url地址
     * @param $data array 请求的参数
     * @param bool $encode 是否需要转码
     * @return mixed
     * @throws NetworkException
     */
    public static function curlPost($url, $data, $encode = true) {
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL, $url);  //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        if ($encode) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $errorMessage = serialize(curl_error($ch));
            Log::error("访问网络[{$url}]出错：[{$errorMessage}],data:" . json_encode($data));
            throw new NetworkException();
        }
        curl_close($ch);
        return $result;
    }

    /**
     * 检查相应结果json
     * @param $str
     * @param $checkField string 需要检查的字段
     * @param int $rightCode
     * @return bool
     */
    public static function checkJson($str, $checkField='code', $rightCode = 0) {
        $str = trim($str);
        if (empty($str)) {
            return false;
        }
        $json = json_decode($str, 1);
        if (is_array($json) && isset($json[$checkField]) && $json[$checkField] == $rightCode) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取随机字符串
     * @param string $pre 前缀
     * @param int $length 长度（不含前缀长度）
     * @param string $codeSet 只从指定的字符里面获取
     * @return string
     */
    public static function getRandStr($pre = '', $length = 4, $codeSet = "") {
        if (!$codeSet) {
            $codeSet = "0123456789ABCDEFGHJKLMNPQRTUVWXY";
        }
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $codeSet[mt_rand(0, strlen($codeSet) - 1)];
        }
        return $pre . $code;
    }

    /**
     * 找回密码获取密码修改凭证（有效期30分钟）
     * @param $username
     * @return string
     */
    public static function getResetPwdToken($username) {
        $token = md5($username . time());
        cache(self::CACHE_RESET_PWD_TOKEN_PREFIX . $username, $token, ['expire' => 30 * 60]);
        return $token;
    }

    /**
     * 验证重置密码凭证
     * @param $username
     * @param $token
     * @return bool
     */
    public static function validateResetPwdToken($username, $token) {
        $cacheToken = cache(self::CACHE_RESET_PWD_TOKEN_PREFIX . $username);
        if($cacheToken == $token){
            cache(self::CACHE_RESET_PWD_TOKEN_PREFIX . $username, null);
            return true;
        }else{
            return false;
        }
    }


    /**
     * 解密
     * @param $string
     * @return bool|string
     */
    public static function auth_decode($string)
    {
        return self::authCode($string);
    }

    /**
     * 加密
     * @param $string
     * @return bool|string
     */
    public static function auth_encode($string)
    {
        return self::authCode($string, 'ENCODE');
    }

    private static function authCode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        if($operation == 'DECODE') {
            $string = str_replace('[a]','+',$string);
            $string = str_replace('[b]','&',$string);
            $string = str_replace('[c]','/',$string);
        }
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
        $ckey_length = 4;

        // 密匙
        $key = md5($key ? $key : self::ENCRYPTION_FACTOR);

        // 密匙a会参与加解密
        $keya = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyb = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) :
            substr(md5(microtime()), -$ckey_length)) : '';
        // 参与运算的密匙
        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
            sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        // 产生密匙簿
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE') {
            // substr($result, 0, 10) == 0 验证数据有效性
            // substr($result, 0, 10) - time() > 0 验证数据有效性
            // substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性
            // 验证数据有效性，请看未加密明文的格式
            if ((substr($result, 0, 10) == 0 || intval(substr($result, 0, 10)) - time() > 0) &&
                substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)
            ) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
            $ustr = $keyc.str_replace('=', '', base64_encode($result));
            $ustr = str_replace('+','[a]',$ustr);
            $ustr = str_replace('&','[b]',$ustr);
            $ustr = str_replace('/','[c]',$ustr);
            return $ustr;
        }
    }


}