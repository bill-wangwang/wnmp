<?php

namespace app\common\exception;

use think\Exception;
use Throwable;

/**
 * 参数异常
 * Class ParamsException
 * @package app\common\exception
 */
class ParamsException extends Exception {

    public function __construct($message = "参数异常", $code = 1, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}