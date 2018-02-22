<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use DB;
use Exception;
use Illuminate\Support\Facades\Lang;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\TeamList;
use Illuminate\Support\Facades\URL;
use Rikkei\Resource\View\getOptions;

class Team extends CoreModel
{
    
    use SoftDeletes;
    
    /*
     * flag allow number max leader of a team
     */
    const MAX_LEADER = 1;
    
    const KEY_CACHE = 'team';
    const TEAM_TYPE_SYSTENA = 1;
    const TEAM_TYPE_HR = 2;

    const ROLE_TEAM_LEADER = 1;
    const ROLE_SUB_LEADER = 2;
    const ROLE_MEMBER = 3;
    const DETAIL = 'Detail';
    
    const TYPE_REGION_HN = 21;
    const TYPE_REGION_DN = 22;
    const TYPE_REGION_JP = 23;
    
    const IS_SOFT_DEVELOPMENT = 1;
    const IS_NOT_SOFT_DEV = 0;
    
    const CODE_RK_DANANG = 'rdn';
    const CODE_TEAM_IT = 'hanoi_it';
    
    const WORKING = 1;
    const END_WORK = 2;
    
    protected $table = 'teams';
    
    /*
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name', 'parent_id', 'sort_order', 'follow_team_id', 'is_function', 'type', 'description', 'leader_id', 'email'
    ];
    
    public static function listRegion() {
        return [
            self::TYPE_REGION_HN => Lang::get('team::view.Hanoi'),
            self::TYPE_REGION_DN => Lang::get('team::view.Danang'),
            self::TYPE_REGION_JP => Lang::get('team::view.Japan'),
        ];
    }


    /**
     * Get the leader info for the team
     */
    public function leaderInfo() {
        return $this->belongsTo('Rikkei\Team\Model\Employee', 'leader_id');
    }

