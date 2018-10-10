<?php
namespace app\common\service;
use app\common\model\Config;

class ConfigService extends CommonService {

    CONST CACHE_KEY = 'CONFIG_LIST';

    public static function edit($data) {
       foreach($data as $key=>$value){
           Config::where(['key'=>$key])->update(['value'=>$value]);
       }
       self::clearCache();
       return true;
    }

    public static function clearCache() {
        return cache(self::CACHE_KEY, null);
    }

    public static function getDbConfig() {
        if(false === ($data = cache(self::CACHE_KEY))){
            $data = Config::order('display_order desc')->column('value', 'key');
            cache(self::CACHE_KEY, $data);
        }
        return $data;
    }

    public static function getByKey($key) {
        $value = Config::where('key', $key)->value('value');
        return $value;
    }
}