<?php

namespace app\common\service\message\sms;

use app\common\exception\ParamsException;
use app\common\service\CommonService;
use app\common\service\SmsService;
use Qcloud\Sms\SmsSingleSender;
use think\facade\Log;

class QcloudSmsService extends CommonService {

    /**
     * 发送单条短信
     * @param $type string 类型 如 register
     * @param $mobile string 手机号
     * @param array $params 参数
     * @return string
     * @throws ParamsException
     * @throws \Exception
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

        if (empty($access_key_id) || empty($access_key_secret) ) {
            throw new ParamsException("暂未配置({$smsType})短信的授权、短信签名等参数");
        }
        if (!isset($smsConfig['template'][$type])) {
            throw new ParamsException("未知短信类型({$type})");
        }
        $config = $smsConfig['template'][$type];
        if (is_array($config['params']) && sizeof($config['params']) != sizeof($params)) {
            throw new ParamsException("短信类型({$type})参数个数不正确");
        }
        try {
            $qcloudSms = new SmsSingleSender($access_key_id, $access_key_secret);
            $result = $qcloudSms->sendWithParam("86", $mobile, $config['template_id'], $params, $sign_name, "", "");
            Log::info("发送腾讯云短信返回：" . $result);
            if (!$result) {
                Log::error("发送短信失败,期望字符串结果为空(001)。{$type}, {$mobile}" . json_encode($params));
                throw new \Exception("发送短信失败(001)", 50000);
            }

            $resArray = json_decode($result, 1);
            if (!is_array($resArray)) {
                Log::error("发送短信失败,期望是个json字符串(002)。{$type}, {$mobile}" . json_encode($params));
                throw new \Exception("发送短信失败(002)", 50000);
            }
            if (isset($resArray['result']) && $resArray['result'] === 0) {
                $out_id = trim($resArray['sid']);
                SmsService::smsLog($type, $mobile, $params, $out_id);
                return $out_id;
            } else {
                Log::error("发送短信失败：" .  $resArray['errmsg'] . ',detail:' . $result );
                throw new \Exception("发送短信失败:" . $resArray['errmsg'] , 50000);
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            Log::error("发送短信失败{$type}, {$mobile}" . json_encode($params) . "res:" . json_encode($error));
            throw new \Exception("发送短信失败 {$error}", 50000);
        }
    }
}