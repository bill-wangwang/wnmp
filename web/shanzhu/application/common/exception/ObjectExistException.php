<?php

namespace app\common\exception;

use think\Exception;
use Throwable;

class ObjectExistException extends Exception {
    public function __construct($message = "记录已存在", $code = 2, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}