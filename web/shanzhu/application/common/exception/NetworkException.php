<?php

namespace app\common\exception;


use think\Exception;
use Throwable;

class NetworkException extends Exception {

    public function __construct($message = "网络异常", $code = 6, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}