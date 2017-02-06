<?php
/**
 * 服务扩展-验证用户的密码是否正确
 * 
 * Author: snake
 * Date: 14-10-28
 * Time: 下午9:27
 * Denpend:
 */

class Author {
    /**
     * 检查用户的明文密码是否正确
     *
     * @param $password string 用户明文密码
     * @param $salt string 生成用户产生的6位随机字符
     * @param $passwordFromDB string 生成用户产生的
     * @return bool
     */
    public static function checkUserPasswork($password,$salt,$passwordFromDB){
        return md5(md5($password) . $salt) == $passwordFromDB;
    }

    /**
     * 生成验证字符串
     *
     * @param $uid string 用户UID
     * @param $timestamp string 用户生成字符串时的时间戳
     * @param string $authkey string 加密的串 默认为配置时的AUTH_KEY
     * @return string
     */
    public static function createKey($uid,$timestamp = '',$authkey = ''){
        if(empty($authkey)){
            $authkey = C('AUTH_KEY');
        }

        if(empty($timestamp)){
            $timestamp = time();
        }

        return md5($uid . $timestamp . $authkey);
    }

    /**
     * 验证用户的Key是否正确
     *
     * @param $uid string 用户UID
     * @param $timestamp string 用户时间戳
     * @param $key string 用户Key
     * @param string $authkey string 加密的串 默认为配置时的AUTH_KEY
     * @return bool
     */
    public static function checkKey($uid,$timestamp,$key,$authkey = ''){

        if(empty($authkey)){
            $authkey = C('AUTH_KEY');
        }
        return md5($uid . $timestamp . $authkey) == $key;
    }

    /**
     * 检查时间是否过期
     *
     * @param $timestamp int 用户传过来的时间戳
     * @param string $authtimeout int 过期的时间长度 单位 秒
     * @return bool
     */
    public static function checkKeyTimeOut($timestamp,$authtimeout = ''){
        if(empty($authtimeout)){
            $authtimeout = C('AUTH_TIMEOUT');
        }

        return time() - $timestamp <= $authtimeout;
    }
}