<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Illuminate\Support\Facades\Lang;
use Exception;
use Rikkei\Core\Model\MenuItem;
use Rikkei\Team\Model\Permission;
use Rikkei\Core\View\CacheHelper;

class Action extends CoreModel
{
    protected $table = 'actions';
    
    /**
     * get action list
     * 
     * @return array
     */
    public static function getListData()
    {
        $actionTree = [];
        //$actions = self::getTreeListRecursive(null, $actionTree);
        $actionsGroup = self::select('id', 'description')
            ->where('parent_id', null)
            ->orderBy('sort_order')
            ->get();
        if (! count($actionsGroup)) {
            return;
        }
        foreach ($actionsGroup as $actionGroup) {
            $actionTree[$actionGroup->id] = [
                'description' => $actionGroup->description,
            ];
            $actionsGroupChild = self::select('id', 'description')
                ->where('parent_id', $actionGroup->id)
                ->orderBy('sort_order')
                ->get();
            if (! count($actionsGroupChild)) {
                continue;
            }
            foreach ($actionsGroupChild as $actionGroupChild) {
                $actionTree[$actionGroup->id]['child'][$actionGroupChild->id] = [
                    'description' => $actionGroupChild->description,
                ];
            }
        }
        return $actionTree;
    }
    
    /**
     * get route to action ids
     * 
     * @param array $actionIds
     * @return array
     */
    public static function getRouteChildren($actionIds)
    {
        $result = [];
        if (! is_array($actionIds)) {
            $actionIds = array ($actionIds);
        }
        $routes = self::select('route', 'id', 'parent_id')
            ->orWhereIn('id', $actionIds)
            ->orWhereIn('parent_id', $actionIds)
            ->where('route' , '<>', null)
            ->where('route' , '<>', '')
            ->get();
        if (! count($routes)) {
            return $result;
        }
        foreach ($routes as $route) {
            if (! $route->route) {
                continue;
            }
            if (isset($result[$route->route]) && $result[$route->route]) {
                continue;
            }
            $result[$route->route] = [
                'id' => $route->id,
                'parent_id' => $route->parent_id,
            ];
        }
        return $result;
    }
    
    /**
     * get collection to show grid data
     * 
     * @return collection model
     */
    public static function getGridData()
    {
        $actionTable = self::getTableName();
        $pager = Config::getPagerData();
        $collection = self::select(
                "{$actionTable}.id as id",
                "{$actionTable}.route as route", 
                "{$actionTable}.name as name", 
                "{$actionTable}.description as description", 
                "{$actionTable}.sort_order",
                'action_parent.name as name_parent'
            )->leftJoin("{$actionTable} as action_parent", 'action_parent.id', '=', "{$actionTable}.parent_id")   
            ->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /**
     * get collection to option
     * 
     * @param boolean $nullable
     * @return array
     */
    public static function toOption($nullable = true, $translate = true)
    {
        $options = [];
        if ($nullable) {
            $options[] = [
                'value' => '',
                'label' => '&nbsp;'
            ];
        }
        self::toOptionRecursive($options, null, 0, $translate);
        return $options;
    }
    
    /**
     * get action collection to option recursive
     * 
     * @param artay $options
     * @param int|null $parentId
     * @param int $level
     */
    public static function toOptionRecursive(&$options, $parentId = null, $level = 0, $translate = true)
    {
        //only get action level < 2
        if ($level >= 2 ) {
            return ;
        }
        $actions = self::select('id', 'description')
            ->where('description', '<>', null)
            ->where('description', '<>', '')
            ->where('parent_id', $parentId)
            ->get();
        if (! count($actions)) {
            return ;
        }
        $prefixLabel = '';
        for ($i = 0 ; $i < $level ; $i++) {
            $prefixLabel .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        foreach ($actions as $action) {
            if ($translate && Lang::has('acl.' . $action->description) && Lang::get('acl.' . $action->description)) {
                $options[] = [
                    'value' => $action->id,
                    'label' => $prefixLabel . Lang::get('acl.' . $action->description)
                ];
            } else {
                $options[] = [
                    'value' => $action->id,
                    'label' => $prefixLabel . $action->description
                ];
            }
            self::toOptionRecursive($options, $action->id, $level+1);
        }
    }
    
    /**
     * rewrite save
     * 
     * @param array $options
     */
    public function save(array $options = array()) 
    {
        $actionNameSame = self::select('id')->where('name', $this->name)
            ->where('id', '<>', $this->id)->first();
        if ($actionNameSame) {
            throw new Exception(Lang::get('team::messages.Code data exists'), self::ERROR_CODE_EXCEPTION);
        }
        if (self::isUseSoftDelete()) {
            $actionNameSame = self::withTrashed()->select('id')
                ->where('name', $this->name)->where('id', '<>', $this->id)->first();
        }
        if ($actionNameSame) {
            $this->name = $this->name . '_' . time();
        }
        try {
            CacheHelper::forget(
                Employee::KEY_CACHE_PERMISSION_TEAM_ACTION
            );
            CacheHelper::forget(
                Employee::KEY_CACHE_PERMISSION_TEAM_ROUTE
            );
            CacheHelper::forget(
                Employee::KEY_CACHE_PERMISSION_ROLE_ACTION
            );
            CacheHelper::forget(
                Employee::KEY_CACHE_PERMISSION_ROLE_ROUTE
            );
            //Translate::writeWord($this->description, Input::get('trans.description'), 'acl');
            return parent::save($options);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * rewrite delete model
     */
    public function delete() {
        try {
            //update menu item
            MenuItem::where('action_id', $this->id)
                ->update([
                    'action_id' => null,
                ]);
            //update permissions
            $permissions = Permission::where('action_id', $this->id)->get();
            if (count($permissions)) {
                foreach ($permissions as $permission) {
                    $permission->delete();
                }
            }
            //delete child acl
            $actionChildren = self::where('parent_id', $this->id)->get();
            if (count($actionChildren)) {
                foreach ($actionChildren as $action) {
                    $action->delete();
                }
            }
            parent::delete();
            CacheHelper::forget(
                Employee::KEY_CACHE_PERMISSION_TEAM_ACTION
            );
            CacheHelper::forget(
                Employee::KEY_CACHE_PERMISSION_TEAM_ROUTE
            );
            CacheHelper::forget(
                Employee::KEY_CACHE_PERMISSION_ROLE_ACTION
            );
            CacheHelper::forget(
                Employee::KEY_CACHE_PERMISSION_ROLE_ROUTE
            );
            CacheHelper::forget(
                MenuItem::KEY_CACHE
            );
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
