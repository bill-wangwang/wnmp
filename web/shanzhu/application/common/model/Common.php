<?php

namespace app\common\model;

use think\Model;

/**
 * Class Common
 * @package app\common\model
 * @method static static where($field, $op = null, $condition = null)
 * @method static static find($data = null)
 * @method static static order($field, $order = null)
 */
class Common extends Model
{
    /*********状态*********/
    const STATUS_OFF = 0;  //禁用
    const STATUS_ON = 1;  //启用

    const STATUS_LIST = [self::STATUS_OFF => '禁用', self::STATUS_ON => '启用'];

    public function getStatusTextAttr($value, $data)
    {
        return isset(self::STATUS_LIST[$data['status']]) ? self::STATUS_LIST[$data['status']] : '未知';
    }

    public function getStatusColorAttr($value, $data)
    {
        $arr = [
            self::STATUS_OFF => 'text-error',
            self::STATUS_ON => 'text-success'
        ];
        return isset($arr[$data['status']]) ? $arr[$data['status']] : 'muted';
    }
}