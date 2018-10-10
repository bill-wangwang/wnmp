<?php

namespace app\common\model;

class SmsLog extends Common {
    protected $updateTime = false;

    /**
     * 获取短信类型
     * @return array
     */
    public static function getTypeList() {
        $list = [];
        $smsType = config('sms_type');
        $smsConfig = config('sms.');
        $templates = $smsConfig[$smsType]['template'];
        foreach ($templates as $type => $template) {
            $list[$type] = $template['title'];
        }
        return $list;
    }

    public function getTypeTextAttr($value, $data) {
        $list = self::getTypeList();
        return isset($list[$data['type']]) ? $list[$data['type']] : '';
    }
}