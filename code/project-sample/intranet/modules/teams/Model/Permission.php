<?php
namespace Rikkei\Team\Model;

use DB;
use Lang;
use Exception;
use Rikkei\Core\View\CacheHelper;

class Permission extends \Rikkei\Core\Model\CoreModel
{
    /*
     * flag value scope
     */
    const SCOPE_NONE = 0;
    const SCOPE_SELF = 1;
    const SCOPE_TEAM = 2;
    const SCOPE_COMPANY = 3;
 
    protected $table = 'permissions';
    public $timestamps = false;


    /**
     * get all scope
     * 
     * @return array
     */
    public static function getScopes()
    {
        return [
            'none' => self::SCOPE_NONE,
            'self' => self::SCOPE_SELF,
            'team' => self::SCOPE_TEAM,
            'company' => self::SCOPE_COMPANY,
        ];
    }
    
    /**
     * get scope to assign default
     * 
     * @return int
     */
    public static function getScopeDefault()
    {
        return self::SCOPE_NONE;
    }
    
    /**
     * get scope format option
     * 
     * @return array
     */
    public static function toOption()
    {
        $scopeIcon = self::scopeIconArray();
        return [
            ['value' => self::SCOPE_NONE, 'label' => $scopeIcon[self::SCOPE_NONE]],
            ['value' => self::SCOPE_SELF, 'label' => $scopeIcon[self::SCOPE_SELF]],
            ['value' => self::SCOPE_TEAM, 'label' => $scopeIcon[self::SCOPE_TEAM]],
            ['value' => self::SCOPE_COMPANY, 'label' => $scopeIcon[self::SCOPE_COMPANY]],
        ];
    }
    
    /**
     * get scope format icon
     * 
     * @return array
     */
    public static function scopeIconArray()
    {
        return [
            self::SCOPE_NONE => '',
            self::SCOPE_SELF => '<i class="fa fa-times"></i>',
            self::SCOPE_TEAM => '<i class="fa fa-caret-up"></i>',
            self::SCOPE_COMPANY => '<i class="fa fa-circle-o"></i>',
        ];
    }
    
    /**
     * get scope guide follow icon
     * 
     * @return string
     */
    public static function getScopeIconGuide()
    {
        $scopesLabel = [
            self::SCOPE_NONE => 'none permission',
            self::SCOPE_SELF => 'self',
            self::SCOPE_TEAM => 'their management team',
            self::SCOPE_COMPANY => 'company',
        ];
        $html = '<p>' . Lang::get('team::view.Note') . ':</p>';
        $html .= '<ul>';
        $scopeIcon = self::scopeIconArray();
        foreach ($scopesLabel as $scopeValue =>$scopeLabel) {
            $html .= '<li>';
            $html .= '<b>' . $scopeIcon[$scopeValue] . '</b>: ';
            $html .= '<span>' . Lang::get('team::view.Scope '. $scopeLabel) . '</span>';
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }
    
    /**
     * save permission
     * 
     * @param array $data
     * @param int $teamOrRoleId
     * @param boolean $flagAddTeam
     */
    public static function saveRule(array $data = [], $teamOrRoleId = null, $flagAddTeam = true) {
        if (! $data) {
            return;
        }
        DB::beginTransaction();
        try {
            foreach ($data as $item) {
                if ($flagAddTeam) {
                    $item['team_id'] = $teamOrRoleId;
                } else {
                    $item['team_id'] = null;
                    $item['role_id'] = $teamOrRoleId;
                }
                $permissionItem = self::select('*')//withTrashed()
                    ->where('role_id', $item['role_id'])
                    ->where('action_id', $item['action_id'])
                    ->where('team_id', $item['team_id'])
                    ->first();
                if (!$permissionItem) {
                    $permissionItem = new Permission();
                }
                $permissionItem->deleted_at = null;
                $permissionItem->setData($item);
                $permissionItem->save();
                CacheHelper::forget(
                    Employee::KEY_CACHE_PERMISSION_TEAM_ACTION,
                    $item['team_id'] . '_' . $item['role_id']
                );
                CacheHelper::forget(
                    Employee::KEY_CACHE_PERMISSION_TEAM_ROUTE,
                    $item['team_id'] . '_' . $item['role_id']
                );
                CacheHelper::forget(
                    Employee::KEY_CACHE_PERMISSION_ROLE_ACTION,
                    $item['role_id']
                );
                CacheHelper::forget(
                    Employee::KEY_CACHE_PERMISSION_ROLE_ROUTE,
                    $item['role_id']
                );
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;   
        }
    }
    
    /**
     * get permission of team
     * 
     * @param int $teamid
     * @return collection model
     */
    public static function getTeamPermission($teamid)
    {
        return self::select('role_id', 'action_id', 'scope')
            ->where('team_id', $teamid)
            ->get();
    }
    
    /**
     * get permission of role
     * 
     * @param int $roleId
     * @return collection model
     */
    public static function getRolePermission($roleId)
    {
        return self::select('action_id', 'scope')
            ->where('role_id', $roleId)
            ->get();
    }
    
    /**
     * rewrite delete model
     * 
     * @return type
     * @throws Exception
     */
    public function delete() {
        try {
            return parent::delete();
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
