<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;

class EmployeeDocExpire extends CoreModel 
{
    protected $table = 'employee_doc_expire';
    const KEY_CACHE = 'employee_doc_expire';
    
    /**
     * get DocExpire by EmployeeId
     * @param int $employeeId
     */
    public static function getGridItemByEmployee($employeeId)
    {
        
        $thisTable = self::getTableName();
        $employeeTbl = Employee::getTableName();
        $collection = self::select([
            "{$thisTable}.id",
            "{$thisTable}.name",
            "{$thisTable}.place",
            "{$thisTable}.issue_date",
            "{$thisTable}.expired_date",
        ])->join("{$employeeTbl}", "{$thisTable}.employee_id", "=", "{$employeeTbl}.id")
        ->where("{$thisTable}.employee_id", $employeeId);
        
        $pager = Config::getPagerData(null, ['order' => "{$thisTable}.updated_at", 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir'])->orderBy("{$thisTable}.updated_at", 'ASC');
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        
        return $collection;
    }
}
