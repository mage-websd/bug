<?php
namespace Rikkei\Team\View;

use Auth;
use Route;
use Rikkei\Team\Model\Permission as PermissionModel;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\Http\Controllers\AuthController;
use Rikkei\Core\Model\CoreConfigData;

/**
 * class permission
 * 
 * check permssion auth
 */
class Permission
{
    /**
     *  store this object
     * @var object
     */
    protected static $instance;
    
    /**
     * store user current logged
     * @var model
     */
    protected $employee;
    /**
     * store rules of current user
     * @var array
     */
    protected $rules;
    
    /**
     * contructor
     */
    public function __construct() 
    {
        $this->initEmployee();
        $this->initRules();
    }

    /**
     * init User loggined
     * 
     * @return \Rikkei\Team\View\Permission
     */
    public function initEmployee()
    {
        if (!$this->employee && Auth::user()) {
            $this->employee = Auth::user()->getEmployee();
        }
        if ($this->isRoot()) {
            return $this;
        }
        if (!$this->employee || !$this->employee->isAllowLogin()) {
            $auth = new AuthController();
            return $auth->logout(Lang::get('core::message.You donot have permission login'));
        }
        return $this;
    }
    
    /**
     * init Rules
     * 
     * @return \Rikkei\Team\View\Permission
     */
    public function initRules()
    {
        if ($this->isRoot() || !$this->employee) {
            return $this;
        }
        if (!$this->rules) {
            $this->rules = $this->employee->getPermission();
            if (!$this->rules) {
                $this->rules = ['checked' => true];
            }
        }
        return $this;
    }
    
    /**
     * get scopes of teams in a route
     * 
     * @param int $teamId
     * @param string|int|null $routeOrActionId
     * @return int|array
     */
    public function getScopeCurrentOfTeam($teamId = null, $routeOrActionId = null)
    {
        if ($this->isRoot()) {
            return PermissionModel::SCOPE_COMPANY;
        }
        if (! $routeOrActionId) {
            $routeCurrent = Route::getCurrentRoute()->getName();
        } else {
            $routeCurrent = $routeOrActionId;
        }
        if (! $this->rules || ! isset($this->rules['team'])) {
            return PermissionModel::SCOPE_NONE;
        }
        $scopes = [];
        //if route current is number: check action id
        if (is_numeric($routeCurrent)) {
            $rulesTeam = $this->rules['team']['action'];
        } else { //if route current is string: check route name
            $rulesTeam = $this->rules['team']['route'];
        }
        foreach ($rulesTeam as $teamIdRule => $rules) {
            if ($teamId && $teamId != $teamIdRule) {
                continue;
            }
            foreach ($rules as $routePermission => $scope) {
                //check all permission
                if ($routePermission == '*') {
                    $routePermission = '.*';
                }
                $flagCheck = false; //search route action
                if (strpos($routePermission, '*') !== false) {
                    if (preg_match('/' . $routePermission . '/', $routeCurrent)) {
                        $flagCheck = true;
                    }
                } else {
                    if ($routeCurrent == $routePermission) {
                        $flagCheck = true;
                    }
                }
                if ($flagCheck) {
                    if ($teamId) {
                        return $scope;
                    }
                    $scopes[$teamIdRule] = $scope;
                }
            }
        }
        
        if (! $scopes) {
            return PermissionModel::SCOPE_NONE;
        }
        //get first element
        if (count($scopes) == 1) {
            return reset($scopes);
        }
        return $scopes;
    }

    /**
     * get scopes of roles in a route
     * 
     * @param string|int|null $routeOrActionId
     * @return int
     */
    public function getScopeCurrentOfRole($routeOrActionId = null)
    {
        if ($this->isRoot()) {
            return PermissionModel::SCOPE_COMPANY;
        }
        if (! $routeOrActionId) {
            $routeCurrent = Route::getCurrentRoute()->getName();
        } else {
            $routeCurrent = $routeOrActionId;
        }
        if (! $this->rules || ! isset($this->rules['role'])) {
            return PermissionModel::SCOPE_NONE;
        }
        $scopeResult = 0;
        //if route current is number: check action id
        if (is_numeric($routeCurrent)) {
            $rulesRole = $this->rules['role']['action'];
        } else { //if route current is string: check route name
            $rulesRole = $this->rules['role']['route'];
        }
        foreach ($rulesRole as $routePermission => $scope) {
            if ($routePermission == '*') {
                $routePermission = '.*';
            }
            $flagCheck = false; //search route action
            if (is_numeric($routeCurrent)) {
                if ($routeCurrent == $routePermission) {
                    $flagCheck = true;
                }
            } else {
                if (strpos($routePermission, '*') !== false) {
                    if (preg_match('/' . $routePermission . '/', $routeCurrent)) {
                        $flagCheck = true;
                    }
                } else {
                    if ($routeCurrent == $routePermission) {
                        $flagCheck = true;
                    }
                }
            }
            if ($flagCheck) {
                return $scope;
            }
        }
        return PermissionModel::SCOPE_NONE;
    }
    
