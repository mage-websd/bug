<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;

class PartyPosition extends CoreModel
{
    protected  $table = 'party_position';
    const KEY_CACHE = 'party_position';
    
    /**
     * get lib Position party
     * @return array
     */
    public static function getAll()
    {
        $return  = CacheHelper::get(self::KEY_CACHE);
        if(! $return ) {
            $collection = self::select(['id', 'name'])
                   ->where('state', '=', 1)
                   ->get();
           $return = collect($collection)->map(function($ele){ 
               return [
                   'value' => $ele->id,
                   'label' => $ele->name,
               ];      
           })->toArray();
           CacheHelper::put(self::KEY_CACHE, $return);
        }
        
        return $return;
    }
    
    public function save(array $options = array())
    {
        CacheHelper::forget(self::KEY_CACHE);
        return parent::save($options);
    }
}

