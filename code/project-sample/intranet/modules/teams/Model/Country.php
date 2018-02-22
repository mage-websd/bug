<?php

namespace Rikkei\Team\Model;

class Country extends \Rikkei\Core\Model\CoreModel
{
    protected $table = 'lib_country';
    const KEY_CACHE = 'lib_country';
    
    /**
     * getLibCountry
     * return array ['country_code' => 'name']
     */
    public static function getAll()
    {
        $datas = \Rikkei\Core\View\CacheHelper::get(self::KEY_CACHE);
        
        if(count($datas)) {
            return $datas;
        }
        
        $collection = self::select('country_code','name')->orderBy('country_code', 'ASC')->get()->toArray();
        
        foreach($collection as $item) {
            $datas[ $item['country_code'] ] = $item['name'];
        }
        
        \Rikkei\Core\View\CacheHelper::put(self::KEY_CACHE, $datas);
        
        return $datas;
    }
}
