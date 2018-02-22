<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;

class EmployeeAttachment extends CoreModel
{
    protected $table = 'employee_attachment';
    const KEY_CACHE = 'esmployee_attachment';
    
    /**
     * get Collection by employeeId
     * @param int $employeeId
     * @return $collection
     */
    public static function getGridByEmployee($employeeId)
    {
        $thisTbl = self::getTableName();
        $empTbl = Employee::getTableName();
        $collections = self::select([
                "{$thisTbl}.id",
                "{$thisTbl}.file_name",
                "{$thisTbl}.template_name",
                "{$thisTbl}.file_size",
                "{$thisTbl}.note",
                ])
                ->leftJoin($empTbl,"{$thisTbl}.employee_id", "=", "{$empTbl}.id")
                ->where("{$thisTbl}.employee_id",$employeeId);
                
        $pager = Config::getPagerData(null, ['order' => "{$thisTbl}.updated_at", 'dir' => 'DESC']);
        $collections = $collections->orderBy($pager['order'], $pager['dir'])->orderBy("{$thisTbl}.updated_at", 'ASC');
        $collections = self::filterGrid($collections);
        $collections = self::pagerCollection($collections, $pager['limit'], $pager['page']);
        
        return $collections;
    }
}
