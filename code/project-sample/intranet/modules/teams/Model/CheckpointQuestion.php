<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;

class CheckpointQuestion extends CoreModel
{
    protected $table = 'checkpoint_question';
    
    /*
     * key store cache
     */
    const KEY_CACHE = 'checkpoint_question';
    
    /**
     * Get questions list by category
     * 
     * @param int $categoryId
     * @return object list questions
     */
    public function getQuestionByCategory($categoryId)
    {
        if ($question = CacheHelper::get(self::KEY_CACHE, $categoryId)) {
            return $question;
        }
        $question = self::where('category_id', $categoryId)->orderBy('sort_order', 'asc')->get();
        CacheHelper::put(self::KEY_CACHE, $question, $categoryId);
        return $question;
    }
}
