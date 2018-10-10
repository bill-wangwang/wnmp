<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/8
 * Time: 11:54
 */

namespace app\common\exception;


use think\Exception;
use Throwable;

class DbException extends Exception {

    public function __construct($message = "数据库操作失败", $code = 5, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}