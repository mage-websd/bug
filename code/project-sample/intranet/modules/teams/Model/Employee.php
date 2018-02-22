<?php
namespace Rikkei\Team\Model;

use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Input;
use Lang;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\View\View;
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Recruitment\Model\RecruitmentApplies;
use Rikkei\Resource\View\View as ResourceView;
use Rikkei\Team\Model\EmployeeRole;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\CheckpointPermission;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission as PermissionView;
use Rikkei\Team\View\TeamConst;
use Rikkei\Team\View\TeamList;

class Employee extends CoreModel
{
    
    use SoftDeletes;
    
    /*
     * flag value gender
     */
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 0;
    
    /*
     * flag value marital
     */
    const MARITAL_SINGLE = 0;
    const MARITAL_MARRIED  = 1;
    const MARITAL_WIDOWED = 2;
    const MARITAL_SEPARATED = 3;
    
    /*
     * flag employee code data
     */
    const CODE_PREFIX = 'RK';
    const CODE_LENGTH = 5;

    /**
     * lib folk
     */
    const LIB_FOLK = [
        0 => 'Kinh',
        1 => 'Bố Y',
        2 => 'La Hú',
        3 => 'Lào',
        4 => 'Lự',
        5 => 'Ba Na',
        6 => 'Brâu',
        7 => 'Cơ Tu',
        8 => 'Lô Lô',
        9 =>'Bru - Vân Kiều',
        10 => 'Chăm',
        11 =>'Chứt',
        12 => 'Ê Đê',
        13 =>'Khmer',
    ];
    
    /**
     * lib religion
     */
    const LIB_RELIG = [
        0 => 'Không',
        1 => 'Phật giáo',
        2 => 'Phật giáo Hòa Hảo',
        3 => 'Cao Đài',
        4 => 'Hồi giáo',
        5 => 'Tin lành',
    ];

    /*
     * key store cache
     */
    const KEY_CACHE = 'employee';
    const KEY_CACHE_PERMISSION_TEAM_ROUTE = 'team_rule_route';
    const KEY_CACHE_PERMISSION_TEAM_ACTION = 'team_rule_action';
    const KEY_CACHE_PERMISSION_ROLE_ROUTE = 'role_rule_route';
    const KEY_CACHE_PERMISSION_ROLE_ACTION = 'role_rule_action';
    const KEY_CACHE_LEADER_PQA = 'leader_pqa';
    const KEY_CACHE_COUNT_EMP_MONTH = 'count_emp_month';
    const KEY_CACHE_LIB_FOLK = 'lib_folk';
    
    /**
     * Type exclude when search employee by ajax
     * function searchAjax()
     */
    const EXCLUDE_REVIEWER = 'reviewer';
    const EXCLUDE_UTILIZATION = 'utilization';
    
    /**
     *
     * path attach folder 
     */
    const ATTACH_FOLDER = 'resource/employee/attach/';
    const AVATAR_FOLDER = 'resource/employee/avatar/';
    
    /**
     * prefix CV
     * @var type 
     */
    const PRE_CV = 'CV';
    
    protected $table = 'employees';
    