    /**
     * move position team
     * 
     * @param boolean $up
     */
    public function move($up = true)
    {
        $siblings = Team::select('id', 'sort_order')
            ->where('parent_id', $this->parent_id)
            ->orderBy('sort_order')
            ->get();
        if (count($siblings) < 2) {
            return true;
        }
        $dataOrder = $siblings->toArray();
        $flagIndexToCurrent = false;
        $countDataOrder = count($dataOrder);
        if ($up) {
            if ($dataOrder[0]['id'] == $this->id) { //item move up is first
                return true;
            }
            for ($i = 1; $i < $countDataOrder; $i++) {
                if (!$flagIndexToCurrent) {
                    $dataOrder[$i]['sort_order'] = $i;
                    if ($dataOrder[$i]['id'] == $this->id) {
                        $dataOrder[$i]['sort_order'] = $i - 1;
                        $dataOrder[$i - 1]['sort_order'] = $i;
                        $flagIndexToCurrent = true;
                    }
                } else {
                    unset($dataOrder[$i]);
                }
            }
        } else {
            if ($dataOrder[count($dataOrder) - 1]['id'] == $this->id) { //item move down is last
                return true;
            }
            for ($i = 0; $i < $countDataOrder - 1; $i++) {
                if (!$flagIndexToCurrent) {
                    $dataOrder[$i]['sort_order'] = $i;
                    if ($dataOrder[$i]['id'] == $this->id) {
                        $dataOrder[$i]['sort_order'] = $i + 1;
                        $dataOrder[$i + 1]['sort_order'] = $i;
                        $flagIndexToCurrent = true;
                        $i++;
                    }
                } else {
                    unset($dataOrder[$i]);
                }
            }
        }
        DB::beginTransaction();
        try {
            foreach ($dataOrder as $data) {
                $team = self::find($data['id']);
                $team->sort_order = $data['sort_order'];
                $team->save();
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * delete team and all child
     * 
     * @throws \Rikkei\Team\Model\Exception
     */
    public function delete()
    {
        if ($length = $this->getNumberMember()) {
            throw new Exception(Lang::get("team::messages.Team :name has :number members, can't delete!",[
                'name' => $this->name,
                'number' => $length
            ]), self::ERROR_CODE_EXCEPTION);
        }
        $children = Team::select('id')
            ->where('parent_id', $this->id)->get();
        DB::beginTransaction();
        try {
            //delete all children of team
            if (count($children)) {
                foreach ($children as $child) {
                    Team::find($child->id)->delete();
                }
            }
            
            // TO DO check table Relationship: team position, user, css, ...
            
            //delete team rule
            Permission::where('team_id', $this->id)->delete();
            //set permission as of  teams follow this team to 0
            Team::where('follow_team_id', $this->id)->update([
                'follow_team_id' => null
            ]);
            parent::delete();
            CacheHelper::forget(self::KEY_CACHE);
            CacheHelper::forget(TeamList::KEY_CACHE_ALL_CHILD, $this->parent_id);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * rewrite vave the team to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array()) {
        if (! $this->parent_id) {
            $this->parent_id = null;
        }
        // update model
        if ($this->id) {
            //delete team rule of this team
            if (! $this->is_function || $this->follow_team_id) {
                Permission::where('team_id', $this->id)->delete();
                
                //flush cache
                $positions = Role::getAllPosition();
                if (count($positions)) {
                    foreach ($positions as $position) {
                        CacheHelper::forget(
                        Employee::KEY_CACHE_PERMISSION_TEAM_ACTION,
                        $this->id . '_' . $position->id
                        );
                        CacheHelper::forget(
                            Employee::KEY_CACHE_PERMISSION_TEAM_ROUTE,
                            $this->id . '_' . $position->id
                        );
                    }
                }
            }
        }
        CacheHelper::forget(self::KEY_CACHE);
        CacheHelper::forget(TeamList::KEY_CACHE_ALL_CHILD, $this->parent_id);
        return parent::save($options);
    }
    
    /**
     * get number children of a team
     * 
     * @return int
     */
    public function getNumberChildren()
    {
        $children = self::select(DB::raw('count(*) as count'))
            ->where('parent_id', $this->id)
            ->first();
        return $children->count;
    }
    
    /**
     * get number member of a team
     * 
     * @return int
     */
    public function getNumberMember()
    {
        $children = TeamMember::select(DB::raw('count(*) as count'))
            ->where('team_id', $this->id)
            ->first();
        return $children->count;
    }
    
    /**
     * get team permission as
     * 
     * @return boolean|model
     */
    public function getTeamPermissionAs()
    {
        if (! $this->follow_team_id) {
            return null;
        }
        if ($teamAs = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $teamAs;
        }
        $teamAs = Team::find($this->follow_team_id);
        if (! $teamAs) {
            return null;
        }
        CacheHelper::put(self::KEY_CACHE, $teamAs, $this->id);
        return $teamAs;
    }
    
    /**
     * get teams by team_id list
     * 
     * @param array $arrTeamIds
     * @return object list
     */
    public function getTeamsByTeamIds($arrTeamIds){
        return self::whereIn('id', $arrTeamIds)->get();
    }
    
    /**
     * Get Team with deleted_at != null by id
     * 
     * @param int $teamId
     * @return Team
     */
    public function getTeamWithTrashedById($teamId){
        return self::where('id',$teamId)
                ->withTrashed()
                ->first();
    }
    
    /**
     * Get Team with deleted_at != null by parent id
     * 
     * @param int $parentId
     * @return Team list
     */
    public function getTeamByParentId($parentId){
        return self::where('parent_id',$parentId)
                ->withTrashed()
                ->get();
    }
    
    /**
     * 
     */
    public function getTeamByParentIdNoTrashed($parentId){
        return self::where('parent_id',$parentId)
                ->get();
    }
    
    /**
     * get leader of team
     * 
     * @return model|null
     */
    public function getLeader()
    {
        if (! $this->leader_id) {
            return null;
        }
        $leader = Employee::find($this->leader_id);
        if (!$leader) {
            return null;
        }
        return $leader;
    }
    
    /**
     * check team is function
     * 
     * @return boolean
     */
    public function isFunction()
    {
        if ($this->is_function) {
            return true;
        }
        return false;
    }
    
    /**
     * get children of team
     * 
     * @param int|null $teamParentId
     * @return model
     */
    public static function getTeamChildren($teamParentId = null)
    {
        if ($teams = CacheHelper::get(self::KEY_CACHE)) {
            return $teams;
        }
        $teams = Team::select('id', 'name', 'parent_id')
                ->where('parent_id', $teamParentId)
                ->orderBy('sort_order', 'asc')
                ->get();
        CacheHelper::put(self::KEY_CACHE, $teams);
        return $teams;
    }
    
    /**
     * get team path
     * 
     * @return array|null
     */
    public static function getTeamPath()
    {
        if ($teamPath = CacheHelper::get(self::KEY_CACHE)) {
            return $teamPath;
        }
        $teamAll = Team::select('id', 'parent_id')->get();
        if (! count($teamAll)) {
            return null;
        }
        $teamPaths = [];
        foreach ($teamAll as $team) {
            self::getTeamPathRecursive($teamPaths[$team->id], $team->parent_id);
            self::getTeamChildRecursive($teamPaths[$team->id], $team->id);
            if (! isset($teamPaths[$team->id])) {
                $teamPaths[$team->id] = null;
            }
        }
        CacheHelper::put(self::KEY_CACHE, $teamPaths);
        return $teamPaths;
    }
    
    /**
     * get team path recursive
     * 
     * @param array $teamPaths
     * @param null|int $parentId
     */
    protected static function getTeamPathRecursive(&$teamPaths = [], $parentId = null)
    {
        if (! $parentId) {
            return;
        }
        $teamParent = Team::find($parentId);
        if (! $teamParent) {
            return;
        }
        $teamPaths[] = (int) $teamParent->id;
        self::getTeamPathRecursive($teamPaths, $teamParent->parent_id);
    }
    
    /**
     * get team child recursive
     * 
     * @param array $teamPaths
     * @param null|int $teamId
     */
    protected static function getTeamChildRecursive(&$teamPaths = [], $teamId = null)
    {
        if (! $teamId) {
            return;
        }
        $teamChildren = Team::select('id', 'parent_id')
            ->where('parent_id', $teamId)
            ->get();
        if (! count($teamChildren)) {
            return;
        }
        foreach ($teamChildren as $item) {
            $teamPaths['child'][] = (int) $item->id;
            self::getTeamChildRecursive($teamPaths, $item->id);
        }
    }
    
    /**
     * get grid data of member list
     * 
     * @param int|null $teamId
     * @return collection
     */
    public static function getMemberGridData($teamId = null, $isWorking = null, $urlFilter = null)
    {
        $countTeam = 0;
        if ($teamId) {
            $team = self::getTeamPath();
            if (isset($team[$teamId]['child'])) {
                $countTeam = count($team[$teamId]['child']);
                $teamId = '(' . implode(", ",$team[$teamId]['child']) . ')';
            } else {
                $countTeam = 1;
                $teamId = '(' . $teamId . ')';
            }
        }
        $teamTable = self::getTableName();
        $teamTableAs = 'team_table';
        $employeeTable = Employee::getTableName();
        $employeeTableAs = $employeeTable;
        $employeeTeamTable = TeamMember::getTableName();
        $employeeTeamTableAs = 'team_member_table';
        $roleTabel = Role::getTableName();
        $roleTabelAs = 'role_table';
        $roleSpecialTabelAs = 'role_special_table';
        $memberRoleTable = EmployeeRole::getTableName();
        $memberRoleTabelAs = 'member_role_table';
        $pager = Config::getPagerData($urlFilter);
        
        $collection = Employee::select(
                "{$employeeTable}.id as id",
                "{$employeeTable}.employee_code as employee_code",
                "{$employeeTable}.name as name",
                "{$employeeTable}.email as email",
                DB::raw("DATE_FORMAT({$employeeTable}.join_date, '%Y-%m-%d') as join_date"),
                DB::raw("DATE_FORMAT({$employeeTable}.leave_date, '%Y-%m-%d') as leave_date"),
                "{$teamTableAs}.name as team_name"
            );
        if ($countTeam > 1 || !$teamId) {
            $collection->addSelect(
                DB::raw("GROUP_CONCAT(DISTINCT " . 
                    "CONCAT(`{$roleTabelAs}`.`role`, ' - ', `{$teamTableAs}`.`name`)" . 
                    " SEPARATOR '; ')" .
                    "as role_name")
            );
        } else {
            $collection->addSelect(
                "{$roleTabelAs}.role as role_name"
            );
        }
        //join team member
        if ($teamId) {
            $collection->join(
                "{$employeeTeamTable} as {$employeeTeamTableAs}", 
                function ($join) use ($teamId, $employeeTable, $employeeTeamTableAs
            ) {
                $join->on("{$employeeTable}.id", '=', "{$employeeTeamTableAs}.employee_id");
                $join->on("{$employeeTeamTableAs}.team_id", 'IN', DB::raw($teamId));
            });
        } else {
            $collection->leftJoin(
                "{$employeeTeamTable} as {$employeeTeamTableAs}", 
                function ($join) use ($employeeTable, $employeeTeamTableAs
            ) {
                $join->on("{$employeeTable}.id", '=', "{$employeeTeamTableAs}.employee_id");
            });
        }
        //join team
        if ($teamId) {
            $collection->join(
                "{$teamTable} as {$teamTableAs}", 
                function ($join) use ($teamId, $teamTableAs, $employeeTeamTableAs
            ) {
                $join->on("{$teamTableAs}.id", '=', "{$employeeTeamTableAs}.team_id");
                $join->on("{$teamTableAs}.id", 'IN', DB::raw($teamId));
            });
        } else {
            $collection->leftJoin(
                "{$teamTable} as {$teamTableAs}", 
                function ($join) use ($teamTableAs, $employeeTeamTableAs
            ) {
                $join->on("{$teamTableAs}.id", '=', "{$employeeTeamTableAs}.team_id");
            });
        }
        
        //join role
        if ($teamId) {
            $collection->join(
                "{$roleTabel} as {$roleTabelAs}", 
                function ($join) use ($roleTabelAs, $employeeTeamTableAs
            ) {
                $join->on("{$roleTabelAs}.id", '=', "{$employeeTeamTableAs}.role_id");
                $join->on("{$roleTabelAs}.special_flg", '=', DB::raw(Role::FLAG_POSITION));
            });
        }
        else {
            $collection->leftJoin(
                "{$roleTabel} as {$roleTabelAs}", 
                function ($join) use ($roleTabelAs, $employeeTeamTableAs
            ) {
                $join->on("{$roleTabelAs}.id", '=', "{$employeeTeamTableAs}.role_id");
                $join->on("{$roleTabelAs}.special_flg", '=', DB::raw(Role::FLAG_POSITION));
            });
        }
        // join get role special
        $collection->leftJoin("{$memberRoleTable} as {$memberRoleTabelAs}",
                "{$memberRoleTabelAs}.employee_id", '=', "{$employeeTable}.id")
            ->leftJoin("{$roleTabel} as {$roleSpecialTabelAs}",
                "{$roleSpecialTabelAs}.id", '=', "{$memberRoleTabelAs}.role_id")
            ->addSelect(Db::raw("GROUP_CONCAT(DISTINCT `{$roleSpecialTabelAs}`.`role`" . 
                    " SEPARATOR '; ') as role_special"));
        $collection->groupBy("{$employeeTable}.id");
        //use soft delete
        if (Team::isUseSoftDelete()) {
            $collection->whereNull("{$teamTableAs}.deleted_at");
        }
        if (Role::isUseSoftDelete()) {
            $collection->whereNull("{$roleTabelAs}.deleted_at");
        }
        if ($isWorking) {
            $currentDay = date("Y-m-d");
            if ($isWorking == Team::WORKING) {
                $collection->where(function($query) use ($employeeTable, $currentDay){
                    $query->orWhereDate("{$employeeTable}.leave_date", ">=", $currentDay)
                            ->orWhereNull("{$employeeTable}.leave_date");
                });
            } else {
                $collection->where(function($query) use ($employeeTable, $currentDay){
                    $query->whereNotNull("{$employeeTable}.leave_date")
                        ->whereDate("{$employeeTable}.leave_date", "<", $currentDay);
                });
            }
        }
        $collection = $collection->where("{$employeeTableAs}.working_type", "!=", getOptions::WORKING_INTERNSHIP);
        self::filterGrid($collection, [], $urlFilter, 'LIKE');
        $collection->orderBy($pager['order'], $pager['dir']);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /**
     * find id systena team
     * 
     * @return null|id
     */
    public static function findSystenaId()
    {
        $team = self::select('id')
            ->where('type', self::TEAM_TYPE_SYSTENA)
            ->first();
        if ($team) {
            return $team->id;
        }
        return null;
    }    
        
    public static function getTeamByLeader($empId) {
        return self::where('leader_id', $empId)->first();
    }
    
    /**
     * Get teams by employee
     * $isLeader = true => get teams with user is leader
     * 
     * @param int $empId
     * @param boolean $isLeader
     * @return Team collection
     */
    public static function getTeamByEmp($empId, $isLeader = false) {
        $team = self::join("team_members", "teams.id", "=", "team_members.team_id")
                ->where('team_members.employee_id', $empId);
        if ($isLeader) {
            $team->where('teams.leader_id', $empId);
        }        
        return $team->select('teams.id', 'teams.name', 'teams.leader_id')->first();
    }
    
    public static function getTeamById($teamId) {
        if ($team = CacheHelper::get(self::KEY_CACHE, self::DETAIL . $teamId)) {
            return $team;
        }
        $team = self::find($teamId);
        CacheHelper::put(self::KEY_CACHE, $team, self::DETAIL . $teamId);
        return $team;
    }

    /*
     * get all team
     */
    public static function getAllTeam()
    {
        if ($teams = CacheHelper::get(self::KEY_CACHE)) {
            return $teams;
        }
        $teams = self::select('id', 'name')
                ->get();
        CacheHelper::put(self::KEY_CACHE, $teams);
        return $teams;
    }

    /**
     * get label of team
     * @return array
     */
    /*public static function getLabelTeam()
    {
        return [
            'RDN-BGD'               => TeamConst::CODE_BOD,
            'RJP-BGD'               => TeamConst::CODE_BOD,
            'TCT-BGD'               => TeamConst::CODE_BOD,
            'D0'                   => TeamConst::CODE_HN_D0,
            'D1'                   => TeamConst::CODE_HN_D1,
            'D2'                   => TeamConst::CODE_HN_D2,
            'D3'                   => TeamConst::CODE_HN_D3,
            'D5'                   => TeamConst::CODE_HN_D5,
            'PTPM-QA'                    => TeamConst::CODE_HN_QA,
            'RDN-PTPM'                    => TeamConst::CODE_DANANG,
            'PTPM-PROD'                    => TeamConst::CODE_HN_PRODUCTION,
            'TCT-PKT'                   => TeamConst::CODE_HN_HCTH,
            'TCT-HCTH'                   => TeamConst::CODE_HN_HCTH,
            'RDN_HCTH'                   => TeamConst::CODE_DANANG,
            'TCT-NS'                   => TeamConst::CODE_HN_HR,
            'RJP'                   => TeamConst::CODE_JAPAN,
            'TCT-SALES'                   => TeamConst::CODE_HN_SALES,
            'TCT-IT'                   => TeamConst::CODE_HN_IT,
            'RDN'                   => TeamConst::CODE_DANANG
        ];
    }*/

    /**
     * label team leader for upload member.
     * @return array
     */
    /*public static function lableTeamLear()
    {
        return [
            'Team Leader',
            'Trưởng phòng Kế toán',
            'Chủ tịch Hội Đồng Quản Trị'
        ];
    }*/

    /**
     * label sub leader for upload member.
     * @return array
     */
    /*public static function lableSubLear()
    {
        return [
            'Sub Leader'
        ];
    }*/

    /**
     * get position member in team
     * @param string
     * @return int
     */
    /*public static function getPositionTeam($namePosition)
    {
        $lableTeamLear = self::lableTeamLear();
        $lableSubLear = self::lableSubLear();
        if (in_array($namePosition, $lableTeamLear)) {
            return self::ROLE_TEAM_LEADER;
        } else if (in_array($namePosition, $lableSubLear)) {
            return self::ROLE_SUB_LEADER;
        } else {
            return self::ROLE_MEMBER;
        }
    }*/

    /**
     * label team specital for upload member.
     * @return array
     */
    /*public static function getLabelTeamSpecial()
    {
        return [
            'Giám đốc Chi nhánh Đà nẵng' => 'Rikkei - Danang',
            'Trưởng phòng nhân sự'       => 'Nhân sự',
            'Hành chính Kế toán Đà Nẵng' => 'Rikkei - Danang',
        ];
    }*/

    /**
     * label position special for upload member.
     * @param string
     * @return array
     */
    /*public static function labelPositionSpecial($namePosition)
    {
        $labelTeam = self::getLabelTeamSpecial();
        $teamId = self::getTeamIdByName($labelTeam[$namePosition]);
        return [
            'Giám đốc Chi nhánh Đà nẵng' => [
                                                'team_id' => $teamId,
                                                'role_id' => self::ROLE_TEAM_LEADER,
                                            ],
            'Trưởng phòng nhân sự'       => [
                                                'team_id' => $teamId,
                                                'role_id' => self::ROLE_TEAM_LEADER,
                                            ],
            'Hành chính Kế toán Đà Nẵng' => [
                                                'team_id' => $teamId,
                                                'role_id' => self::ROLE_MEMBER,
                                            ],
        ];
    }*/

    /**
     * label for upload member.
     * @param string
     * @return array
     */
    /*public static function teamSpecial($namePosition)
    {
        $labelPositionSpecial = self::labelPositionSpecial($namePosition);
        if (array_key_exists($namePosition, $labelPositionSpecial)) {
            $labelTeam = self::getLabelTeam();
            return $labelPositionSpecial[$namePosition];
        }
        return null;
    }*/

    /**
     * get team id by name for upload member.
     * @param string
     * @return int
     */
    public static function getTeamIdByName($name)
    {
        $team = self::where('name', trim($name))
                    ->first();
        if ($team) {
            return $team->id;
        }
        return;
    }

    /**
     * get leader of team
     * @param int
     * @return int
     */
    public static function getLeaderOfTeam($id)
    {
        $team = self::find($id);
        if(!$team) {
            return null;
        }
        return $team->leader_id;
    }
    
    /**
     * get name of teams
     * 
     * @param array $ids
     * @return array
     */
    public static function getTeamsName(array $ids)
    {
        $collection = self::select('name')
            ->whereIn('id', $ids)
            ->get();
        $result = [];
        if (!count($collection)) {
            return $result;
        }
        foreach ($collection as $item) {
            $result[] = $item->name;
        }
        return $result;
    }
    
    /**
     * Get team by type
     * 
     * @param int $type
     * @return Team
     */
    public static function getTeamByType($type) {
        return self::where('type', $type)
                   ->select('*')
                   ->first();
    }
    
    public static function searchAjax($name, array $config = []) {
        $result = [];
        $arrayDefault = [
            'page' => 1,
            'limit' => 20,
            'typeExclude' => null
        ];
        $config = array_merge($arrayDefault, $config);
        $collection = self::select(['id', 'name'])
                    ->where('name', 'LIKE', '%' . $name . '%')
                    ->orderBy('name');
                    
        self::pagerCollection($collection, $config['limit'], $config['page']); 
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => $item->name,
            ];
        }
        return $result;
    }
    
    public static function getTeamList($conditions = [], $columns = ['*']) {
        $teams = self::select($columns);
        if (is_array($conditions) && count($conditions)) {
            foreach ($conditions as $field => $value) {
                $teams->where($field, $value);
            }
        }
        return $teams->get();
                    
    }
        
    /**
     * Get teams name by team id
     * 
     * @param array $ids array team_id
     * @return array
     */
    public static function getNamesByIds($ids) {
        $teams = self::whereIn('id', $ids)->select('name')->get();
        $result = [];
        foreach ($teams as $team) {
            $result[] = $team->name;
        }
        
        return $result;
    }
    
    /**
     * get leader id of team by team code
     * @param string
     * @return int
     */
    public static function getLeaderIdByCode($teamCode)
    {
        $team = self::where('code', $teamCode)->first();

        if(!$team) {
            return null;
        }
        return $team->leader_id;
    }

    /**
     * Purpose : check leader of team by 
     *
     * @param $employee_id
     *
     * @return bool
     */
    public static function checkLeaderOfTeamByTeamCode($teamCode, $employeeId)
    {
        return self::where('code', $teamCode)
                    ->where('leader_id', $employeeId)
                    ->count() ? true : false;
    }
    
    /**
     * get all team path
     * 
     * @return array
     */
    public static function getTeamPathTree()
    {
        if ($result = CacheHelper::get(self::KEY_CACHE)) {
            return $result;
        }
        $collection = self::select(['id', 'parent_id', 'name', 'code', 'is_soft_dev'])
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        self::getTeamPathTreeRecursive($collection, $result, 0);
        CacheHelper::put(self::KEY_CACHE, $result);
        return $result;
    }
    
    /**
     * call recursive of team path tree
     * 
     * @param collection $collection
     * @param array $result
     * @param int $idParentCheck
     * @return boolean
     */
    protected static function getTeamPathTreeRecursive(
        &$collection,
        &$result,
        $idParentCheck
    ) {
        if (!count($collection)) {
            return true;
        }
        foreach ($collection as $keyIndex => $item) {
            // init element result
            $item->id = (int) $item->id;
            if (!isset($result[$item->id])) {
                $result[$item->id] = [
                    'parent' => [],
                    'child' => [],
                    'data' => []
                ];
            }
            $result[$item->id]['data'] = [
                'name' => $item->name,
                'code' => $item->code,
                'is_soft_dev' => $item->is_soft_dev
            ];
            if ((int) $item->parent_id !== $idParentCheck) {
                continue;
            }
            if (!isset($result[$idParentCheck])) {
                $result[$idParentCheck] = [
                    'parent' => [],
                    'child' => [],
                    'data' => []
                ];
            }
            // insert array: parent in db + array parent of parent
            $result[$item->id]['parent'] = 
                array_merge([$idParentCheck], $result[$idParentCheck]['parent']);
            // insert child element
            $result[$idParentCheck]['child'][] = $item->id;
            $collection->forget($keyIndex);
            self::getTeamPathTreeRecursive($collection, $result, $item->id);
        }
    }
    
    /**
     * get all leader of team
     * 
     * @return array
     */
    public static function getAllLeaderTeam()
    {
        if ($result = CacheHelper::get(Employee::KEY_CACHE)) {
            return $result;
        }
        $tableEmployee = Employee::getTableName();
        $tableTeam = self::getTableName();
        
        $collection = self::select([$tableTeam.'.id as id', 
            $tableTeam.'.leader_id', $tableEmployee.'.name'])
            ->join($tableEmployee, $tableEmployee.'.id', '=', 
                $tableTeam.'.leader_id')
            ->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        foreach ($collection as $item) {
            $item->id = (int) $item->id;
            $result[$item->id] = [
                'id' => $item->leader_id,
                'name' => $item->name
            ];
        }
        CacheHelper::put(Employee::KEY_CACHE, $result);
        return $result;
    }
    
    /**
     * Get teams has not child
     * 
     * @return Team
     */
    public static function getTeamsChildest($teamIds = null)
    {
        $result = self::whereRaw("(Select count(id) from teams as t where parent_id = teams.id and t.deleted_at is null) = 0");
        if ($teamIds) {
            $result->whereIn('id', $teamIds);
        }
        $result->select('id', 'name')
                ->where('is_soft_dev', self::IS_SOFT_DEVELOPMENT)
                ->orderBy('name');
        return $result->get();
    }
}
