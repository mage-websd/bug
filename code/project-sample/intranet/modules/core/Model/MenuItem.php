<?php

namespace Rikkei\Core\Model;

use DB;
use Rikkei\Team\View\Config;
use Lang;
use Exception;
use Rikkei\Core\View\CacheHelper;

class MenuItem extends CoreModel
{
    /*
     * flag status menu item
     */
    const STATE_ENABLE  = 1;
    const STATE_DISABLE = 0;
    
    public $timestamps = false;
    
    const KEY_CACHE = 'menu_items';
    const KEY_CACHE_HAS_CHILD = 'item_has_child';
    const HAS_CHILD = 1;
    const HAS_NOT_CHILD = 2;


    /**
     * count child of menu item
     * 
     * @return count
     */
    public function hasChild()
    {
        if ($hasChild = CacheHelper::get(self::KEY_CACHE_HAS_CHILD, $this->id)) {
            return $hasChild == self::HAS_CHILD;
        }
        $child = self::select(DB::raw('count(*) as count'))
            ->where('parent_id', $this->id)
            ->first();
        $hasChild = $child->count == 0 ? self::HAS_NOT_CHILD : self::HAS_CHILD;
        CacheHelper::put(self::KEY_CACHE_HAS_CHILD, $hasChild, $this->id);
        return $hasChild == self::HAS_CHILD;
    }
    
    /**
     * get collection to show grid data
     * 
     * @return collection model
     */
    public static function getGridData()
    {
        $menuItemsTable = self::getTableName();
        $menuGroupTable = Menu::getTableName();
        $pager = Config::getPagerData();
        $collection = self::select("{$menuItemsTable}.id as id","{$menuItemsTable}.name as name", 
            'menu_item_parent.name as name_parent ', "{$menuItemsTable}.url", "{$menuGroupTable}.name as nane_group")
            ->join($menuGroupTable, "{$menuGroupTable}.id", '=', 'menu_id')
            ->leftJoin("$menuItemsTable as menu_item_parent", 'menu_item_parent.id', '=', "{$menuItemsTable}.parent_id")
            ->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /**
     * get option state
     * 
     * @return array
     */
    public static function toOptionState()
    {
        return [
            [
                'value' => self::STATE_DISABLE,
                'label' => Lang::get('core::view.Disable')
            ],
            [
                'value' => self::STATE_ENABLE,
                'label' => Lang::get('core::view.Active')
            ]
        ];
    }
    
    
    /**
     * get collection to option
     * 
     * @param null|int $skipId
     * @param boolean $nullable
     * @return array
     */
    public static function toOption($skipId = null, $nullable = true)
    {
        $options = [];
        if ($nullable) {
            $options[] = [
                'value' => '',
                'label' => '&nbsp;'
            ];
        }
        self::toOptionRecursive($options, null, $skipId, 0);
        return $options;
    }
    
    /**
     * collection to option recursive
     * 
     * @param artay $options
     * @param int|null $parentId
     * @param null|int $skipId
     * @param int $level
     */
    public static function toOptionRecursive(&$options, $parentId = null, $skipId = null, $level = 0)
    {
        $menuItem = self::select('id', 'name')
            ->where('parent_id', $parentId)
            ->where('id', '<>', $skipId)
            ->get();
        if (! count($menuItem)) {
            return ;
        }
        $prefixLabel = '';
        for ($i = 0 ; $i < $level ; $i++) {
            $prefixLabel .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        foreach ($menuItem as $item) {
            $options[] = [
                'value' => $item->id,
                'label' => $prefixLabel . $item->name
            ];
            self::toOptionRecursive($options, $item->id, $skipId, $level+1);
        }
    }
    
    /**
     * rewrite save
     * 
     * @param array $options
     */
    public function save(array $options = array()) {
        if ($this->parent_id) {
            $parentMenu = MenuItem::find($this->parent_id);
            $this->menu_id  = $parentMenu->menu_id;
        } else {
            $this->parent_id = null;
        }
        if (! $this->action_id) {
            $this->action_id = null;
        }
        try {
            $result = parent::save($options);
            CacheHelper::forget(self::KEY_CACHE);
            CacheHelper::forget(self::KEY_CACHE_HAS_CHILD, $this->parent_id);
            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * rewrite delete model
     */
    public function delete() {
        try {
            $menuItemChildren = MenuItem::where('parent_id', $this->id)
                ->get();
            if (count($menuItemChildren)) {
                foreach ($menuItemChildren as $item) {
                    $item->delete();
                }
            }
            parent::delete();
            CacheHelper::forget(self::KEY_CACHE);
            CacheHelper::forget(self::KEY_CACHE_HAS_CHILD, $this->parent_id);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * get child of menu item
     * 
     * @param int $parentId
     * @param int $menuGroupId
     */
    public static function getChildMenuItems($parentId, $menuGroupId)
    {
        if ($menuItemsCache = CacheHelper::get(self::KEY_CACHE)) {
            return $menuItemsCache;
        }
        $menuItems = MenuItem::where('menu_id', $menuGroupId)
            ->where('parent_id', $parentId)
            ->orderBy('sort_order')
            ->get();
        CacheHelper::put(self::KEY_CACHE, $menuItems);
        return $menuItems;
    }
    
    /**
     * find menu id level 0 follow name or path url
     * 
     * @param string $name
     * @param $url $menuGroupId
     */
    public static function getIdMenuevel0($name = null, $url = null)
    {
        if ($menuItemsCache = CacheHelper::get(self::KEY_CACHE)) {
            return $menuItemsCache;
        }
        $menuItem = null;
        if ($name) {
            $menuItem = MenuItem::select('id')
                ->where('parent_id', null)
                ->where('name', $name)
                ->first();
            if ($menuItem) {
                $menuItem = $menuItem->id;
                CacheHelper::put(self::KEY_CACHE, $menuItem);
                return $menuItem;
            }
        }
        
        if ($url) {
            $menuItem = MenuItem::select('id')
                ->where('parent_id', null)
                ->where('url', $url)
                ->first();
            if ($menuItem) {
                $menuItem = $menuItem->id;
                CacheHelper::put(self::KEY_CACHE, $menuItem);
                return $menuItem;
            }
        }
        return null;
    }
}
