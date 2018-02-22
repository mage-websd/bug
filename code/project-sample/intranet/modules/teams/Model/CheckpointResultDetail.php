<?php

namespace Rikkei\Team\Model;

use Illuminate\Database\Eloquent\Model;
use Rikkei\Core\View\CacheHelper;
use DB;

class CheckpointResultDetail extends Model
{
    protected $table = 'checkpoint_result_detail';
    
    public $timestamps = false;
    
    /*
     * key store cache
     */
    const KEY_CACHE = 'checkpoint_result_detail';
    
    /**
     * Insert into table css_result_detail
     * @param array $data
     */
    public function insert($resultId, $arrQuestion){
        try {
            if (count($arrQuestion)) {
                $countQuestion = count($arrQuestion); 
                for($i=0; $i<$countQuestion; $i++){
                    $detail = new CheckpointResultDetail();
                    $detail->result_id = $resultId;
                    $detail->question_id = $arrQuestion[$i][0];
                    $detail->point = $arrQuestion[$i][1];
                    $detail->comment = $arrQuestion[$i][2];
                    
                    $detail->save();
                 }
            }
        } catch (Exception $ex) {
            throw $ex;
        }
        
    }
    
    public static function getResultDetail($resultId) {
        return self::where('result_id', $resultId)->select('*')->get();
    }
    
    public static function getDetail($resultId, $questionId) {
        if ($result = CacheHelper::get(self::KEY_CACHE, $resultId.'_'.$questionId)) {
            return $result;
        }
        $result = self::where('result_id', $resultId)->where('question_id', $questionId)
                ->select('*')->first();
        CacheHelper::put(self::KEY_CACHE, $result, $resultId.'_'.$questionId);
        return $result;
    }
    
    public static function updateDetail($resultId, $arrQuestion) {
        DB::beginTransaction();
        try {
            if (count($arrQuestion)) {
                $countQuestion = count($arrQuestion); 
                for($i=0; $i<$countQuestion; $i++){
                    self::where(
                            [
                                'result_id' => $resultId,
                                'question_id' => $arrQuestion[$i][0],
                            ]
                        )
                        ->update(
                            [
                               'leader_point' => $arrQuestion[$i][1],
                               'leader_comment' => $arrQuestion[$i][2],
                            ]
                        );
                    
                    // Clear cache
                    CacheHelper::forget(self::KEY_CACHE, $resultId.'_'.$arrQuestion[$i][0]);
                }
                DB::commit();
            }
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