    /**
     * check allow access to route current
     * 
     * @param string|null $route route name
     * @return boolean
     */
    public function isAllow($route = null)
    {
        if ($this->isRoot()) {
            return true;
        }
        if ($this->isScopeNone(null, $route)) {
            return false;
        }
        return true;
    }
    
    /**
     * check is scope none
     * 
     * @param int $teamId
     * @param string|int|null $route route name
     * @return boolean
     */
    public function isScopeNone($teamId = null, $route = null)
    {
        if ($this->isRoot()) {
            return false;
        }
        if ($this->getScopeCurrentOfRole($route) != PermissionModel::SCOPE_NONE) {
            return false;
        }
        $scopeTeam = $this->getScopeCurrentOfTeam($teamId, $route);
        // scope team is int
        if (is_numeric($scopeTeam)) {
            if ($scopeTeam != PermissionModel::SCOPE_NONE) {
                return false;
            }
            return true;
        }
        // scope team is array
        foreach ($scopeTeam as $scope) {
            if ($scope != PermissionModel::SCOPE_NONE) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * check is scope self
     * 
     * @param int $teamId
     * @param string|null $route route name
     * @return boolean
     */
    public function isScopeSelf($teamId = null, $route = null)
    {
        if ($this->getScopeCurrentOfRole($route) == PermissionModel::SCOPE_SELF) {
            return true;
        }
        $scopeTeam = $this->getScopeCurrentOfTeam($teamId, $route);
        // scope team is int
        if (is_numeric($scopeTeam)) {
            if ($scopeTeam == PermissionModel::SCOPE_SELF) {
                return true;
            }
            return false;
        }
        // scope team is array
        foreach ($scopeTeam as $scope) {
            if ($scope == PermissionModel::SCOPE_SELF) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * check is scope team
     * 
     * @param int $teamId
     * @param string|null $route route name
     * @return boolean
     */
    public function isScopeTeam($teamId = null, $route = null)
    {
        if ($this->getScopeCurrentOfRole($route) == PermissionModel::SCOPE_TEAM) {
            return true;
        }
        $scopeTeam = $this->getScopeCurrentOfTeam($teamId, $route);
        // scope team is int
        if (is_numeric($scopeTeam)) {
            if ($scopeTeam == PermissionModel::SCOPE_TEAM) {
                return true;
            }
            return false;
        }
        // scope team is array
        foreach ($scopeTeam as $scope) {
            if ($scope == PermissionModel::SCOPE_TEAM) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * check is scope company
     *
     * @param int $teamId
     * @param string|null $route route name
     * @return boolean
     */
    public function isScopeCompany($teamId = null, $route = null)
    {
        if ($this->isRoot()) {
            return true;
        }
        if (!$route) {
            $route = Route::getCurrentRoute()->getName();
        }
        if ($this->getScopeCurrentOfRole($route) == PermissionModel::SCOPE_COMPANY) {
            return true;
        }
        $scopeTeam = $this->getScopeCurrentOfTeam($teamId, $route);
        // scope team is int
        if (is_numeric($scopeTeam)) {
            if ($scopeTeam == PermissionModel::SCOPE_COMPANY) {
                return true;
            }
            return false;
        }
        // scope team is array
        foreach ($scopeTeam as $scope) {
            if ($scope == PermissionModel::SCOPE_COMPANY) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * get team of employee
     * 
     * @return array|null
     */
    public function getTeams()
    {
        if ($this->rules && 
                isset($this->rules['team']) && 
                isset($this->rules['team']['route'])
        ) {
            return array_keys($this->rules['team']['route']);
        }
        return [];
    }
    
    /**
     * get root account from file .env
     * 
     * @return string|null
     */
    public function getRootAccount()
    {
        return trim(config('services.account_root'));
    }
    
    /**
     * get qa account from file .env
     * 
     * @return string|null
     */
    public function getQAAccount()
    {
        return CoreConfigData::getQAAccount();
    }
    
    /**
     * get coo account from file .env
     * 
     * @return string|null
     */
    public function getCOOAccount()
    {
        return CoreConfigData::getCOOAccount();
    }
    
    /**
     * check current user is root
     * 
     * @return boolean
     */
    public function isRoot()
    {
        if ($this->employee && $this->getRootAccount() == $this->employee->email) {
            return true;
        }
        return false;
    }
    
    /**
     * check current user is root
     * 
     * @return boolean
     */
    public function isCOOAccount()
    {
        if ($this->employee && in_array($this->employee->email, $this->getCOOAccount())) {
            return true;
        }
        return false;
    }
    
    /**
     * get employee current
     * 
     * @return null|model
     */
    public function getEmployee()
    {
        return $this->employee;
    }
    
    /**
     * get permission of employee current
     * 
     * @return array|null
     */
    public function getPermission()
    {
        return $this->rules;
    }
    
    /**
     * Singleton instance
     * 
     * @return \Rikkei\Team\View\Permission
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }
    
}
