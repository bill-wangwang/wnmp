<?php

namespace app\common\service;

use app\common\model\User;

class UserService extends CommonService {

    public static function getById($id) {
        return User::find($id);
    }

    public static function getByMobile($mobile) {
        return User::where([['mobile', '=', $mobile]])->find();
    }

    public static function create($data) {
        return User::create($data);
    }

}