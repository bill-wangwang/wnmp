<?php

namespace app\common\service\tools;

use app\common\service\CommonService;

class UtilService extends CommonService {

    public static function isMobile($mobile) {
        $pattern = "/^1([3456789])[0-9]{9}$/";
        return preg_match($pattern, $mobile);
    }

}