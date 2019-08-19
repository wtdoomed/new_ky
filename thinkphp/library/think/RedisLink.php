<?php
/**
 * 单例模式连接redis
 * Created by PhpStorm.
 * User: wt
 * Date: 18/05/01
 * Time: 下午1:30
 */
namespace think;
/**
 * 连接redis类
 */
class RedisLink
{
    private static $obj;

    private function __construct(){
    }

    public static function get_instance(){
        if(!isset(self::$obj)){
            $redis = new \Redis();
            $redis->connect('127.0.0.1', 6379);
            self::$obj = $redis;
        }
        return self::$obj;
    }

    private function __clone(){
    }
}

