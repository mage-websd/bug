<?php

namespace Rikkei\Core\View;

class CookieCore
{
    /**
     * get cookie
     * 
     * @param string $key
     * @return string|array
     */
    public static function get($key = null)
    {
        if ($key === null) {
            return $_COOKIE;
        }
        if (isset($_COOKIE[$key])) {
            return $_COOKIE[$key];
        }
        return null;
    }
    
    /**
     * remove cookie
     * 
     * @param string $key
     * @return boolean
     */
    public static function forget($key)
    {
        if (isset($_COOKIE[$key])) {
            unset($_COOKIE[$key]);
            setcookie($key, null, 0, '/');
            return true;
        }
        return false;
    }
    
    /**
     * set cookie
     * 
     * @param type $name
     * @param type $value
     * @param type $expire
     * @return type
     */
    public static function set($name, $value, $expire = 1) 
    {
        return setcookie($name, $value, time() + $expire * 60 * 60 * 24, '/');
    }
    
    /**
     * set cookie raw url
     * 
     * @param string $name
     * @param type $value
     * @param float $expire
     */
    public static function setRaw($name, $value, $expire = 1) 
    {
        return setcookie(md5($name), serialize($value), time() + $expire * 60 * 60 * 24, '/');
    }
    
    /**
     * get cookie raw
     * 
     * @param type $key
     * @return type
     */
    public static function getRaw($key = null)
    {
        if ($key === null) {
            return $_COOKIE;
        }
        try {
            $key = md5($key);
            if (isset($_COOKIE[$key])) {
                return unserialize($_COOKIE[$key]);
            }
        } catch (\Exception $ex) {
            return null;
        }
        return null;
    }
    
    /**
     * remove cookie raw
     * 
     * @param string $key
     * @return boolean
     */
    public static function forgetRaw($key)
    {
        $key = md5($key);
        if (isset($_COOKIE[$key])) {
            unset($_COOKIE[$key]);
            setcookie($key, null, 0, '/');
            return true;
        }
        return false;
    }
    
    /**
     * remove cookie with prefix
     * 
     * @param string $key
     * @return boolean
     */
    public static function forgetPrefix($key)
    {
        if (!isset($_COOKIE) || !count($_COOKIE)) {
            return true;
        }
        foreach ($_COOKIE as $i => $v) {
            if (preg_match('/^'.$key.'/', $i)) {
                self::forget($i);
            }
        }
        return true;
    }
}

