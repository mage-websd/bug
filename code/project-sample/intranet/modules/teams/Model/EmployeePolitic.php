<?php
namespace Rikkei\Team\Model;

class EmployeePolitic extends EmployeeItemRelate
{
    protected $table = 'employee_politic';
    
    public static function getEmpById($employeeId)
    {
        $tableName = self::getTableName();
        $employeeTable = Employee::getTableName();
        
        $model = self::select([
                    "{$tableName}.id",
                    "{$tableName}.employee_id",
                    "{$tableName}.is_party_member",
                    "{$tableName}.party_join_date",
                    "{$tableName}.party_position",
                    "{$tableName}.party_join_place",
                    "{$tableName}.is_union_member",
                    "{$tableName}.union_join_date",
                    "{$tableName}.union_poisition",
                    "{$tableName}.union_join_place",
                ])
                ->join($employeeTable, "{$employeeTable}.id", "=", "{$tableName}.employee_id")
                ->where("{$tableName}.employee_id", "=", $employeeId)
                ->first();
        
        if(!$model) {
            $model = new static;
        }
        
        return $model;
    }
}

