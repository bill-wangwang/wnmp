<?php

namespace app\common\exception;

use think\Exception;
use Throwable;

/**
 * 记录不存在
 * Class ObjectNotFoundException
 * @package app\common\exception
 */
class ObjectNotFoundException extends Exception {
    public function __construct($message = "记录不存在", $code = 3, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}