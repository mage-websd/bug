<?php
namespace Rikkei\Team\Model;

use Illuminate\Support\Facades\Validator;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;

class EmployeeSchool extends CoreModel
{
    const KEY_CACHE = 'employee_school';

    protected $table = 'employee_schools';
    public $timestamps = false;

    /**
     * education type
     */
    const EDU_TYPE_FORMAL = 1;
    const EDU_TYPE_SPECIAL = 2;
    const EDU_TYPE_COMMUNICATE = 3;
    const EDU_TYPE_IN_OFFICE = 4;
    
    
    /**
     * education degree level
     */
    const EDU_DEG_GREAT = 1;
    const EDU_DEG_MIDDLE = 2;
    const EDU_DEG_ABOVE_AVERAGE = 3;
    const EDU_DEG_MEDIUM = 4;
    const EDU_DEG_EXCELLENT = 5;

    /**
     * save employee school
     * 
     * @param int $employeeId
     * @param array $schoolIds
     * @param array $schools
     */
    public static function saveItems($employeeId, $schoolIds= [], $schools = [])
    {
        self::where('employee_id', $employeeId)->delete();
        if (! $schoolIds || ! $schools || ! $employeeId) {
            return;
        }
        $schoolIdsAdded = [];
        foreach ($schoolIds as $key => $schoolId) {
            if (! isset($schools[$key]) || ! $schools[$key]['employee_school'] || in_array($schoolId, $schoolIdsAdded)) {
                continue;
            }
            $employeeSchoolData = $schools[$key]['employee_school'];
            if(isset($employeeSchoolData['is_graduated']) && ($employeeSchoolData['is_graduated'] == 'on' || $employeeSchoolData['is_graduated'] == 1)) {
                $employeeSchoolData['is_graduated'] = 1;
                $employeeSchoolData['awarded_date'] = trim($employeeSchoolData['awarded_date']) ? trim($employeeSchoolData['awarded_date']) : null;
            } else {
                $employeeSchoolData['is_graduated'] = 0;
                $employeeSchoolData['awarded_date'] = null;
            }
            $validator = Validator::
            $validator = Validator::make($employeeSchoolData, [
                'majors' => 'required|max:255',
                'start_at' => 'required|max:255',
            ]);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->send();
            }
            if (! $employeeSchoolData['end_at']) {
                unset($employeeSchoolData['end_at']);
            }
            $employeeSchoolItem = new self();
            $employeeSchoolItem->setData($employeeSchoolData);
            $employeeSchoolItem->school_id = $schoolId;
            $employeeSchoolItem->employee_id = $employeeId;
            $employeeSchoolItem->updated_at = date('Y-m-d H:i:s');
            $employeeSchoolItem->save();
            $schoolIdsAdded[] = $schoolId;
        }
    }
    
    /**
     * get employee school follow employee id
     * 
     * @param type $employeeId
     * @return object model
     */
    public static function getItemsFollowEmployee($employeeId)
    {
        $thisTable = self::getTableName();
        $schoolTable = School::getTableName();
        
        return self::select('school_id', 'start_at', 'end_at', 'majors', "{$schoolTable}.name as school",
                'name', 'country', 'province', 'image', 'id','quality', 'degree', 'type', 'faculty', 'is_graduated', 'awarded_date', 'note')
            ->join($schoolTable, "{$schoolTable}.id", '=', "{$thisTable}.school_id")
            ->where('employee_id', $employeeId)
            ->get();
    }

    /**
     * get list employeeScholl by employee for gridView
     * @param type $employeeId
     * @return Collection
     */
    public static function getListItemsFollowEmployee($employeeId)
    {
        $thisTbl = self::getTableName();
        $thisTable = self::getTableName();
        $schoolTable = School::getTableName();
        
        $collection = self::select('school_id', 'start_at', 'end_at', 'majors', 
                'name', 'country', 'province', 'image', 'id' ,'quality', 'degree', 'type', 'faculty', 'is_graduated', 'awarded_date', 'note')
            ->join($schoolTable, "{$schoolTable}.id", '=', "{$thisTable}.school_id")
            ->where('employee_id', $employeeId);
        
        $pager = Config::getPagerData(null, ['order' => "{$thisTbl}.updated_at", 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir'])->orderBy("{$thisTbl}.updated_at", 'ASC');
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /**
     * get list eduType
     * @return array
     */
    public static function listEduType()
    {
        return [
            self::EDU_TYPE_FORMAL       => trans('team::profile.Formal'),
            self::EDU_TYPE_SPECIAL      => trans('team::profile.Special'),
            self::EDU_TYPE_COMMUNICATE  => trans('team::profile.Communicate'),
            self::EDU_TYPE_IN_OFFICE    => trans('team::profile.In office'),
        ];
    }
    
    /**
     * getList degree level
     * @return array Description
     */
    public static function listDegree()
    {
        return [
            self::EDU_DEG_GREAT     => trans('team::profile.Great'),
            self::EDU_DEG_MIDDLE    => trans('team::profile.Middle'),
            self::EDU_DEG_ABOVE_AVERAGE => trans('team::profile.Above aveage'),
            self::EDU_DEG_MEDIUM    => trans('team::profile.Medium'),
            self::EDU_DEG_EXCELLENT => trans('team::profile.Excellent'),
        ];
    }
}
