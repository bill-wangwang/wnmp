<?php

namespace app\common\exception;

use think\Exception;
use Throwable;

class PermissionDeniedException extends Exception {
    public function __construct($message = "无权访问", $code = 40101, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}