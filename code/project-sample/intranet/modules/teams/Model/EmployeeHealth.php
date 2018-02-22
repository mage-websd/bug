<?php

namespace Rikkei\Team\Model;
use Rikkei\Team\Model\Employee;

class EmployeeHealth extends EmployeeItemRelate
{

    protected $table = 'employee_health';

    const BLOOD_TYPE_B = 'B';
    const BLOOD_TYPE_O = 'O';
    const BLOOD_TYPE_A = 'A';
    const BLOOD_TYPE_AB = 'AB';

    /**
     * toOption blood type
     */
    public static function toOptionBloodType()
    {
        return [
            [
                'value' => self::BLOOD_TYPE_A,
                'label' => self::BLOOD_TYPE_A,
            ],
            [
                'value' => self::BLOOD_TYPE_AB,
                'label' => self::BLOOD_TYPE_AB,
            ],
            [
                'value' => self::BLOOD_TYPE_B,
                'label' => self::BLOOD_TYPE_B,
            ],
            [
                'value' => self::BLOOD_TYPE_O,
                'label' => self::BLOOD_TYPE_O,
            ],
        ];
    }
    
    
    /**
     * 
     * @param type $employeeId
     * @return EmployeeHealth
     */
    public static function getItemsFollowEmployee($employeeId = 0)
   {
        
        $thisTable = self::getTableName();
        $employeeTable = Employee::getTableName();
        $model = self::select([
                    "{$thisTable}.id",
                    "{$thisTable}.blood_type",
                    "{$thisTable}.height",
                    "{$thisTable}.weigth",
                    "{$thisTable}.health_status",
                    "{$thisTable}.health_note",
                    "{$thisTable}.ailment",
                    "{$thisTable}.is_disabled",
            ])
            ->join($employeeTable, "{$employeeTable}.id", '=', "{$thisTable}.employee_id")
            ->where("{$thisTable}.employee_id", $employeeId)
            ->first();
        if(!$model){
            return new static();
        }
        return $model;
    }
}
