<?php

namespace app\common\exception;


use think\Exception;
use Throwable;

class UploadException extends Exception {

    public function __construct($message = "上传失败", $code = 8, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}