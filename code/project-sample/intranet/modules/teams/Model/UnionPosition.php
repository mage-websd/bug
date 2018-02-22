<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;

class UnionPosition extends CoreModel
{
    protected $table = 'union_position';
    
    const KEY_CACHE = 'union_position';
    
    /**
     * get All list UnionPosition
     * @return array Description
     */
    public static function getAll()
    {
        $arrs = CacheHelper::get(self::KEY_CACHE);
        if(! $arrs ) {
            $collection = self::select(['id','name'])
                        ->where('state', '=', 1)
                        ->get();
            $arrs = collect($collection)->map(function($ele) {
                return [
                    'value' => $ele->id,
                    'label' => $ele->name,
                ];
            })->toArray();
            CacheHelper::put(self::KEY_CACHE, $arrs);
        }
        
        return $arrs;
    }
    
    /**
     * remove cache when change value
     * @param array $options
     * @return type
     */
    public function save(array $options = array())
    {
        CacheHelper::forget(self::KEY_CACHE);
        return parent::save($options);
    }
}

