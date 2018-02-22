<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\DB;
use Rikkei\Resource\View\View;
use Rikkei\Team\Model\Employee;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Team\Model\Team;

class EmployeeTeamHistory extends CoreModel
{
    use SoftDeletes;

    protected $table = 'employee_team_history';

    /*
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'team_id', 'employee_id', 'start_at', 'end_at', 'created_at', 'updated_at'
    ];

    /**
     * Get list current team of employee
     *
     * @param int $employeeId
     * @return EmployeeTeamHistory
     */
    public static function getCurrentTeams ($employeeId)
    {
        return self::where('employee_id',$employeeId)
                ->whereNull('end_at')
                ->get();
    }

    public static function getCurrentByTeamEmployee($teamId, $employeeId)
    {
        return self::where('team_id', $teamId)
                ->where('employee_id', $employeeId)
                ->whereNull('end_at')
                ->first();
    }

    /**
     * Get count team's employee in month
     *
     * @param int $month
     * @param int $year
     * @param int|null $teamId
     * @param boolean $isTeamSoftDev
     * @return int
     */
    public static function getCountEmployeeOfMonth($month, $year, $teamId = null, $isTeamSoftDev = false)
    {
        $firstLastMonth = View::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $firstDay = $firstLastMonth[0];
        $lastDay = $firstLastMonth[1];
        $list = self::whereRaw("(DATE(start_at) <= DATE(?) or start_at is null) and (DATE(end_at) >= DATE(?) or end_at is null)", [$lastDay, $firstDay])
                    ->selectRaw('count(distinct employee_id) as count_emp')
                    ->leftJoin("employees", "employees.id", "=", "employee_team_history.employee_id")
                    ->whereRaw("DATE(employees.join_date) <= DATE(?)", [$lastDay]);
        if (!empty($teamId)) {
            $list->where('team_id', $teamId);
        }
        if ($isTeamSoftDev) {
            $teamTable = Team::getTableName();
            $employeeTeamHistoryTbl = self::getTableName();
            $list->leftJoin("{$teamTable}", "{$employeeTeamHistoryTbl}.team_id" , "=", "{$teamTable}.id")
                ->where("{$teamTable}.is_soft_dev", Team::IS_SOFT_DEVELOPMENT);
        }
        return $list->first()->count_emp;
    }

    /**
     * Get employees start or end in month
     *
     * @param int $month
     * @param int $year
     * @param int $teamId
     * @return EmployeeTeamHistory collection
     */
    public static function getEmpStartOrEndInMonth($month, $year, $teamId = null)
    {
        $emps = self::where(function ($query) use ($month, $year) {
            $query->whereRaw("MONTH(end_at) = ? AND YEAR(end_at) = ?", [$month, $year])
                    ->orWhereRaw("MONTH(start_at) = ? AND YEAR(start_at) = ?", [$month, $year]);
        });
        if ($teamId) {
            $emps->where('team_id', $teamId);
        }
        return $emps->get();
    }

    /**
     * Set end date work in team
     *
     * @param int $employeeId
     * @param date $date
     * @return void
     */
    public static function updateEndAt($employeeId, $date = null)
    {
        DB::beginTransaction();
        try {
            $empHistoryWithNullEndAt = self::where('employee_id', $employeeId)
                                            ->whereNull('end_at')->get();
            //if employee is working else employee leave job
            if (count($empHistoryWithNullEndAt)) {
                if ($date) {
                    self::where('employee_id', $employeeId)
                        ->whereNull('end_at')
                        ->update(['end_at' => $date]);
                }
            } else {
                $empHistoryWithMaxEndAt = DB::select("select * from employee_team_history join
                                    (select max(end_at) max_end_at from employee_team_history where employee_id = ?) as  emp_max_end on employee_team_history.end_at = emp_max_end.max_end_at
                                    where employee_id = ?", [$employeeId, $employeeId]
                                    );
                if (count($empHistoryWithMaxEndAt)) {
                    foreach ($empHistoryWithMaxEndAt as $empMaxEndAt) {
                        self::where('id', $empMaxEndAt->id)
                                ->update(['end_at'=> $date]);
                    }
                }
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
        }
    }

    /**
     * Get teams of employee by month, year
     *
     * @param int $month
     * @param int $year
     * @param int $empId
     * @return EmployeeTeamHistory collection
     */
    public static function getTeamOfEmp($month, $year, $empId, $teamId = null)
    {
        $firstLastMonth = View::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $firstDay = $firstLastMonth[0];
        $lastDay = $firstLastMonth[1];
        $result = self::whereRaw("(DATE(start_at) <= DATE(?) or start_at is null) and (DATE(end_at) >= DATE(?) or end_at is null)", [$lastDay, $firstDay])
                    ->where('employee_id', $empId)
                    ->select('*');
        if ($teamId) {
            $result->where('team_id', $teamId);
            return $result->first();
        }
        return $result->get();
    }

    /**
     * Set end_at for employees leave date in today
     */
    public static function cronUpdate()
    {
        $empsUpdatedToday = Employee::getEmpUpdatedToday();
        foreach ($empsUpdatedToday as $emp) {
            static::updateEndAt($emp->id, $emp->leave_date);
        }
    }

    /**
     * Get employee has team start at $startAt
     *
     * @param date $startAt
     * @param int $employeeId
     * @return EmployeeTeamHistory collection
     */
    public static function getTeamByStartAt($startAt, $employeeId)
    {
        return self::whereRaw("DATE(start_at) = DATE(?)", [$startAt])
                ->where('employee_id', $employeeId)
                ->get();
    }
}
