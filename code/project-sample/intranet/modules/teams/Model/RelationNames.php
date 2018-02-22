<?php

namespace Rikkei\Team\Model;

class RelationNames extends \Rikkei\Core\Model\CoreModel
{
    protected $table = 'relation_names';
    const KEY_CACHE  = 'relation_names';
    const MAP_KEY_CACHE = 'map_relation_names';
    
    /**
     * get list all relationName ['value' => $value , 'label' => $label]
     * @return array
     */
    public static function toOptionRelationship()
    {
        if( ($data = \Rikkei\Core\View\CacheHelper::get(self::KEY_CACHE)) )
        {
            return $data;
        }
        $collection = self::select('id','name')->get();
        
        $return  = collect($collection)->map(function($model){
            return [
                'value' => $model->id,
                'label' => $model->name,
            ];
        });
        
        \Rikkei\Core\View\CacheHelper::put(self::KEY_CACHE, $return);
        
        return $return;
    }
    
    /**
     * get All relation name [key => value]
     * @return array
     * 
     */
    public static function getAll()
    {
        if( ($data = \Rikkei\Core\View\CacheHelper::get(self::MAP_KEY_CACHE)) )
        {
            return $data;
        }
        $collection = self::select('id','name')->get()->toArray();
        
        $arrs = [];
        
        foreach($collection as $item) {
            $arrs[ $item['id'] ] = $item['name'];
        }
        \Rikkei\Core\View\CacheHelper::put(self::MAP_KEY_CACHE, $arrs);
        
        return $arrs;
    }
}

