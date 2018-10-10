<?php

namespace app\common\service\message\sms;

use app\common\exception\ParamsException;
use app\common\service\CommonService;
use app\common\service\SmsService;
use message\sms\ali\DySms;
use think\facade\Log;

class DySmsService extends CommonService {

    /**
     * 发送单条短信
     * @param $type string 类型 如 register
     * @param $mobile string 手机号
     * @param array $params 参数
     * @throws
     * @return
     */
    public static function send($type, $mobile, $params = []) {
        if (empty($type) || empty($mobile)) {
            throw new ParamsException("发送短信缺少必填参数");
        }
        $smsType = config('sms_type');
        $allSmsConfig = config('sms.');
        if (!isset($allSmsConfig[$smsType])) {
            throw new ParamsException("暂未配置({$smsType})短信");
        }
        $smsConfig = $allSmsConfig[$smsType];
        $access_key_id = $smsConfig['access_key_id'] ?: '';
        $access_key_secret = $smsConfig['access_key_secret'] ?: '';
        $sign_name = $smsConfig['sign_name'] ?: '';
        $domain = $smsConfig['domain'] ?: '';
        $region_id = $smsConfig['region_id'] ?: '';
        $action = $smsConfig['action'] ?: '';
        $version = $smsConfig['version'] ?: '';
        if (empty($access_key_id) || empty($access_key_secret) || empty($sign_name) || empty($domain) || empty($region_id) || empty($action) || empty($version)) {
            throw new ParamsException("暂未配置({$smsType})短信的授权、短信签名等参数");
        }
        if (!isset($smsConfig['template'][$type])) {
            throw new ParamsException("未知短信类型({$type})");
        }
        $config = $smsConfig['template'][$type];
        if (is_array($config['params']) && sizeof($config['params']) != sizeof($params)) {
            throw new ParamsException("短信类型({$type})参数个数不正确");
        }
        $dySms = new DySms();
        $requestParam = [
            'PhoneNumbers'  => $mobile,
            'SignName'      => $sign_name,
            'TemplateCode'  => $config['template_id'],
            'TemplateParam' => $params,
            'RegionId'      => $region_id,
            'Action'        => $action,
            'Version'       => $version,
        ];
        if (!empty($requestParam["TemplateParam"]) && is_array($requestParam["TemplateParam"])) {
            $requestParam["TemplateParam"] = json_encode($requestParam["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }
        $content = $dySms->request($access_key_id, $access_key_secret, $domain, $requestParam);
        if(is_object($content) && isset($content->Message) && isset($content->BizId)  && $content->Message=='OK' ){
            $out_id = $content->BizId;
            SmsService::smsLog($type, $mobile, $params, $out_id);
            return $out_id;
        } else {
            $error = $content->Message ?: '' ;
            Log::error("发送短信失败{$type}, {$mobile}" . json_encode($params) . "res:" . json_encode($content));
            throw new \Exception("发送短信失败 {$error}", 50000);
        }
    }
}