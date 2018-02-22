<?php

namespace Rikkei\Team\Model;

class EmployeeWorkExperienceJapan extends EmployeeItemRelate
{
    protected $table = 'work_experience_japan';
    const KEY_CACHE = 'work_experience_japan';
    
    /**
     * get list items japan work by EmployeeId
     * @param type $employeeId
     * @return object 
     */
    public static function getItemsFollowEmployee($employeeId)
    {
        $collection = self::select('want_to_japan', 'note', 'from', 'to')
            ->where('employee_id', $employeeId)
            ->orderBy('updated_at');
        return $collection->first();
    } 
}
