<?php

namespace app\common\service\message\sms;

use app\common\exception\ParamsException;
use app\common\service\CommonService;
use app\common\service\SmsService;
use app\common\service\ToolService;

class LocalSmsService extends CommonService {

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
        if (!isset($smsConfig['template'][$type])) {
            throw new ParamsException("未知短信类型({$type})");
        }
        $config = $smsConfig['template'][$type];
        if (is_array($config['params']) && sizeof($config['params']) != sizeof($params)) {
            throw new ParamsException("短信类型({$type})参数个数不正确");
        }
        $out_id = ToolService::getRandStr('local_' . date('ymdHis_'), 4, '0123456789');
        SmsService::smsLog($type, $mobile, $params, $out_id);
        return $out_id;
    }
}