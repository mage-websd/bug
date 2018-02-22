<?php

namespace Rikkei\Core\View;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    /**
     * time store data into cache
     * 
     * @var int
     */
    protected static $timeStoreCache = 10080; //time store cache is 1 week
    
    /**
     * get data cache with key cache prefix
     * 
     * @param string $key
     * @param null|string $suffixKey
     * @param boolean $argsKey
     * @return type|null
     */
    public static function get($key, $suffixKey = null, $argsKey = true)
    {
        $keyData = self::initKeyCacheData($argsKey);
        $dataExists = Cache::get($key);
        if ($suffixKey) {
            if ($dataExists && isset($dataExists[$suffixKey][$keyData]) && $dataExists[$suffixKey][$keyData]) {
                return $dataExists[$suffixKey][$keyData];
            }
        } else {
            if ($dataExists && isset($dataExists[$keyData]) && $dataExists[$keyData]) {
                return $dataExists[$keyData];
            }
        }
        
        return null;
    }
    
    /**
     * put data into cache with key cache prefix
     * 
     * @param string $key
     * @param type $data
     * @param null|string $suffixKey
     * @param boolean $argsKey
     */
    public static function put($key, $data, $suffixKey = null, $argsKey = true)
    {
        $keyData = self::initKeyCacheData($argsKey);
        $dataExists = Cache::get($key);
        if (! $dataExists) {
            $dataExists = [];
        }
        if ($suffixKey) {
            $dataExists[$suffixKey][$keyData] = $data;
        } else {
            $dataExists[$keyData] = $data;
        }
        
        Cache::put($key, $dataExists, self::$timeStoreCache);
    }
    
    /**
     * get data cache
     * 
     * @param type $key
     * @return type
     */
    public static function getGroup($key)
    {
        return Cache::get($key);
    }
    
    /**
     * remove cache key
     *
     * @param string $key
     * @param null|string $suffixKey
     */
    public static function forget($key, $suffixKey = null)
    {
        if ($suffixKey) {
            $dataExists = Cache::get($key);
            if (! $dataExists) {
                return;
            }
            if (isset($dataExists[$suffixKey])) {
                unset($dataExists[$suffixKey]);
            }
            Cache::put($key, $dataExists, self::$timeStoreCache);
            return;
        }
        Cache::forget($key);
    }
    
    /**
     * check has cache key
     *
     * @param string $key
     * @param null|string $suffixKey
     */
    public static function has($key, $suffixKey = null)
    {
        if (!Cache::has($key)) {
            return false;
        }
        if (!$suffixKey) {
            return true;
        }
        $dataExists = Cache::get($key);
        if (isset($dataExists[$suffixKey]) && $dataExists[$suffixKey]) {
            return true;
        }
        return false;
    }
    
    /**
     * remove all cache
     */
    public static function flush()
    {
        Cache::flush();
    }


    /**
     * get key follow function and class called
     * 
     * @param boolean $argsKey
     * @return string
     */
    public static function initKeyCacheData($argsKey = true)
    {
        $dataCalled = debug_backtrace();
        if (isset($dataCalled[2])) {
            $dataCalled = $dataCalled[2];
        } elseif (isset($dataCalled[1])) {
            $dataCalled = $dataCalled[1];
        } else {
            $dataCalled = $dataCalled[0];
        }
        $key = '';
        if (isset($dataCalled['class'])) {
            $key .= $dataCalled['class'] . '-c-';
        }
        if (isset($dataCalled['function'])) {
            $key .= $dataCalled['function'] . '-f-';
        }
        if ($argsKey && isset($dataCalled['args']) && $dataCalled['args']) {
            foreach ($dataCalled['args'] as $args) {
                if (is_numeric($args) || is_string($args) || is_array($args)) {
                    $key .= var_export($args, true) . '-';
                }
            }
            $key .= 'ar-';
        }
        return $key;
    }
}
