<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\DB;

class TeamMember extends CoreModel
{
    protected $table = 'team_members';
    
    /*
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'team_id', 'employee_id', 'position_id'
    ];
    
    /**
     * Get TeamMember by employee
     * 
     * @param int $employeeId
     * @return TeamMember list
     */
    public function getTeamMembersByEmployee ($employeeId){
        return self::where('employee_id',$employeeId)->get();
    }

    /**
     * check team has member
     * @param int
     * @param int
     * @param int
     * @return boolean
     */
    public static function checkTeamMember($teamId, $employeeId, $roleId)
    {
        return self::where('team_id', $teamId)
                    ->where('employee_id', $employeeId)
                    ->where('role_id', $roleId)
                    ->count() ? true : false;
    }

    /**
     * Get TeamMember by employee
     * 
     * @param int $employeeId
     * @return TeamMember list
     */
    public static function getTeamMembersByEmployees ($employeeId) {
        return self::where('employee_id',$employeeId)->get();
    }

    /**
     * get leader or subleader
     * @param int
     * @param int
     * @return array
     */
    public static function getLeaderOrSubleader($employeeId, $roleId)
    {
        return self::where('employee_id', $employeeId)
                    ->whereIn('role_id', $roleId)
                    ->select(['team_id'])
                    ->get();
    }

    /**
     * get all memeber of team
     * @param int
     * @return array
     */
    public static function getAllMemberOfTeam($teamId)
    {
        return self::where('team_id', $teamId)
                    ->where('role_id', Team::ROLE_MEMBER)
                    ->lists('employee_id')
                    ->toArray();
    }
    
    /**
     * get Employees by teamId
     * @param int
     * @return array
     */
    public static function getEmployeesByTeamId($teamId)
    {
        return self::where('teams.id', $teamId)
            ->join('teams', 'teams.id', '=', 'team_members.team_id')
            ->join('employees', 'employees.id', '=', 'team_members.employee_id')
            ->lists('employees.email')
            ->toArray();
    }

    /**
     * get all memeber of team by team code
     * @param string
     * @return array
     */
    public static function getAllMemberOfTeamByCode($teamCode)
    {
        return self::where('teams.code', $teamCode)
            ->join('teams', 'teams.id', '=', 'team_members.team_id')
            ->join('employees', 'employees.id', '=', 'team_members.employee_id')
            ->select('employee_id', 'employees.name')
            ->get();
    }

    /**
     * check member of team
     * @param string
     * @param int
     * @return boolean
     */
    public static function checkMemberOfTeamByTeamCode($teamCode, $employeeId)
    {
        return self::where('teams.code', $teamCode)
                    ->where('team_members.employee_id', $employeeId)
                    ->join('teams', 'teams.id', '=', 'team_members.team_id')
                    ->join('employees', 'employees.id', '=', 'team_members.employee_id')
                    ->count() ? true : false;
    }
    
    /**
     * get teams of employees
     * 
     * @param type $employeeIds
     * @return collection
     */
    public static function getTeamOfEmployee($employeeIds)
    {
        return self::select('employee_id',
            DB::raw('GROUP_CONCAT(team_id SEPARATOR ",") AS team_ids'))
            ->whereIn('employee_id', $employeeIds)
            ->groupBy('employee_id')
            ->get();
    }

    /**
     * check team include employee?
     *
     * @param int $employeeId
     * @param array $teamIds
     * @return boolean
     */
    public static function isEmployeeOfTeam($employeeId, array $teamIds)
    {
        $result = self::select('team_id')->where('employee_id', $employeeId)
            ->whereIn('team_id', $teamIds)
            ->first();
        if ($result) {
            return true;
        }
        return false;
    }
}
