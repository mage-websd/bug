<?php

namespace Rikkei\Team\Model;
use Rikkei\Team\Model\Employee;

class EmployeeCostume extends EmployeeItemRelate {
    
    const ASIA_SIZE_L = 'L';
    const ASIA_SIZE_M = 'M';
    const ASIA_SIZE_S = 'S';
    const ASIA_SIZE_XL = 'XL';
    const ASIA_SIZE_XS = 'XS';
    const ASIA_SIZE_XXL = 'XXL';
    
    const EUROPE_MAX_SIZE = 42;
    const EUROPE_MIN_SIZE = 32;
    
    protected $table = 'employee_costume';
    
    /**
     * get EmployeeCostume model by employeeId
     * @param type $employeeId
     * @return EmployeeHobby
     */
    public static function getEmpById($employeeId)
    {
        $thisTable = self::getTableName();
        $employeeTable = Employee::getTableName();
        
        $model = self::select("{$thisTable}.*")
            ->join($employeeTable, "{$employeeTable}.id", "=", "{$thisTable}.employee_id")
            ->where("{$thisTable}.employee_id", "=", $employeeId)
            ->first();
            
        if( !$model ) {
            $model = new static;
            $model->employee_id = $employeeId;
        }
        return $model;
    }
    
    
    /**
     * get options asia size
     * @return array Description
     */
    public static function toOptionsAsiaSize()
    {
        return [
            [
                'value' => self::ASIA_SIZE_L,
                'label' => self::ASIA_SIZE_L,
            ],
            [
                'value' => self::ASIA_SIZE_M,
                'label' => self::ASIA_SIZE_M,
            ],
            [
                'value' => self::ASIA_SIZE_S,
                'label' => self::ASIA_SIZE_S,
            ],
            [
                'value' => self::ASIA_SIZE_XL,
                'label' => self::ASIA_SIZE_XL,
            ],
            [
                'value' => self::ASIA_SIZE_XS,
                'label' => self::ASIA_SIZE_XS,
            ],
            [
                'value' => self::ASIA_SIZE_XXL,
                'label' => self::ASIA_SIZE_XXL,
            ],
        ];
    }
    
    /**
     * get options europe size;
     * @return array range from min size to max size
     */
    public static function toOptionsEuropeSize()
    {
        return range(self::EUROPE_MIN_SIZE, self::EUROPE_MAX_SIZE);
    }
}
