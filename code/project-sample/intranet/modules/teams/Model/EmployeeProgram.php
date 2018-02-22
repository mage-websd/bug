<?php
namespace Rikkei\Team\Model;

use DB;
use Rikkei\Resource\Model\Programs;

class EmployeeProgram extends \Rikkei\Core\Model\CoreModel
{
    protected $table = 'employee_programs';
    
    /*
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'employee_id', 'programming_id', 'level', 'experience', 'created_at', 'updated_at'
    ];
    
    /**
     * Save data
     * 
     * @param array $data
     * @throws \Rikkei\Team\Model\Exception
     */
    public static function saveData($data) {
        DB::beginTransaction();
        try {
            self::insert($data);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    public static function getProgramsOfEmp($empId) {
        $programTable = Programs::getTableName();
        $EmpProTable = self::getTableName();
        $result = self::join("{$programTable}", "{$programTable}.id", "=", "{$EmpProTable}.programming_id")
                    ->where("{$EmpProTable}.employee_id", $empId)
                    ->select([
                        "{$programTable}.id", 
                        "{$programTable}.name",
                        "{$EmpProTable}.level",
                        "{$EmpProTable}.experience"
                    ])
                    ->get();
        return $result;
    }
}
