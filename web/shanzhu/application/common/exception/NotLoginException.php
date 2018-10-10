<?php

namespace app\common\exception;

use think\Exception;
use Throwable;

/**
 * 未登录或者登录已经超时
 * Class NotLoginException
 * @package app\common\exception
 */
class NotLoginException extends Exception {
    public function __construct($message = "未登录或者登录已经超时", $code = 4, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}