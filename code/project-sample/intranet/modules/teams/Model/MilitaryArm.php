<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;
use function collect;

class MilitaryArm extends CoreModel
{
    protected $table = 'military_arm';
    const KEY_CACHE = 'military_arm';
    
    /**
     * getAll data military_arm
     * @return array Description
     */
    public static function getAll()
    {
        $data  = CacheHelper::get(self::KEY_CACHE);
        if(! $data ) {
            $collection = self::select(['id', 'name'])
                   ->where('state', '=', 1)
                   ->get();
           $return = collect($collection)->map(function($ele){ 
               return [
                   'value' => $ele->id,
                   'label' => $ele->name,
               ];      
           })->toArray();
           CacheHelper::put(self::KEY_CACHE, $data);
        }
        
        return $return;
    }
    
    /**
     * overide save function
     * @param array $options
     */
    public function save(array $options = array()) {
        CacheHelper::forget(self::KEY_CACHE);
        return parent::save($options);
    }
}
