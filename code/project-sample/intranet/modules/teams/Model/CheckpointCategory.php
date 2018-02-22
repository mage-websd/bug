<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\Model\CoreModel;

class CheckpointCategory extends CoreModel
{
    protected $table = 'checkpoint_category';
    
    /*
     * key store cache
     */
    const KEY_CACHE = 'checkpoint_category';
    const KEY_CACHE_BY_CHECKPOINT_CATE = 'checkpoint_type';
    const KEY_CACHE_PARENT = 'checkpoint_category_parent';
    
    /**
     * Get root category by checkpoint_type_id
     * 
     * @param int $checkTypeId
     * @return list object checkpoint_category
     */
    public function getRootCategory($checkTypeId)
    {
        if ($cate = CacheHelper::get(self::KEY_CACHE_BY_CHECKPOINT_CATE, $checkTypeId)) {
            return $cate;
        }
        $cate = self::where("parent_id",0)->where('checkpoint_type_id',$checkTypeId)->first();
        CacheHelper::put(self::KEY_CACHE_BY_CHECKPOINT_CATE, $cate, $checkTypeId);
        return $cate;
    }
    
    /**
     * Get checkpoint_category by parent_id
     * 
     * @param int $parentId
     * @return list object checkpoint_category 
     */
    public function getCategoryByParent($parentId)
    {
        if ($cate = CacheHelper::get(self::KEY_CACHE_PARENT, $parentId)) {
            return $cate;
        }
        $cate = self::where('parent_id', $parentId)->get();
        CacheHelper::put(self::KEY_CACHE_PARENT, $cate, $parentId);
        return $cate;
    }
}
