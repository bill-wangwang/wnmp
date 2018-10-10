<?php

namespace app\common\model;

use think\model\concern\SoftDelete;

class User extends Common {
    use SoftDelete;
    protected $defaultSoftDelete = 0;
}