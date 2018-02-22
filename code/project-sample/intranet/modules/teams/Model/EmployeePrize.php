<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePrize extends CoreModel
{
    use SoftDeletes;

    protected $tableName = 'employee_prizes';
    const KEY_CACHE = 'employee_prizes';
    
    /**
     * get gridview by EmployeeId
     *
     * @param int $employeeId
     */
    public static function gridViewByEmpl($employeeId)
    {
        $pager = Config::getPagerData(null, ['order' => 'updated_at', 'dir' => 'DESC']);
        $collections = self::select(['id', 'name', 'level', 'issue_date', 'expire_date'])
            ->where('employee_id', $employeeId)
            ->orderBy($pager['order'], $pager['dir'])
            ->orderBy('updated_at', 'desc');
        $collections = self::filterGrid($collections, [], null, 'like');
        $collections = self::pagerCollection($collections, $pager['limit'], $pager['page']);
        return $collections;
    }
    
    /**
     * get list prizes by employeeId
     * @param array $employeeId
     * @return collection Description
     */
    public static function getItemsByEmplyee($employee_id)
    {
        $thisTbl = self::getTableName();
        $empTbl = Employee::getTableName();
        $prizTbl= Prizes::getTableName(); 
        
        return self::select([
            "{$thisTbl}.*",
            "{$prizTbl}.name as name",
        ])
        ->join("{$prizTbl}", "{$prizTbl}.id", "=", "{$thisTbl}.prize_id")
        ->join("{$empTbl}", "{$thisTbl}.employee_id", "=", "{$empTbl}.id")
        ->where("{$thisTbl}.employee_id", $employee_id)
        ->get();
    }
}
