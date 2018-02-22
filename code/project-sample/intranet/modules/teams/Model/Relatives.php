<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;

class Relatives extends CoreModel
{
    protected $table = 'relatives';
    
    /**
     * return Collection
     * @param int $employeeId
     */
    public static function getListRelativesByEmpId($employeeId)
    {
        $thisTbl = self::getTableName();
        $emplRelaTbl = EmployeeRelatives::getTableName();
        $empTbl = Employee::getTableName();
        
        $collection = self::select([
            "{$thisTbl}.id",
            "{$thisTbl}.name",
            "{$thisTbl}.relationship",
            "{$thisTbl}.date_of_birth",
            "{$thisTbl}.mobile",
            "{$thisTbl}.career",
            "{$thisTbl}.is_dependent",
            "{$thisTbl}.only_input_year",
        ])
        ->join("{$emplRelaTbl} as er", "er.relative_id", "=", "{$thisTbl}.id")
        ->join("{$empTbl} as e", "er.employee_id", "=", "e.id")
        ->where("e.id", $employeeId);
        
        $pager = Config::getPagerData(null, ['order' => "{$thisTbl}.created_at", 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir'])->orderBy("{$thisTbl}.updated_at", 'ASC');
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;    
    }
}