    /*
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'birthday',
        'nickname',
        'email',
        'employee_card_id',
        'join_date',
        'leave_date',
        'leave_reason',
        'persional_email',
        'mobile_phone',
        'home_phone',
        'gender',
        'address',
         'home_town',
        'id_card_number',
        'id_card_place',
        'id_cart_date',
        'recruitment_apply_id',
        'employee_code',
        'personal_email',
        'state'
    ];
    
    /**
     * get collection to show grid
     * 
     * @return type
     */
    public static function getGridData()
    {
        $pager = Config::getPagerData();
        $collection = self::select('id','name','email', 'employee_code')
            ->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /**
     * rewrite save model employee
     * 
     * @param array $options
     */
    public function save(array $options = array(), $config = [])
    {
        DB::beginTransaction();
        try {
            $result = parent::save($options);
            //$rSkill = $this->saveSkills();
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE);
            return $result;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * save recruitment apply id follow phone
     * 
     * @return Employee
     */
    public function saveRecruitmentAppyId()
    {
        if ($this->recruitment_apply_id || ! $this->mobile_phone) {
            return;
        }
        $recruitment = RecruitmentApplies::select('id')->where('phone', $this->mobile_phone)->first();
        if ($recruitment) {
            $this->recruitment_apply_id = $recruitment->id;
        }
        return $this;
    }
    
    /**
     * save team positon for member
     * 
     * @param array $teamPostions
     * @throws Exception
     */
    public function saveTeamPosition(array $teamPostions = [])
    {
        if (! $this->id) {
            return;
        }
        if (! $teamPostions) {
            $teamPostions = (array) Input::get('team');
            
            if (isset($teamPostions[0])) {
                unset($teamPostions[0]);
            }
            if (! Input::get('employee_team_change')) {
                return;
            }
        }
        if (! $teamPostions) {
            return;
        }
        
        //check miss data
        foreach ($teamPostions as $teamPostion) {
            if (! isset($teamPostion['team']) || 
                ! isset($teamPostion['position']) ||
                ! $teamPostion['team'] ||
                ! $teamPostion['position']) {
                throw new Exception(Lang::get('team::view.Miss data team or position'), self::ERROR_CODE_EXCEPTION);
            }
        }
        
        //check data team not same
        $lengthTeamPostionsSubmit = count($teamPostions);
        for ($i = 1 ; $i < $lengthTeamPostionsSubmit ;  $i++) {
            if (! isset($teamPostions[$i])) {
                continue;
            }
            for ($j = $i + 1 ; $j <= $lengthTeamPostionsSubmit ; $j ++) {
                if (! isset($teamPostions[$j])) {
                    continue;
                }
                if ($teamPostions[$i]['team'] == $teamPostions[$j]['team']) {
                    throw new Exception(Lang::get('team::view.Team same data'), self::ERROR_CODE_EXCEPTION);
                }
            }
        }
        DB::beginTransaction();
        try {
            //delete all team position of employee before insert new
            TeamMember::where('employee_id', $this->id)->delete();
            // set null leader id before set leader new
            Team::where('leader_id', $this->id)->update([
                'leader_id' => null
            ]);
            //save to table employee_team_history
            $this->updateTeamHistory($teamPostions);
            //save to table team_members
            foreach ($teamPostions as $teamPostion) {
                $team = Team::find($teamPostion['team']);
                if (! $team) {
                    continue;
                }
                if (! $team->isFunction()) {
                    throw new Exception(Lang::get('team::messages.Team :name isnot function', ['name' => $team->name]), self::ERROR_CODE_EXCEPTION);
                }

                $positionLeader = Role::isPositionLeader($teamPostion['position']);
                if ($positionLeader === null) { //not found position
                    continue;
                } else if ($positionLeader === true) { //position is leader
                    $teamLeader = $team->getLeader();
                    if (Team::MAX_LEADER == 1 && $teamLeader && $teamLeader->id != $this->id) { //flag team only have 1 leader
                        throw new Exception(Lang::get('team::messages.Team :name had :nameleader leader!', ['name' => $team->name, 'nameleader' => $team->leaderInfo->name]), self::ERROR_CODE_EXCEPTION);
                    } elseif (! $teamLeader) { //save leader for team
                        $team->leader_id = $this->id;
                        $team->save();
                    }
                }

                $teamMember = new TeamMember();
                $teamMember->setData([
                    'team_id' => $teamPostion['team'],
                    'role_id' => $teamPostion['position'],
                    'employee_id' => $this->id
                ]);
                $teamMember->save();
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
        CacheHelper::forget(self::KEY_CACHE, $this->id);
        return true;
    }
    
    /**
     * Update team history
     * 
     * @param array $teamPostions
     * @param array $dataEmployee
     */
    public function updateTeamHistory($teamPostions)
    {
        
        $currentTeam = EmployeeTeamHistory::getCurrentTeams($this->id);
        $arrCurTeamId = [];
        foreach ($currentTeam as $t) {
            $arrCurTeamId[] = $t->team_id;
        }
        $arrNewTeamId = [];
        foreach ($teamPostions as $newT) {
            $arrNewTeamId[] = $newT['team'];
        }
        foreach ($arrCurTeamId as $teamId) {
            if (!in_array($teamId, $arrNewTeamId)) {
                $teamHistory = EmployeeTeamHistory::getCurrentByTeamEmployee($teamId, $this->id);
                $teamHistory->end_at = Carbon::now()->format('Y-m-d H:i:s');
                $teamHistory->save();
            }
        }
        foreach ($arrNewTeamId as $teamId) {
            if (!in_array($teamId, $arrCurTeamId)) {
                $teamHistory = new EmployeeTeamHistory();
                $teamHistory->team_id = $teamId;
                $teamHistory->employee_id = $this->id;
                $teamHistory->start_at = Carbon::now()->format('Y-m-d H:i:s');
                $teamHistory->save();
            }
        }
    }
    
    /**
     * save role for employee
     * 
     * @param array $roles
     * @throws Exception
     */
    public function saveRoles(array $roles = [])
    {
        if (!$this->id) {
            return;
        }
        if (!$roles) {
            $roles = (array) Input::get('role');
            if (!Input::get('employee_role_change')) {
                return;
            }
        }
        
        DB::beginTransaction();
        try {
            EmployeeRole::where('employee_id', $this->id)->delete();
            if (count($roles)) {
                foreach ($roles as $role) {
                    $employeeRole = new EmployeeRole();
                    $employeeRole->setData([
                        'role_id' => $role,
                        'employee_id' => $this->id
                    ]);
                    $employeeRole->save();
                }
            }
            CacheHelper::forget(self::KEY_CACHE, $this->id);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * set code for employee
     * 
     * @return Employee
     */
    public function saveCode($code = null)
    {
        $year = strtotime($this->join_date);
        $year = date('y', $year);
        if ($code) {
            $code = (int) $code;
            if ($code) {
                $lengthCodeCurrent = strlen($code);
                for ($i = 0 ; $i < self::CODE_LENGTH - $lengthCodeCurrent; $i++) {
                    $code = '0' . $code;
                }
                $code = self::CODE_PREFIX . $year . $code;
                $this->employee_code = $code;
                return $this;
            }
        }
        if ($this->employee_code || ! $this->join_date) {
            return;
        }
        $codeLast = self::select('employee_code')
            ->where('employee_code', 'like', self::CODE_PREFIX . $year . '%')
            ->orderBy('employee_code', 'DESC')
            ->first();
        if (! $codeLast) {
            $codeEmployee = self::CODE_PREFIX . $year;
            for ($i = 0; $i < self::CODE_LENGTH - 1; $i++) {
                $codeEmployee .= '0';
            }
            $codeEmployee .= '1';
        } else {
            $codeLast = $codeLast->employee_code;
            $codeEmployee = preg_replace('/^' . self::CODE_PREFIX . $year . '/', '', $codeLast);
            $codeEmployee = (int) $codeEmployee + 1;
            $codeEmployee = (string) $codeEmployee;
            $lengthCodeCurrent = strlen($codeEmployee);
            for ($i = 0 ; $i < self::CODE_LENGTH - $lengthCodeCurrent; $i++) {
                $codeEmployee = '0' . $codeEmployee;
            }
            $codeEmployee = self::CODE_PREFIX . $year . $codeEmployee;
        }
        $this->employee_code = $codeEmployee;
        return $this;
    }
    
    /**
     * save skill and experience
     */
    protected function saveSkills()
    {
        if (! $this->id) {
            return;
        }
        $skillsAll = Input::all();
        $skills = array_get($skillsAll, 'employee_skill');
        $skillsChage = array_get($skillsAll, 'employee_skill_change');
        if (! $skills || !$skillsChage) {
            return;
        }
        $skillsArray = [];
        $skillsChageArray = [];
        parse_str($skills, $skillsArray);
        parse_str($skillsChage, $skillsChageArray);
        
        if (PermissionView::getInstance()->isAllow('team::team.member.edit.skill')) {
            //save school
            if (isset($skillsArray['schools'][0])) {
                unset($skillsArray['schools'][0]);
            }
            if (isset($skillsArray['schools']) &&
                isset($skillsChageArray['schools']) && $skillsChageArray['schools']) {
                $this->saveSchools($skillsArray['schools']);
            }

            // save language
            if (isset($skillsArray['languages'][0])) {
                unset($skillsArray['languages'][0]);
            }
            if (isset($skillsArray['languages']) &&
                isset($skillsChageArray['languages']) && $skillsChageArray['languages']) {
                $this->saveCetificateType($skillsArray['languages'], Certificate::TYPE_LANGUAGE);
            }

            // save cetificate
            if (isset($skillsArray['cetificates'][0])) {
                unset($skillsArray['cetificates'][0]);
            }
            if (isset($skillsArray['cetificates']) &&
                isset($skillsChageArray['cetificates']) && $skillsChageArray['cetificates']) {
                $this->saveCetificateType($skillsArray['cetificates'], Certificate::TYPE_CETIFICATE);
            }

            // save skill
            if (isset($skillsArray['programs'][0])) {
                unset($skillsArray['programs'][0]);
            }
            if (isset($skillsArray['programs']) &&
                isset($skillsChageArray['programs']) && $skillsChageArray['programs']) {
                //delete old programs
                $proOld = self::getAllProgramOfEmployee($this);
                $this->employeePro()->detach($proOld);
                //insert new programs
                $dataInsert = [];
                foreach ($skillsArray['programs'] as $item) {
                    $empProgram = $item['employee_program'];
                    $program = $item['program'];
                    $dataInsert[] = [
                        'employee_id' => $this->id,
                        'programming_id' => $program['id'],
                        'level' => $empProgram['level'],
                        'experience' => $empProgram['experience'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                }
                if (count($dataInsert)) {
                    EmployeeProgram::saveData($dataInsert);
                }
            }

            if (isset($skillsArray['oss'][0])) {
                unset($skillsArray['oss'][0]);
            }
            if (isset($skillsArray['oss']) &&
                isset($skillsChageArray['oss']) && $skillsChageArray['oss']) {
                $this->saveSkillItem($skillsArray['oss'], Skill::TYPE_OS);
            }

            if (isset($skillsArray['databases'][0])) {
                unset($skillsArray['databases'][0]);
            }
            if (isset($skillsArray['databases']) &&
                isset($skillsChageArray['databases']) && $skillsChageArray['databases']) {
                $this->saveSkillItem($skillsArray['databases'], Skill::TYPE_DATABASE);
            }
        }
        
        if (PermissionView::getInstance()->isAllow('team::team.member.edit.exerience')) {
            //save work experience
            if (isset($skillsArray['work_experiences'][0])) {
                unset($skillsArray['work_experiences'][0]);
            }
            if (isset($skillsArray['work_experiences']) &&
                isset($skillsChageArray['work_experiences']) && $skillsChageArray['work_experiences']) {
                $this->saveWorkExperience($skillsArray['work_experiences']);
            }

            //save project experience
            if (isset($skillsArray['project_experiences'][0])) {
                unset($skillsArray['project_experiences'][0]);
            }
            if (isset($skillsArray['project_experiences']) &&
                isset($skillsChageArray['project_experiences']) && $skillsChageArray['project_experiences']) {
                $this->saveProjectExperience($skillsArray['project_experiences']);
            }        
        }
    }
    
    /**
     * The users that belong to the action.
     */
    public function employeePro() {
        $tableEmployeePro = EmployeeProgram::getTableName();
        return $this->belongsToMany('Rikkei\Resource\Model\Programs', $tableEmployeePro, 'employee_id', 'programming_id');
    }
    
    /**
     * Get all program of employee
     * 
     * @parram Employee $employee
     * @return array
     */
    public static function getAllProgramOfEmployee($employee) 
    {        
        $pros = array();
        foreach ($employee->employeePro as $pro) {
            array_push($pros, $pro->id);
        }
        return $pros;
    }

    /**
     * save schools item
     * 
     * @param array $schools
     * @return array
     */
    protected function saveSchools($schools = [])
    {
        $schoolIds = School::saveItems($schools);
        $this->saveEmployeeSchools($schoolIds, $schools);
        
    }
    
    /**
     * save employee school
     * 
     * @param type $schoolIds
     * @param type $schools
     * @return type
     */
    protected function saveEmployeeSchools($schoolIds = [], $schools = [])
    {
        return EmployeeSchool::saveItems($this->id, $schoolIds, $schools);
    }
    
    /**
     * save certificate item
     * 
     * @param array $cetificatesType
     * @param type $type
     * @return type
     */
    protected function saveCetificateType($cetificatesType = [], $type = null)
    {
        $cetificatesTypeIds = Certificate::saveItems($cetificatesType, $type);
        $this->saveEmployeeCetificateType($cetificatesTypeIds, $cetificatesType, $type);
    }
    
    /**
     * save employee cetificate
     * 
     * @param type $cetificatesTypeIds
     * @param type $cetificatesType
     * @return type
     */
    protected function saveEmployeeCetificateType($cetificatesTypeIds = [], $cetificatesType = [], $type = null)
    {
        return EmployeeCertificate::saveItems($this->id, $cetificatesTypeIds, $cetificatesType, $type);
    }
    
    /**
     * save skills
     * 
     * @param array $skills
     * @param type $type
     * @return type
     */
    protected function saveSkillItem($skills = [], $type = null, $profile = false)
    {
        $skillIds = Skill::saveItems($skills, $type);
        $this->saveEmployeeSkillItem($skillIds, $skills, $type, $profile);
    }
    
    /**
     * save employee skills
     * 
     * @param type $cetificatesTypeIds
     * @param type $cetificatesType
     * @return type
     */
    protected function saveEmployeeSkillItem($skillIds = [], $skills = [], $type = null, $profile = false)
    {
        return EmployeeSkill::saveItems($this->id, $skillIds, $skills, $type, $profile);
    }
    
    /**
     * save work experience for employee
     * 
     * @param type $workExperienceData
     * @return type
     */
    protected function saveWorkExperience($workExperienceData)
    {
        return WorkExperience::saveItems($this->id, $workExperienceData);
    }
    
    /**
     * save project experience
     * 
     * @param type $projectExperienceData
     * @return type
     */
    protected function saveProjectExperience($projectExperienceData)
    {
        return ProjectExperience::saveItems($this->id, $projectExperienceData);
    }


    /**
     * get team and position of employee
     * 
     * @return collection
     */
    public function getTeamPositons()
    {
        if ($employeeTeam = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeTeam;
        }
        $employeeTeam = TeamMember::select('team_id', 'role_id')->where('employee_id', $this->id)->get();
        if ($this->id) {
            CacheHelper::put(self::KEY_CACHE, $employeeTeam, $this->id);
        }
        return $employeeTeam;
    }
    
    /**
     * get schools of employee
     * 
     * @return model
     */
    public function getSchools()
    {
        if ($employeeSchools = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeSchools;
        }
        $employeeSchools = EmployeeSchool::getItemsFollowEmployee($this->id);
        CacheHelper::put(self::KEY_CACHE, $employeeSchools, $this->id);
        return $employeeSchools;
    }
    
    /**
     * get language of employee
     * 
     * @return model
     */
    public function getLanguages()
    {
        if ($employeeLanguages = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeLanguages;
        }
        $employeeLanguages = EmployeeCertificate::getItemsFollowEmployee($this->id, Certificate::TYPE_LANGUAGE);
        CacheHelper::put(self::KEY_CACHE, $employeeLanguages, $this->id);
        return $employeeLanguages;
    }
    
    /**
     * get cetificate of employee
     * 
     * @return model
     */
    public function getCetificates()
    {
        if ($employeeCetificates = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeCetificates;
        }
        $employeeCetificates = EmployeeCertificate::getItemsFollowEmployee($this->id, Certificate::TYPE_CETIFICATE);
        CacheHelper::put(self::KEY_CACHE, $employeeCetificates, $this->id);
        return $employeeCetificates;
    }
    
    /**
     * get programs of employee
     * 
     * @return model
     */
    public function getPrograms()
    {
        if ($employeeSkills = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeSkills;
        }
        $employeeSkills = EmployeeSkill::getItemsFollowEmployee($this->id, Skill::TYPE_PROGRAM);
        CacheHelper::put(self::KEY_CACHE, $employeeSkills, $this->id);
        return $employeeSkills;
    }
    
    /**
     * get database of employee
     * 
     * @return model
     */
    public function getDatabases()
    {
        if ($employeeSkills = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeSkills;
        }
        $employeeSkills = EmployeeSkill::getItemsFollowEmployee($this->id, Skill::TYPE_DATABASE);
        CacheHelper::put(self::KEY_CACHE, $employeeSkills, $this->id);
        return $employeeSkills;
    }
    
    /**
     * get os of employee
     * 
     * @return model
     */
    public function getOss()
    {
        if ($employeeSkills = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeSkills;
        }
        $employeeSkills = EmployeeSkill::getItemsFollowEmployee($this->id, Skill::TYPE_OS);
        CacheHelper::put(self::KEY_CACHE, $employeeSkills, $this->id);
        return $employeeSkills;
    }
    
    /**
     * get work experience of employee
     * 
     * @return model
     */
    public function getWorkExperience()
    {
        if ($employeeSkills = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeSkills;
        }
        $employeeSkills = WorkExperience::getItemsFollowEmployee($this->id);
        CacheHelper::put(self::KEY_CACHE, $employeeSkills, $this->id);
        return $employeeSkills;
    }
    
    
    /**
     * get project experience of employee
     * 
     * @return model
     */
    public function getProjectExperience()
    {
        if ($employeeSkills = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeSkills;
        }
        $employeeSkills = ProjectExperience::getItemsFollowEmployee($this->id);
        CacheHelper::put(self::KEY_CACHE, $employeeSkills, $this->id);
        return $employeeSkills;
    }
    
    /**
     * get roles of employee
     * 
     * @return collection
     */
    public function getRoles()
    {
        if ($employeeRole = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeRole;
        }
        $employeeRole = EmployeeRole::select('role_id', 'role')
                ->join('roles', 'roles.id', '=', 'employee_roles.role_id')
                ->where('employee_id', $this->id)
                ->orderBy('role')
                ->get();
        if ($this->id) {
            CacheHelper::put(self::KEY_CACHE, $employeeRole, $this->id);
        }
        return $employeeRole;
    }
    
    /**
     * get roles of employee
     * 
     * @return collection
     */
    public function getRoleIds()
    {
        if ($employeeRole = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeRole;
        }
        $employeeRole = EmployeeRole::select('role_id')
            ->where('employee_id', $this->id)
            ->get();
        if ($this->id) {
            CacheHelper::put(self::KEY_CACHE, $employeeRole, $this->id);
        }
        return $employeeRole;
    }

    /**
     * get model item relate of employee
     *
     * @param type $type
     * @return \Rikkei\Team\Model\class
     */
    public function getItemRelate($type)
    {
        $class = 'Rikkei\Team\Model\Employee' . ucfirst($type);
        if (!class_exists($class)) {
            $class = EmployeeContact::class;
        }
        if ($item = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $item;
        }
        $item = $class::find($this->id);
        if (!$item) {
            $item = new $class();
        }
        CacheHelper::put(self::KEY_CACHE, $item, $this->id);
        return $item;
    }
    
    /**
     * get Hobby by employeeId
     * @return model
     */
    public function getHobby()
    {
        $employeeHobby = EmployeeHobby::getItemFollowEmployee($this->id);
        return $employeeHobby;
    }
    
    /**
     * get Costume by employeeId
     * @return EmployeeCostume Description
     */
    public function getCostume()
    {
        $employeeCostume = EmployeeCostume::getEmpById($this->id);
        return $employeeCostume;
    }
    
    /**
     * get Politic by employeeId
     * @return EmployeePolitic Description
     */
    public function getPolitic()
    {
        $employeePolitic = EmployeePolitic::getEmpById($this->id);
        return $employeePolitic;
    }
    
    /**
     * get Military model by employeeId
     * @return EmployeeMilitary Description
     */
    public function getMilitary()
    {
        $employeeMilitary = EmployeeMilitary::getModelByEmplId($this->id);
        return $employeeMilitary;
    }
    
    /**
     * get relationships of employee
     * 
     * @return model
     */
    public function getRelations()
    {
        if ($employeeSkills = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeSkills;
        }
        $employeeSkills = EmployeeSkill::getItemsFollowEmployee($this->id, Skill::TYPE_PROGRAM);
        CacheHelper::put(self::KEY_CACHE, $employeeSkills, $this->id);
        return $employeeSkills;
    }
    
    /**
     * convert collection model to array with key is name column
     * 
     * @param model $collection
     * @param string $collection
     * @return array
     */
    protected static function formatArray($collection, $key = null)
    {
        if (!$collection instanceof \Illuminate\Contracts\Support\Arrayable) {
            return [];
        }
        $collectionArray = $collection->toArray();
        if (! $key) {
            return $collectionArray;
        }
        $result = [];
        foreach ($collectionArray as $item) {
            $result[$item[$key]] = $item;
        }
        return $result;
    }
    
    /**
     * get permission of employee
     * result = array route name and action id allowed follow each team
     * 
     * @return array
     */
    public function getPermission()
    {
        $permissionTeam = $this->getPermissionTeam();
        $permissionRole = $this->getPermissionRole();
        $result = [];
        if ($permissionTeam) {
            $result['team'] = $permissionTeam;
        }
        if ($permissionRole) {
            $result['role'] = $permissionRole;
        }
        return $result;
    }
    
    /**
     * get permission team of employee
     * 
     * @return array
     */
    public function getPermissionTeam()
    {        
        $teams = $this->getTeamPositons();
        if (! $teams || ! count($teams)) {
            return [];
        }
        $routesAllow = [];
        $actionIdsAllow = [];
        $actionTable = Action::getTableName();
        $permissionTable = Permission::getTableName();
        foreach ($teams as $teamMember) {
            $team = Team::find($teamMember->team_id);
            if (! $team->isFunction()) {
                continue;
            }
            $teamAs = $team->getTeamPermissionAs();
            $teamIdOrgin = $team->id;
            if ($teamAs) {
                $team = $teamAs;
            }
            $teamIdAs = $team->id;
            if ($actionIdAllow = CacheHelper::get(
                    self::KEY_CACHE_PERMISSION_TEAM_ACTION, 
                    $teamIdAs . '_' . $teamMember->role_id)) {
                $actionIdsAllow[$teamIdOrgin] = $actionIdAllow;
            } else {
                //get permission of team member
                $teamPermission = Permission::select('action_id',  'route', 'scope')
                    ->join($actionTable, $actionTable . '.id', '=', $permissionTable . '.action_id')
                    ->where('team_id', $team->id)
                    ->where('role_id', $teamMember->role_id)
                    ->get();
                if (count($teamPermission)) {
                    foreach ($teamPermission as $item) {
                        if (! $item->scope) {
                            continue;
                        }
                        if ($item->action_id) {
                            $actionIdsAllow[$teamIdOrgin][$item->action_id] = $item->scope;
                        }
                    }
                    if (isset($actionIdsAllow[$teamIdOrgin]) && $actionIdsAllow[$teamIdOrgin]) {
                        CacheHelper::put(
                            self::KEY_CACHE_PERMISSION_TEAM_ACTION, 
                            $actionIdsAllow[$teamIdOrgin],
                            $teamIdAs . '_' . $teamMember->role_id
                        );
                    }
                }
            }
            
            //get scope of route name from action id
            if (! isset($actionIdsAllow[$teamIdOrgin]) || ! count($actionIdsAllow[$teamIdOrgin])) {
                continue;
            }
            $actionIds = $actionIdsAllow[$teamIdOrgin];
            $actionIds = array_keys($actionIds);
            if ($routeAllow = CacheHelper::get(
                    self::KEY_CACHE_PERMISSION_TEAM_ROUTE,
                    $teamIdAs . '_' . $teamMember->role_id)) {
                $routesAllow[$teamIdOrgin] = $routeAllow;
                continue;
            }
            $routes = Action::getRouteChildren($actionIds);
            if (count($routes)) {
                foreach ($routes as $route => $valueIds) {
                    if ($valueIds['id'] && isset($actionIdsAllow[$teamIdOrgin][$valueIds['id']])) {
                        $routesAllow[$teamIdOrgin][$route] = $actionIdsAllow[$teamIdOrgin][$valueIds['id']];
                    } else if ($valueIds['parent_id'] && isset($actionIdsAllow[$teamIdOrgin][$valueIds['parent_id']])) {
                        $routesAllow[$teamIdOrgin][$route] = $actionIdsAllow[$teamIdOrgin][$valueIds['parent_id']];
                    }
                }
                if (isset($routesAllow[$teamIdOrgin]) && $routesAllow[$teamIdOrgin]) {
                    CacheHelper::put(
                            self::KEY_CACHE_PERMISSION_TEAM_ROUTE, 
                            $routesAllow[$teamIdOrgin],
                            $teamIdAs . '_' . $teamMember->role_id
                        );
                }
            }
        }
        
        if (! $routesAllow && ! $actionIdsAllow) {
            return [];
        }
        return [
            'route' => $routesAllow,
            'action' => $actionIdsAllow,
        ];
    }
    
    /**
     * get acl role of rule
     * 
     * @return array
     */
    protected function getPermissionRole()
    {
        $roles = $this->getRoleIds();
        if (! $roles || ! count($roles)) {
            return [];
        }
        $routesAllow = [];
        $actionIdsAllow = [];
        $routesAllowOfRole = [];
        $actionIdsAllowOfRole = [];
        $actionTable = Action::getTableName();
        $permissionTable = Permission::getTableName();
        foreach ($roles as $role) {
            if ($actionIdAllow = CacheHelper::get(
                    self::KEY_CACHE_PERMISSION_ROLE_ACTION,
                    $role->role_id)) {
                $actionIdsAllowOfRole = $actionIdAllow;
            } else {
                $rolePermission = Permission::select('action_id',  'route', 'scope')
                    ->join($actionTable, $actionTable . '.id', '=', $permissionTable . '.action_id')
                    ->where('team_id', null)
                    ->where('role_id', $role->role_id)
                    ->get();
                foreach ($rolePermission as $item) {
                    if (! $item->scope) {
                        continue;
                    }
                    if ($item->action_id) {
                        $actionIdsAllowOfRole[$item->action_id] = (int) $item->scope;
                    }
                }
                CacheHelper::put(
                    self::KEY_CACHE_PERMISSION_ROLE_ACTION, 
                    $actionIdsAllowOfRole,
                    $role->role_id
                );
            }
            
            //get scope of route name from action id
            if ($actionIdsAllowOfRole) {
                if ($routeAllow = CacheHelper::get(
                    self::KEY_CACHE_PERMISSION_ROLE_ROUTE,
                    $role->role_id)) {
                    $routesAllowOfRole = $routeAllow;
                } else {
                    $actionIds = array_keys($actionIdsAllowOfRole);
                    $routes = Action::getRouteChildren($actionIds);
                    foreach ($routes as $route => $valueIds) {
                        if ($valueIds['id'] && isset($actionIdsAllowOfRole[$valueIds['id']])) {
                            $routesAllowOfRole[$route] = $actionIdsAllowOfRole[$valueIds['id']];
                        } else if ($valueIds['parent_id'] && isset($actionIdsAllowOfRole[$valueIds['parent_id']])) {
                            $routesAllowOfRole[$route] = $actionIdsAllowOfRole[$valueIds['parent_id']];
                        }
                    }
                    CacheHelper::put(
                            self::KEY_CACHE_PERMISSION_ROLE_ROUTE, 
                            $routesAllowOfRole, 
                            $role->role_id
                    );
                }
            }
            
            //get scope greater of role for user
            foreach ($actionIdsAllowOfRole as $actionId => $scope) {
                if (isset($actionIdsAllow[$actionId]) && $actionIdsAllow[$actionId] > $scope) {
                    continue;
                }
                $actionIdsAllow[$actionId] = $scope;
            }
            
            foreach ($routesAllowOfRole as $route => $scope) {
                if (isset($routesAllow[$route]) && $routesAllow[$route] > $scope) {
                    continue;
                }
                $routesAllow[$route] = $scope;
            }
        }
        
        return [
            'route' => $routesAllow,
            'action' => $actionIdsAllow,
        ];
    }
    
    /**
     * gender to option
     * 
     * @return array
     */
    public static function toOptionGender()
    {
        return [
            [
            'value' => self::GENDER_MALE,
            'label' => Lang::get('team::view.Male')
            ],
            [
                'value' => self::GENDER_FEMALE,
                'label' => Lang::get('team::view.Female')
            ]
        ];
    }
    
    public static function labelGender() {
        return [
            self::GENDER_FEMALE => Lang::get('resource::view.Female'),
            self::GENDER_MALE => Lang::get('resource::view.Male'),
        ];
    }
    
    /**
     * get list Marital
     * @return array (label => '' , value => '')
     */
    public static function toOptionMarital()
    {
        return [
            [
                'value' => self::MARITAL_SINGLE,
                'label' => Lang::get('team::view.Single'),
            ],
            [
                'value' => self::MARITAL_MARRIED,
                'label' => Lang::get('team::view.Married'),
            ],
            [
                'value' => self::MARITAL_WIDOWED,
                'label' => Lang::get('team::view.Widowed'),
            ],
            [
                'value' => self::MARITAL_SEPARATED,
                'label' => Lang::get('team::view.Separated'),
            ],
        ];
    }
    
    /**
     * array marital (value => label)
     * @return array
     */
    public static function labelMarital()
    {
        return [
            self::MARITAL_SINGLE => Lang::get('resource::view.Single'),
            self::MARITAL_MARRIED => Lang::get('resource::view.Married'),
            self::MARITAL_WIDOWED => Lang::get('resource::view.Widowed'),
            self::MARITAL_SEPARATED => Lang::get('resource::view.Separated'),
        ];
    }
    
    /**
     * check employee allow login
     * 
     * @return boolean
     */
    public function isAllowLogin()
    {
        $joinDate = strtotime($this->join_date);
        $leaveDate = strtotime($this->leave_date);
        $nowDate = date('Y-m-d');
        if (date('Y-m-d', $joinDate) > $nowDate || ($leaveDate && date('Y-m-d', $leaveDate) <= $nowDate)) {
            return false;
        }
        return true;
    }
    
    /**
     * check is leader of a team
     * 
     * @return boolean
     */
    public function isLeader()
    {
        if ($employeeLeader = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return self::flagToBoolean($employeeLeader);
        }
        $positions = $this->getTeamPositons();
        foreach ($positions as $position) {
            $employeeLeader = Role::isPositionLeader($position->role_id);
            if ($employeeLeader) {
                break;
            }
        }
        CacheHelper::put(
            self::KEY_CACHE, 
            self::booleanToFlag($employeeLeader), 
            $this->id);
        return $employeeLeader;
    }

    /**
     * get ids of team that employee is leader
     * 
     * @return array
     */
    public function getTeamIdIsLeader()
    {
        if ($teamIds = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $teamIds;
        }
        $teamIds = [];
        $positions = $this->getTeamPositons();
        foreach ($positions as $position) {
            $employeeLeader = Role::isPositionLeader($position->role_id);
            if ($employeeLeader) {
                $teamIds[] = $position->team_id;
            }
        }
        CacheHelper::put(self::KEY_CACHE, $teamIds, $this->id);
        return $teamIds;
    }
    
    /**
     * check permission greater with another employee
     * 
     * @param model $employee
     * @param boolean $checkIsLeader
     * @return boolean
     */
    public function isGreater($employee, $checkIsLeader = false)
    {
        if ($employeeGreater = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return self::flagToBoolean($employeeGreater);
        }
        if (is_numeric($employee)) {
            $employee = Employee::find($employee);
        }
        $employeeGreater = null;
        if (! $employee) {
            $employeeGreater = false;
        } elseif ($this->id == $employee->id) {
            if ($checkIsLeader) {
                if ($this->isLeader()) {
                    $employeeGreater = true;
                } else {
                    $employeeGreater = false;
                }
            } else {
                $employeeGreater = false;
            }
        } else {
            $thisTeam = $this->getTeamPositons();
            $anotherTeam = $employee->getTeamPositons();
            $teamPaths = Team::getTeamPath();
            if (! count($thisTeam) || ! count($anotherTeam) || ! count($teamPaths)) {
                $employeeGreater = false;
            }
        }
        if ($employeeGreater === null) {
            $employeeGreater = $this->isTeamPositionGreater(
                $thisTeam, 
                $anotherTeam, 
                $teamPaths,
                $checkIsLeader
            );
        }
        CacheHelper::put(self::KEY_CACHE, self::booleanToFlag($employeeGreater), $this->id);
        return $employeeGreater;
    }
    
    /**
     * check team greater, position greater of 2 employee
     * 
     * @param model $thisTeam
     * @param model $anotherTeam
     * @param array $teamPaths
     * @return boolean
     */
    protected function isTeamPositionGreater(
        $thisTeam, 
        $anotherTeam, 
        $teamPaths, 
        $checkIsLeader = false
    ) {
        foreach ($anotherTeam as $anotherTeamItem) {
            foreach ($thisTeam as $thisTeamItem) {
                // this team is team root
                if (! isset($teamPaths[$anotherTeamItem->team_id]) ||
                    ! $teamPaths[$anotherTeamItem->team_id]) {
                    continue;
                }
                // team greater
                if (in_array($thisTeamItem->team_id, $teamPaths[$anotherTeamItem->team_id])) {
                    return true;
                }
                // 2 team diffirent branch
                if ($thisTeamItem->team_id != $anotherTeamItem->team_id) {
                    continue;
                }
                // same team, compare position
                $thisPosition = Role::find($thisTeamItem->role_id);
                $anotherPosition = Role::find($anotherTeamItem->role_id);
                if (! $thisPosition || 
                    ! $anotherPosition || 
                    ! $thisPosition->isPosition() || 
                    ! $anotherPosition->isPosition()) {
                    continue;
                }
                if ($checkIsLeader) {
                    if ($thisPosition->isLeader()) {
                        return true;
                    }
                    return false;
                }
                if ($thisPosition->sort_order < $anotherPosition->sort_order) {
                    return true;
                }
            }
        }
        return false;
    }
    
    public static function checkEmailExist($email){
        $count = self::where('email',$email)->withTrashed()->count();
        
        if($count > 0) {
            return true;
        }
        return false;
    }
    
    public static function checkNicknameExist($nickname){
        $count = self::where('nickname',$nickname)->withTrashed()->count();
        
        if($count > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * get employee follow email
     * 
     * @param string $chart
     * @return colleciton
     */
    public static function getEmpLikeEmail($chart) 
    {
        $employeeEmail = self::select('name','email','japanese_name')
                ->where('email','like',"$chart%")
                ->orderBy('nickname')
                ->take(5)
                ->get();
        return $employeeEmail;
            
    }
    
    public static function getEmpByEmail($email) {
        return self::where('email','=',$email)
                ->select('*')
                ->first();
    }

    /*
     * get all employee
     */
    public static function getAllEmployee($order=null, $dir=null, $leave = false) {
        if ($employee = CacheHelper::get(self::KEY_CACHE)) {
            return $employee;
        }
        if (!$order) {
            $order = 'name';
        }
        if (!$dir) {
            $dir = 'asc';
        }
        $employee = self::select(['id', 'name', 'email'])
                ->orderby($order, $dir);
        if (!$leave) {
            $employee->whereNull('leave_date');
        }
        CacheHelper::put(self::KEY_CACHE, $employee->get());
        return $employee->get();
    }

    /**
     * get employee follow id
     * 
     * @param type $id
     * @return type
     */
    public static function getEmpById($id) {
        if ($emp = CacheHelper::get(self::KEY_CACHE, $id)) {
            return $emp;
        }
        $emp = self::find($id);
        CacheHelper::put(self::KEY_CACHE, $emp, $id);
        return $emp;
    }

    /*
     * get id employee by email
     * @param string
     * return int
     */
    public static function getIdEmpByEmail($email, $type = 1) {
        if (is_array($email)) {
            $employee = self::whereIn('email', $email);
        } else {
            $employee = self::where('email', $email);
        }
        $employee = $employee->select('id')
            ->first();
        switch ($type) {
            case 2: // return object
                return $employee;
            default: // return id
                if ($employee) {
                    return $employee->id;
                }
                return null;
        }
    }

    /**
     * get employee by emails
     * 
     * @param type $email
     * @return type
     */
    public static function getEmpItemByEmail($email)
    {
        if (is_array($email)) {
            $employee = self::whereIn('email', $email);
        } else {
            $employee = self::where('email', $email);
        }
        return $employee->first();
    }

    /*
     * get name employee by id
     * @param string
     * return int
     */
    public static function getNameEmpById($id) {
        if (!$id) {
            return;
        }
        $employee = self::find($id);
        if (!$employee) {
            return null;
        }
        return $employee->name;
    }

    /*
     * get email employee by id
     * @param string
     * return int
     */
    public static function getEmailEmpById($id) {
        if (!$id) {
            return;
        }
        $employee = self::find($id);
        if ($employee) {
            return $employee->email;
        }
        return null;
    }

    public function teams() {
        return $this->belongsToMany('\Rikkei\Team\Model\Team', 'team_members', 'employee_id', 'team_id');
    }

    /**
     * check current employee is COO
     * @return boolean
     */
    public function isCOO() {
        $coo_accs = CoreConfigData::getCOOAccount();
        return in_array($this->email, $coo_accs);
    }

    /**
     * check current employee is Leader PQA
     * @return type
     */
    public function isLeaderPQA() {
        $pqaAccount = CoreConfigData::getQAAccount();
        return in_array($this->email, $pqaAccount);
    }

    /**
     * get employee by email
     * @param string
     * @return array
     */
    public static function getEmployeeByEmail($email)
    {
        return self::select(['id', 'email'])
            ->withTrashed()
            ->where('email', $email)
            ->first();
    }

    /**
     * check nick name exists
     * @param string
     * @return boolean
     */
    public static function checkNickNameExists($nickname)
    {
        return self::where('nickname', $nickname)->first() ? true : false;
    }

    /**
     * seach employee by email
     * 
     * @param string $email
     * @param int $page
     * @param ing $limit
     * @return array
     */
    public static function searchAjax($email, array $config = [])
    {
        $result = [];
        $arrayDefault = [
            'page' => 1,
            'limit' => 20,
            'typeExclude' => null
        ];
        $config = array_merge($arrayDefault, $config);
        $collection = self::select('id', 'email', 'name')
            ->where(function($query) use ($email) {
                $query->orWhere('email', 'LIKE', '%' . $email . '%')
                    ->orWhere('name', 'LIKE', '%' . $email . '%');
            })
            ->orderBy('email');
        switch ($config['typeExclude']) {
            case self::EXCLUDE_REVIEWER: // not show reviewer
                if (!Input::get('task')) {
                    break;
                }
                $collection->whereNotIn('id', function($query) {
                    $query->select('employee_id')
                        ->from(TaskAssign::getTableName())
                        ->where('task_id', Input::get('task'))
                        ->where('role', TaskAssign::ROLE_REVIEWER);
                });
                $now = Carbon::now();
                $collection->where(function($query) use ($now) {
                    $query->orWhereNull('leave_date')
                        ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
                });
                break;

            case self::EXCLUDE_UTILIZATION:  
                if (PermissionView::getInstance()->isScopeTeam(null, 'resource::dashboard.utilization')) {
                    $curEmp = PermissionView::getInstance()->getEmployee();
                    $teamsOfEmp = CheckpointPermission::getArrTeamIdByEmployee($curEmp->id);
                    $teamMemberTable = TeamMember::getTableName();
                    $empTable = self::getTableName();
                    $collection->join("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=", "{$empTable}.id")
                               ->whereIn("{$teamMemberTable}.team_id", $teamsOfEmp);
                }
                break;
            case 'not': // show full all employee of company
                break;
            default: // show employee not leave
                $now = Carbon::now();
                $collection->where(function($query) use ($now) {
                    $query->orWhereNull('leave_date')
                        ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
                });
                break;
        }
        self::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        
        if(isset($config['fullName']) && $config['fullName']) {
            foreach ($collection as $item) {
                $result['items'][] = [
                    'id' => $item->id,
                    'text' => e($item->name) . ' (' 
                        . View::getNickName($item->email) . ')',
                ];
            }
            return $result;
        }
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => View::getNickName($item->email),
            ];
        }
        return $result;
    }
    
    /**
     * get employee by team id
     * 
     * @param int $teamId
     * @return object
     */
    public static function getEmpByTeam($teamId) {
        $empTable = self::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        return self::join("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=", "{$empTable}.id")
                   ->where("{$teamMemberTable}.team_id", $teamId)
                   ->whereRaw('leave_date is null or leave_date > ?', [date('Y-m-d')])
                   ->select("{$empTable}.id", "{$empTable}.email", "{$empTable}.name")
                   ->distinct()
                   ->get();
    }

    /**
     * get employee for checkpoint by team id and start date checkpoint
     * 
     */
    public static function getEmpForCheckpoint($teamId, $startDate)
    {
        $empTable = self::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        return self::join("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=", "{$empTable}.id")
                   ->where("{$teamMemberTable}.team_id", $teamId)
                   ->whereRaw('leave_date is null or leave_date > ?', [$startDate])
                   ->select("{$empTable}.id", "{$empTable}.email", "{$empTable}.name")
                   ->distinct()
                   ->get();
    }

    /**
     * get all employee for checkpoint follow start date checkpoint
     */
    public static function getAllEmpForCheckpoint($startDate, $order=null, $dir=null)
    {
        if ($employee = CacheHelper::get(self::KEY_CACHE)) {
            return $employee;
        }
        if (!$order) {
            $order = 'name';
        }
        if (!$dir) {
            $dir = 'asc';
        }
        $employee = self::select(['id', 'name', 'email'])
        ->whereRaw('leave_date is null or leave_date > ?', [$startDate])
                ->orderby($order, $dir);
        CacheHelper::put(self::KEY_CACHE, $employee->get());
        return $employee->get();
    }

        /**
     * get employee by team ids
     * 
     * @param int $teamIds
     * @return object
     */
    public static function getEmpByTeams($teamIds, $order = 'email', $dir = 'asc') {
        $empTable = self::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        return self::join("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=", "{$empTable}.id")
                   ->whereIn("{$teamMemberTable}.team_id", $teamIds)
                   ->select("{$empTable}.id", "{$empTable}.email", "{$empTable}.name")
                   ->get();
    }

    /**
     * get employee by ids
     * 
     * @param type $ids
     * @return type
     */
    public static function getEmpByIds(array $ids) {
        return self::whereIn('id', $ids)->select('*')->get();
    }

    /*
     * get name employee by id
     * 
     * @param int
     * return string
     */
    public static function getNameEmailById($id) 
    {
        $item = self::select('name', 'email')
            ->where('id', $id)
            ->first();
        return $item;
    }

    /**
     * get all employee of team (and self child)
     * 
     * @param int $teamId
     * @param array $where
     * @return object
     */
    public static function getAllEmployeesOfTeam($teamId, array $where = [])
    {
        $teamPath = Team::getTeamPath();
        if (isset($teamPath[$teamId]['child'])) {
            $teams = $teamPath[$teamId]['child'];
            $teams[] = $teamId;
        } else {
            $teams[] = $teamId;
        }
        $tableEmployee = self::getTableName();
        $tableTeamEmployee = TeamMember::getTableName();
        
        $collection = self::select($tableEmployee.'.id', 
            $tableEmployee.'.email', $tableEmployee.'.name')
            ->whereNull($tableEmployee.'.leave_date')
            ->join($tableTeamEmployee, $tableTeamEmployee.'.employee_id',
                '=', $tableEmployee.'.id')
            ->whereIn($tableTeamEmployee.'.team_id', $teams)
            ->groupBy($tableEmployee.'.id');
        if (isset($where['gender'])) {
            $collection->where('gender', $where['gender']);
        }
        return $collection->get();
    }
    
    /**
     * get all employee active
     * 
     * @return collection
     */
    public static function getEmailNameEmployeeJoin()
    {
        return self::select('id', 'email', 'name')
            ->whereNull('leave_date')
            ->get();
    }
    
    /**
     * get all employee is pm or leader
     * @return array
     * 
     */
    public static function getAllEmployeeIsPmOrLeader()
    {
        $tableEmployeeRole = EmployeeRole::getTableName();
        $tableRole = Role::getTableName();
        $tableEmployee = self::getTableName();
        $tableTeam = Team::getTableName();
        $listPm = self::join($tableEmployeeRole, $tableEmployee. '.id', '=', $tableEmployeeRole. '.employee_id')
                            ->join($tableRole, $tableEmployeeRole. '.role_id', '=', $tableRole. '.id')
                            ->where($tableRole .'.role' , '=', 'pm')
                            ->select([$tableEmployee.'.id', $tableEmployee. '.name', $tableEmployee. '.email'])
                            ->orderBy($tableEmployee. '.name');
        return self::join($tableTeam, $tableEmployee. '.id', '=', $tableTeam. '.leader_id')
                            ->select([$tableEmployee.'.id', $tableEmployee. '.name', $tableEmployee. '.email'])
                            ->orderBy($tableEmployee. '.name')
                            ->union($listPm)
                            ->groupBy($tableEmployee.'.id')
                            ->get();
    }
    
    /**
     * Count employee by team
     * 
     * @param array $teamIds
     * @return int
     */
    public static function countEmployeeByTeams($teamIds) {
        $teamMemberTable = TeamMember::getTableName();
        $empTable = self::getTableName();
        return self::join("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=" , "{$empTable}.id")
            ->whereIn('team_id', $teamIds)
            ->whereNull("{$empTable}.leave_date")
            ->select(DB::raw("distinct {$empTable}.id"))
            ->count();
    }
    
     /**
      * Get $id employees by nickname
      * @param string $nickname
      * @return array id employees
    */
    public static function getIdByNickName($nickName) {
        $arrayNickName = array_map('trim',explode(",",$nickName));
        $arrayIdEmploy = self::select('email','id')->get()->toArray();
        $arrayId = array();
        foreach ($arrayIdEmploy as $valueIdEmploy) {
            foreach ($arrayNickName as $valueNickName) {
                if (strtolower(View::getNickName($valueIdEmploy['email'])) == strtolower($valueNickName)) {
                    array_push($arrayId, $valueIdEmploy['id']);
                }
            } 
        }
        $stringId = "";
        foreach ($arrayId as $keyId => $valueId) {
            if($keyId == 0) $stringId = $valueId;
            else $stringId = $stringId.",".$valueId;
        }
        if(!empty($stringId)) {
            return $stringId;
        } else {
            return null;
        }
    }
    
    /**
     * Get count new employee in month
     * 
     * @param int $year
     * @param int $month
     * @return int
     */
    public static function getNewEmpInMonth($year, $month, $teamIds) {
        $firstLastMonth = ResourceView::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $start = $firstLastMonth[0];
        $end = $firstLastMonth[1];
        $teamMemberTable = TeamMember::getTableName();
        $EmployeeTable = self::getTableName();
        $teamTable = Team::getTableName();
        $result = self::join("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=", "{$EmployeeTable}.id")
            ->join("{$teamTable}", "{$teamTable}.id", "=", "{$teamMemberTable}.team_id")
            ->whereBetween('join_date', [$start, $end])
            ->where("is_soft_dev", Team::IS_SOFT_DEVELOPMENT);
        if ($teamIds) {
            $result->whereIn("{$teamMemberTable}.team_id", $teamIds);
        }  
        return $result->count();
    }
    
    public static function getEmpOutInMonth($year, $month, $teamId)
    {
        $firstLastMonth = ResourceView::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $start = $firstLastMonth[0];
        $end = $firstLastMonth[1];
        $teamMemberTable = TeamMember::getTableName();
        $EmployeeTable = self::getTableName();
        return self::join("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=", "{$EmployeeTable}.id")
                ->whereBetween('leave_date', [$start, $end])
                ->where("{$teamMemberTable}.team_id", $teamId)
                ->count();
                
    }
    
    /*
     * get name employee by email
     * 
     * @param int
     * return string
     */
    public static function getNameByEmail($email) 
    {
        return self::select('name', 'id')
            ->where('email', $email)
            ->first();
    }
    
    /**
     * get Role and team employee
    */
    public static function getRoleById($id) {
        $role = TeamMember::where('employee_id',$id)
            ->leftJoin('roles','team_members.role_id','=','roles.id')
            ->leftJoin('teams','team_members.team_id','=','teams.id')
            ->select('roles.role','teams.name','teams.id')->get();
        if ($role) {
            return $role;
        }
        return null;
    }

    /**
     * @param array email employee
     * @return array info emplpyee in company
     * get array email in company
     */
    public static function getArrayEmail($email,$type) {
        $arrayEmail = array();
        foreach($email as $valueIt) {
            array_push($arrayEmail,trim($valueIt));
        }
        $arrayEmailIt = Employee::whereIn('email',$arrayEmail)->select($type)->get()->toArray();
        return $arrayEmailIt;
    }
    /**
     * @param int email's id
     * @return string Name Email
     */
    public static function getNickNameById($id) {
        $employee = self::find($id);
        if (!$employee) {
            return null;
        }
        return strstr($employee->email, '@', true);
    }
    
    public static function searchAjaxWithTeamName($email, $config = []) {
        $result = [];
        $arrayDefault = [
            'page' => 1,
            'limit' => 10,
            'typeExclude' => null
        ];
        $config = array_merge($arrayDefault, $config);
        $empTable = self::getTableName();
        $teamMemTable = TeamMember::getTableName();
        $teamTable = Team::getTableName();
        $collection = self::join("{$teamMemTable}", "{$teamMemTable}.employee_id", "=", "{$empTable}.id")
            ->join("{$teamTable}", "{$teamTable}.id", "=", "{$teamMemTable}.team_id")
            ->where("{$teamTable}.is_soft_dev", Team::IS_SOFT_DEVELOPMENT)
            ->where(function ($query) use ($email, $empTable) {
                $query->where("{$empTable}.email", 'LIKE', '%' . $email . '%')
                    ->orWhere("{$empTable}.name", 'LIKE', '%' . $email . '%');
            })
            ->orderBy('email')
            ->groupBy("{$empTable}.id")
            ->select(
                    "{$empTable}.id",
                    "{$empTable}.email",
                    "{$empTable}.name",
                    DB::raw("group_concat({$teamTable}.name SEPARATOR ', ') AS team")
            );
        self::pagerCollection($collection, $config['limit'], $config['page']);
        foreach ($collection as $item) {
            $result[] = [
                'data' => $item->id,
                'value' => View::getNickName($item->email) . ' - ' . $item->name . ' (' . $item->team . ')',
            ];
        }
        return $result;
    }
    
    /**
     * get all pm of system
     * 
     * @return array
     */
    public static function getAllPM()
    {
        if ($result = CacheHelper::get(self::KEY_CACHE)) {
            return $result;
        }
        $tableEmployeeRole = EmployeeRole::getTableName();
        $tableRole = Role::getTableName();
        $tableEmployee = self::getTableName();
        $pmRoleName = 'pm';
        
        $collection = self::select([$tableEmployee.'.id', 
            $tableEmployee. '.name', $tableEmployee. '.email'])
            ->join($tableEmployeeRole, $tableEmployee. '.id', '=',
                $tableEmployeeRole. '.employee_id')
            ->join($tableRole, $tableEmployeeRole. '.role_id', '=', $tableRole. '.id')
            ->where($tableRole .'.role' , '=', $pmRoleName)
            ->orderBy($tableEmployee. '.name')
            ->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        foreach ($collection as $item) {
            $item->id = (int) $item->id;
            $result[(int) $item->id] = [
                'id' => $item->id,
                'name' => $item->name,
                'email' => $item->email,
            ];
        }
        CacheHelper::put(self::KEY_CACHE, $result);
        return $result;
    }
    
    /**
     * update employee code from employee card id
     * @param type $teamId
     * @param type $cardId
     */
    public function generateEmpCode($teamId, $cardId = null) {
        if (!$cardId) {
            $cardId = $this->employee_card_id;
        }
        
        $this->employee_code = static::getCodeFromCardId($cardId, $teamId);
        $this->save();
    }
    
    /**
     * Get code prefix of employee
     * 
     * @param int $teamId
     * @return string
     */
    public static function getCodePrefix($teamId)
    {
        $prefix = 'NV';
        $excerptTeams = [
            [
                'code' => TeamConst::CODE_JAPAN,
                'prefix' => 'JP'
            ],
            [
                'code' => TeamConst::CODE_DANANG,
                'prefix' => 'DN'
            ]
        ];
        foreach ($excerptTeams as $teamArr) {
            $team = Team::where('code', $teamArr['code'])->select('id')->first();
            if (!$team) {
                continue;
            }
            $teamChildIds = TeamList::getTeamChildIds($team->id);
            if (in_array($teamId, $teamChildIds)) {
                $prefix = $teamArr['prefix'];
                break;
            }
        }
        return $prefix;
    }
    
    /**
     * Get code from cardId and team
     * 
     * @param int $cardId
     * @param int $teamId
     * @return string
     */
    public static function getCodeFromCardId($cardId, $teamId)
    {
        return static::getCodePrefix($teamId) . sprintf('%07d', intval($cardId));
    }
    
    /**
     * generate suggest card id by team
     * @param int $teamId
     * @return int
     */
    public static function genSuggestCardId($teamId)
    {
        $prefix = self::getCodePrefix($teamId);   
        return intval(self::where('employee_code', 'like', $prefix . '%')->max('employee_card_id')) + 1;
    }
    
    /**
     * Get employee update leave_at at today
     * 
     * @return Employee collection
     */
    public static function getEmpUpdatedToday()
    {
        return self::whereRaw("DATE(updated_at) = CURDATE()")
                ->select('id', 'leave_date')
                ->get();
    }
    
    /**
     * Get employee join or leave in month
     * 
     * @param int $month
     * @param int $year
     * @return Employee collection
     */
    public static function getEmpJoinOrLeaveInMonth($month, $year)
    {
        return self::whereRaw("MONTH(join_date) = ? AND YEAR(join_date) = ?", [$month, $year])
                ->orWhereRaw("MONTH(leave_date) = ? AND YEAR(leave_date) = ?", [$month, $year])
                ->select('id', 'join_date', 'leave_date')
                ->get();
    }

    /**
     * Get employee's retired date
     * 
     * @param int $id
     * @return date
     */
    public static function retiredDate($id)
    {
        return self::where('id', $id)
        ->select('leave_date')
        ->first()->leave_date;
    }

    /**
     * get account of employee
     */
    public function getAccount()
    {
        return preg_replace('/\s|@.*/', '', $this->email);
    }

    /**
     * Option folk lib
     */
    public static function toOptionFolk()
    {
        $folk = self::LIB_FOLK;
        $newArr = [];
        foreach($folk as $key => $value) {
            $a = [
                'value' => $key,
                'label' => $value,
            ];
            $newArr[] = $a;
        }
        return $newArr;
    }

    /**
     * get list option religion
     * @return array
     */
    public static function toOptionReligion()
    {
        $folk = self::LIB_RELIG;
        $newArr = [];
        foreach($folk as $key => $value) {
            $a = [
                'value' => $key,
                'label' => $value,
            ];
            $newArr[] = $a;
        }
        return $newArr;
    }

    /**
     * get work experience of employee
     * 
     * @return model
     */
     public function getWorkExperienceJapan()
    {
        if ($employeeSkills = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeSkills;
        }
        $employeeSkills = EmployeeWorkExperienceJapan::getItemsFollowEmployee($this->id);
        CacheHelper::put(self::KEY_CACHE, $employeeSkills, $this->id);
        return $employeeSkills;
    }

    /**
     * get projectExperience by employeeId and experienceId
     * @return $model
     */
    public function getProjectExperienceGroupWork($workIds = array())
    {
        $employeeSkills = ProjectExperience::getItemsFollowExperience($this->id, $workIds);
        return $employeeSkills;
    }
}
