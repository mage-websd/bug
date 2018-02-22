<?php

namespace Rikkei\Team\Model;
use Rikkei\Core\Model\CoreModel;

class QualityEducation extends CoreModel
{
    protected $table = 'quality_education';
    const KEY_CACHE = 'quality_education';
    
    /**
     * list qualityEducation
     * @return array [id => name]
     */
    public static function getAll()
    {
        $data = \Rikkei\Core\View\CacheHelper::get(self::KEY_CACHE);
        
        if(count($data)) {
            return $data;
        }
        
        $collection = self::select('id','name')
                    ->orderBy('id', 'ASC')
                    ->orderBy('updated_at', 'ASC')
                    ->get()
                    ->toArray();
        
        foreach($collection as $item) {
            $data[$item['id']]  = $item['name'];
        }
        
        \Rikkei\Core\View\CacheHelper::put(self::KEY_CACHE, $data);
        
        return $data;
    }
}

