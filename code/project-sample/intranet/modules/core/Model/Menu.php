<?php

namespace Rikkei\Core\Model;

use Rikkei\Team\View\Config;
use Lang;
use Exception;
use DB;
use Rikkei\Core\Model\MenuItem;
use Rikkei\Core\View\CacheHelper;

/**
 * Menu object
 */
class Menu extends CoreModel
{
    /*
     * flag status of menu
     */
    const FLAG_DISABLE = 0;
    const FLAG_ACTIVE = 1;
    const FLAG_MAIN = 2;
    const FLAG_SETTING = 3;
    
    const KEY_CACHE = 'menu_groups';
    
    protected $table = 'menus';
    
    public $timestamps = false;
    
    /**
     * get menu default
     * 
     * @return model
     */
    public static function getMenuDefault()
    {
        if ($menu = CacheHelper::get(self::KEY_CACHE)) {
            return $menu;
        }
        
        $menu = self::where('state', self::FLAG_MAIN)
            ->first();
        CacheHelper::put(self::KEY_CACHE, $menu);
        return $menu;
    }
    
    /**
     * get menu default
     * 
     * @return model
     */
    public static function getMenuSetting()
    {
        if ($menu = CacheHelper::get(self::KEY_CACHE)) {
            return $menu;
        }
        
        $menu = self::where('state', self::FLAG_SETTING)
            ->first();
        CacheHelper::put(self::KEY_CACHE, $menu);
        return $menu;
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
                'value' => self::FLAG_DISABLE,
                'label' => Lang::get('core::view.Disable')
            ],
            [
                'value' => self::FLAG_ACTIVE,
                'label' => Lang::get('core::view.Active')
            ],
            [
                'value' => self::FLAG_MAIN,
                'label' => Lang::get('core::view.Main menu')
            ],
            [
                'value' => self::FLAG_SETTING,
                'label' => 'Setting menu'
            ],
        ];
    }
    
    /**
     * get collection to show grid data
     * 
     * @return collection model
     */
    public static function getGridData()
    {
        $pager = Config::getPagerData();
        $collection = self::select('id','name')
            ->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /**
     * rewrite save
     * 
     * @param array $options
     */
    public function save(array $options = [])
    {
        DB::beginTransaction();
        try {
            // set state model to flag active
            if ($this->state == self::FLAG_MAIN) {
                self::where('state', self::FLAG_MAIN)
                    ->where('id', '<>', $this->id)
                    ->update([
                        'state' => self::FLAG_ACTIVE
                    ]);
            } elseif ($this->state == self::FLAG_SETTING) {
                self::where('state', self::FLAG_SETTING)
                    ->where('id', '<>', $this->id)
                    ->update([
                        'state' => self::FLAG_ACTIVE
                    ]);
            }
            CacheHelper::forget(self::KEY_CACHE);
            parent::save($options);
            Db::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * count menu item
     */
    public function countMenuItem()
    {
        $items = MenuItem::select(DB::raw('COUNT(*) AS count'))
            ->where('menu_id', $this->id)
            ->first();
        return $items->count;
    }
    
    /**
     * rewrite delete
     */
    public function delete() {
        $count = $this->countMenuItem();
        if ($count) {
            $message = Lang::get("core::view.This menu group has :number items, can't delete", ['number' => $count]);
            throw new Exception($message, self::ERROR_CODE_EXCEPTION);
        }
        try {
            CacheHelper::forget(self::KEY_CACHE);
            return parent::delete();
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * to option array
     * 
     * @return array
     */
    public static function toOption($nullable = false)
    {
        $menus = self::select('id', 'name')
            ->where('state', '<>', self::FLAG_DISABLE)
            ->orderBy('name')
            ->get();
        $options = [];
        if ($nullable) {
            $options[] = [
                'value' => '',
                'label' => '',
            ];
        }
        if (count($menus)) {
            foreach ($menus as $menu) {
                $options[] = [
                    'value' => $menu->id,
                    'label' => $menu->name,
                ];
            }
        }
        return $options;
    }
}
