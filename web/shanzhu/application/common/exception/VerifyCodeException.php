<?php
namespace app\common\exception;

use think\Exception;
use Throwable;

class VerifyCodeException extends Exception {

    public function __construct($message = "验证码错误", $code = 7, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}