<?php

namespace Rikkei\Team\Model;

use Illuminate\Database\Eloquent\Model;
use Rikkei\Core\View\CacheHelper;
use DB;
use Rikkei\Team\View\CheckpointPermission;

class CheckpointResult extends \Rikkei\Core\Model\CoreModel
{
    protected $table = 'checkpoint_result';
    
    /*
     * key store cache
     */
    const KEY_CACHE = 'checkpoint_result';
    const KEY_CACHE_CHECKPOINT_ID = 'checkpoint_result_checkpoint_id';
    
    /**
     * Insert into table checkpoint_result
     * @param array $data
     */
    public function insert($data){
        DB::beginTransaction();
        try {
            $result = new CheckpointResult();
            $result->checkpoint_id = $data['checkpoint_id'];
            $result->total_point = $data['total_point'];
            $result->comment = $data['comment'];
            $result->employee_id = $data['employee_id'];
            $result->team_id = $data['team_id'];
            $result->save();        
            DB::commit();
            return $result->id;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
        
    }
    
    /**
     * Get Checkpoint by checkpoint_id
     * @param int $id checkpoint_id
     * @return Checkpoint
     */
    public static function getResultById($id) {
        if ($result = CacheHelper::get(self::KEY_CACHE, $id)) {
            return $result;
        }
        $result = self::find($id);
        CacheHelper::put(self::KEY_CACHE, $result, $id);
        return $result;
    }
    
    /**
     * Update checkpoint to database
     */
    public function updateResult() {
        DB::beginTransaction();
        try {
            $this->save();
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE, $this->id);
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * Get count 
     * 
     * @param int $checkpointId
     * @param int $empId
     */
    public static function checkMade($checkpointId, $empId) {
        return self::where(
                    [
                        'checkpoint_id' => $checkpointId,
                        'employee_id'   => $empId
                    ]
                )->count();
        
    }
    
    public static function getOnlyResult($checkpointId) {
        if ($result = CacheHelper::get(self::KEY_CACHE_CHECKPOINT_ID, $checkpointId)) {
            return $result;
        }
        $result = self::where('checkpoint_id', $checkpointId)->first();
        CacheHelper::put(self::KEY_CACHE_CHECKPOINT_ID, $result, $checkpointId);
        return $result;
    }
    
    /**
     * Display checkpoint result list 
     * @param int $checkpointId
     * @param string $order
     * @param string $dir
     * @param int $evaluatorId
     * @return object list css result
     */
    public static function getResultByCheckpointId($checkpointId, $order, $dir, $evaluatorId = null) {
        $tableResult = self::getTableName();
        $checkpoint = Checkpoint::getCheckpointById($checkpointId);

        $result = Employee::leftJoin(
                    DB::raw("(SELECT checkpoint_result.*, employees.id as emp_id "
                            . "FROM employees inner join checkpoint_result ON employees.id = checkpoint_result.employee_id "
                            . "WHERE checkpoint_result.checkpoint_id = $checkpointId "
                            . "GROUP BY checkpoint_result.id) AS tableResult")
                    , 'employees.id', '=', 'tableResult.emp_id')
                ->whereRaw('employees.id in (select team_members.employee_id from team_members join employees on team_members.employee_id = employees.id WHERE (employees.leave_date is null or employees.leave_date > ?) AND team_members.team_id = ?)', 
                    [$checkpoint->start_date,$checkpoint->team_id])
                ->orWhereRaw('employees.id in (select checkpoint_result.employee_id from checkpoint_result where checkpoint_result.checkpoint_id = ? AND checkpoint_result.team_id =?)', [$checkpoint->id, $checkpoint->team_id])
                ->select('tableResult.*', 'employees.name as emp_name', 'employees.id as emp_id' );
        // If current user login is evaluator
//        if ($evaluatorId) {
//            $checkpoint = Checkpoint::getCheckpointById($checkpointId);
//            $evaluated = CheckpointPermission::getInstance()->getEvaluatedByEvaluator($checkpoint->evaluators, $evaluatorId);
//            $result->whereIn('employees.id', $evaluated);
//        }
        $result->orderBy($order, $dir);
        return $result;
    }
     
    /**
     * Check employee made checkpoint. 
     * @param int $checkpointId
     * @param int $empId
     * @return int 
     */
    public static function checkExist($checkpointId, $empId) {
        return self::where('checkpoint_id', $checkpointId)
                      ->where('employee_id', $empId)
                      ->count();
    }
}
