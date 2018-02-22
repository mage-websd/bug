<?php

namespace Rikkei\Team\Model;
use Rikkei\Team\Model\Employee;

class EmployeeHobby extends EmployeeItemRelate
{
    protected $table = 'employee_hobby';

    /**
     * get EmployeeHobby model by employeeId
     * @param type $employeeId
     * @return EmployeeHobby Description
     */
    public static function getItemFollowEmployee($employeeId) {
        $thisTable = self::getTableName();
        $employeeTable = Employee::getTableName();
        
        $model = self::select("{$thisTable}.*")
            ->join($employeeTable, "{$employeeTable}.id", "=", "{$thisTable}.employee_id")
            ->where("{$thisTable}.employee_id", "=", $employeeId)
            ->first();
            
        if( !$model ) {
            $model = new static;
        }
        return $model;
    }
}
