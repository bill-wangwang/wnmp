<?php

namespace tools;

use think\Validate;

class Verify {

    public static function checkEmail($email) {
        return Validate::is($email,'email');
    }

    public static function checkMobile($mobil) {
        $mobile = trim($mobil);
        if(strlen($mobile)!=11 || !is_numeric($mobile) ){
            return false;
        }
        return preg_match('#^1[3-9][\d]{9}$#', $mobile) ? true : false;
    }
}