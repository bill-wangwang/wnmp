<?php

namespace app\common\service;

use app\common\exception\ParamsException;
use app\common\exception\VerifyCodeException;
use app\common\model\SmsLog;
use app\common\service\message\sms\DySmsService;
use app\common\service\message\sms\LocalSmsService;
use app\common\service\message\sms\QcloudSmsService;
use app\common\service\tools\UtilService;


class SmsService extends CommonService {

    /**
     * 发送短信
     * @param $type string 短信类型 如 register
     * @param $mobile string 手机号
     * @param array $params 需要发送的参数
     * @return string 第三方返回的业务id
     * @throws ParamsException
     * @throws \Exception
     */
    public static function send($type, $mobile, $params = []) {
        $smsType = config('sms_type');
        switch ($smsType) {
            case 'dysms':
                $out_id = DySmsService::send($type, $mobile, $params);
                break;
            case 'local':
                $out_id = LocalSmsService::send($type, $mobile, $params);
                break;
            case 'qcloudsms':
                $out_id = QcloudSmsService::send($type, $mobile, $params);
                break;
            default:
                throw new ParamsException("暂未支持的短信类型");
                break;
        }
        return $out_id;
    }

    /**
     * 获取对应的验证码
     * @param $type string 短信类型
     * @param $mobile string 手机号码
     * @param int $length 验证码长度
     * @param int $expiration_time 验证码有效时间（单位为分钟），有效时间内重复获取一样
     * @return string 返回验证码
     */
    public static function getVerifyCode($type, $mobile, $length = 4, $expiration_time = 10) {
        $cache_key = $type . '_' . $mobile ;
        $code = cache($cache_key);
        if (!$code) {
            $code = ToolService::getRandStr('', $length, '0123456789');
            cache($cache_key, $code, $expiration_time * 60);
        } else {
            // 有效时间内重新获取是同样的验证码，但是有效期需要从此刻开始
            cache($cache_key, $code, $expiration_time * 60);
        }
        return $code;
    }

    /**
     * 检验验证码
     * @param $type string 短信类型 如 register
     * @param $mobile string 手机号
     * @param $code string 验证码
     * @param int $max_try 允许尝试的最大次数
     * @param int $delete 验证通过后是否删除
     * @return bool
     * @throws VerifyCodeException
     */
    public static function checkVerifyCode($type, $mobile, $code, $max_try = 5, $delete = 1) {
        $cache_key = $type . '_' . $mobile ;
        $answer = cache($cache_key);
        if (!$answer) {
            throw  new VerifyCodeException("验证码无效或者已过期，请重新获取验证码");
        }
        //如果是本地类型 并且配置通用验证码 强制重置 answer 为配置项的内容
        if (config('app.sms_type') == 'local' && UtilService::isMobile($mobile)) {
            $temp = config('sms.local');
            if (isset($temp['public_verify_code']) && !empty($temp['public_verify_code'])) {
                // && $code == $temp['public_verify_code']
                $answer = $temp['public_verify_code'];
            }
        }
        $error_cache_key = $cache_key . '_error'; //错误次数的key
        if ($code == $answer) {
            if ($delete) {
                cache($cache_key, null);
                cache($error_cache_key, null);
            }
            return true;
        } else {
            $error_count = intval(cache($error_cache_key)) + 1;
            if ($error_count > $max_try) {
                cache($cache_key, null);
                cache($error_cache_key, null);
                throw  new VerifyCodeException("尝试错误次数过多，请重新获取验证码");
            } else {
                cache($error_cache_key, $error_count);
                throw  new VerifyCodeException("验证码不正确");
            }
        }
    }

    /**
     * 记录短信日志到数据库
     * @param $type string 短信类型 如 register
     * @param $mobile string 手机号
     * @param array $params 需要发送的参数
     * @param $out_id string 第三方返回的业务id
     * @return SmsLog
     */
    public static function smsLog($type, $mobile, $params, $out_id = '') {
        $smsType = config('sms_type');
        return SmsLog::create([
            'sms_type' => $smsType,
            'type'     => $type,
            'mobile'   => $mobile,
            'params'   => json_encode($params),
            'out_id'   => $out_id
        ]);
    }
}