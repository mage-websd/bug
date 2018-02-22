<?php

namespace Rikkei\Team\Model;

class EmployeeMilitary extends EmployeeItemRelate
{
    protected $table = 'employee_military';
    
    const SOLDIER_LEVEL_1 = 1;
    const SOLDIER_LEVEL_2 = 2;
    const SOLDIER_LEVEL_3 = 3;
    const SOLDIER_LEVEL_4 = 4;
    
    /**
     * get model by employeeId
     * @param int $employeeId
     * @return EmployeeMilitary Description
     */
    public static function getModelByEmplId($employeeId)
    {
        $thisTable = self::getTableName();
        $employeeTable = Employee::getTableName();
        
        $model = self::select(["{$thisTable}.*"])
            ->join("{$employeeTable}", "{$thisTable}.employee_id", "=", "{$employeeTable}.id")
            ->where("{$thisTable}.employee_id", "=", $employeeId)
            ->first();
        
        if( !$model ) {
            $model = new static;
            $model->employee_id = $employeeId;
        }
        
        return $model;
    }
    
    /**
     * get array value => label soldier level
     * @return array [[label => 'label' , value => 'value']]
     */
    public static function toOptionSoldierLevel()
    {
        return [
            [
              'label' => trans('team::profile.Sodier level1'),
              'value' => self::SOLDIER_LEVEL_1,
            ],
            [
              'label' => trans('team::profile.Sodier level2'),
              'value' => self::SOLDIER_LEVEL_2,
            ],
            [
              'label' => trans('team::profile.Sodier level3'),
              'value' => self::SOLDIER_LEVEL_3,
            ],
            [
              'label' => trans('team::profile.Sodier level4'),
              'value' => self::SOLDIER_LEVEL_4,
            ],
        ];
    }
}

