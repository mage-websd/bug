<?php

namespace Rikkei\Team\Model;

use Illuminate\Database\Eloquent\Model;
use Rikkei\Core\View\CacheHelper;
use DB;
use Rikkei\Team\View\Permission as TeamPermission;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\Model\CoreModel;

class Checkpoint extends CoreModel
{
    protected $table = 'checkpoint';
    
    /*
     * key store cache
     */
    const KEY_CACHE = 'checkpoint';
    
    /*
     * Total choice / question
     */
    const TOTAL_CHOICE = 4;
    
    /**
     * Get checkpoint by checkpoint_id and toke
     * 
     * @param int $id
     * @param string $token
     * @return object checkpoint
     */
    public function getCheckpointByIdAndToken($id,$token)
    {
        if ($checkpoint = CacheHelper::get(self::KEY_CACHE, $id . '_' . $token)) {
            return $checkpoint;
        }
        $checkpoint = self::where('id', $id)->where('token', $token)->first();
        CacheHelper::put(self::KEY_CACHE, $checkpoint, $id . '_' . $token);
        return $checkpoint;
    }
    
    /**
     * Get checkpoint by checkpoint_id and toke
     * 
     * @param int $id
     * @param string $token
     * @return object checkpoint
     */
    public static function getCheckpointById($id)
    {
        if ($checkpoint = CacheHelper::get(self::KEY_CACHE, $id)) {
            return $checkpoint;
        }
        $checkpoint = self::find($id);
        CacheHelper::put(self::KEY_CACHE, $checkpoint, $id);
        return $checkpoint;
    }
    
    /**
     * Get checkpoint list by permission
     * 
     * @param string $order
     * @param string $dir
     * @param ing|null $empId
     */
    public function getList($order, $dir, $empId = null, $teamIds = null) 
    {
        $collection = self::join('checkpoint_type', 'checkpoint_type.id', '=', 'checkpoint.checkpoint_type_id')
                ->leftJoin('checkpoint_time', 'checkpoint_time.id', '=', 'checkpoint.checkpoint_time_id')
                ->leftJoin('employees','employees.id','=','checkpoint.employee_id')
                ->join('teams','teams.id','=','checkpoint.team_id');
                
        $emp = TeamPermission::getInstance()->getEmployee();
        
        //self permission
        if ($empId) {
            $collection->where(function($query) use ($emp) {
                $query->orWhereRaw('FIND_IN_SET("'.$emp->id.'",checkpoint.evaluator_id)')
                    ->orWhereRaw('FIND_IN_SET("'.$emp->email.'",checkpoint.rikker_relate)')
                    ->orWhere('checkpoint.employee_id', $emp->id);
            });
        }
        
        //team permission
        if ($teamIds) {
            $collection->where(function($query) use ($emp, $teamIds) {
                $query->whereIn('checkpoint.team_id',$teamIds)
                    ->orWhereRaw('FIND_IN_SET("'.$emp->email.'",checkpoint.rikker_relate)')
                    ->orWhereRaw('FIND_IN_SET("'.$emp->id.'",checkpoint.evaluator_id)')
                    ->orWhere('checkpoint.employee_id', $emp->id);
            });
        }
        
        $collection->orderBy($order,$dir)
                    ->groupBy('checkpoint.id');
        
        $collection->select('checkpoint.*',
                    'checkpoint_type.name as checkpoint_type_name',
                    'checkpoint_time.check_time',
                    'employees.email as creator',
                    'teams.name as team_name',
                    DB::raw(
                        '(select COUNT(checkpoint_result.id) from checkpoint_result where checkpoint_id = checkpoint.id) as count_make')
                );
        
        return $collection;
    }
    
    public static function getListSelf() {
        $checkpointTable = self::getTableName();
        $resultTable = CheckpointResult::getTableName();
        $pager = Config::getPagerData();
        $pagerFilter = (array) Form::getFilterPagerData();
        $pagerFilter = array_filter($pagerFilter);
        if ($pagerFilter) {
            $order = $pager['order'];
            $dir = $pager['dir'];
        } else {
            $order = "{$checkpointTable}.created_at";
            $dir = 'desc';
        }
        
        $collection = self::join('checkpoint_type', 'checkpoint_type.id', '=', 'checkpoint.checkpoint_type_id')
                ->leftJoin('checkpoint_time', 'checkpoint_time.id', '=', 'checkpoint.checkpoint_time_id')
                ->leftJoin('employees','employees.id','=','checkpoint.employee_id')
                ->join('teams','teams.id','=','checkpoint.team_id');
                
        $emp = TeamPermission::getInstance()->getEmployee();
        $collection->whereRaw('FIND_IN_SET("'.$emp->id.'",checkpoint.evaluated_id)');
        
        $collection->orderBy($order,$dir)
                    ->groupBy('checkpoint.id');
        
        $collection->select('checkpoint.*',
                    'checkpoint_type.name as checkpoint_type_name',
                    'checkpoint_time.check_time',
                    'employees.email as creator',
                    'teams.name as team_name',
                    DB::raw(
                        '(select COUNT(checkpoint_result.id) from checkpoint_result where checkpoint_id = checkpoint.id and employee_id = '.$emp->id.') as count_make'),
                    DB::raw(
                        '(select checkpoint_result.created_at from checkpoint_result where checkpoint_id = checkpoint.id and employee_id = '.$emp->id.') as result_date') ,
                    DB::raw(
                        '(select checkpoint_result.id from checkpoint_result where checkpoint_id = checkpoint.id and employee_id = '.$emp->id.') as result_id')      
                );
        
        $collection = CoreModel::filterGrid($collection);
        $collection = CoreModel::pagerCollection($collection, $pager['limit'], $pager['page']);
        
        return $collection;
    }

        /**
     * Clear all Checkpoint
     * @throws \Rikkei\Team\Model\Exception
     */
    public static function clearAll() {
        DB::beginTransaction();
        try {
            DB::table("checkpoint_result_detail")->delete();
            DB::table("checkpoint_result")->delete();
            DB::table("checkpoint")->delete();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
