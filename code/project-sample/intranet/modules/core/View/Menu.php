<?php
/** 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Rikkei\Core\View;

use URL;
use Rikkei\Team\View\Permission;
use Rikkei\Core\Model\Menu as MenuModel;
use Rikkei\Core\Model\MenuItem;

class Menu
{
    /*
     * active menu flag
     */
    protected static $active;
    
    protected static $menuHtml;


    /**
     * set active menu
     * 
     * @param string $name
     * @param string $path
     * @param string $flag
     */
    public static function setActive($name = null, $path = null, $flag = null)
    {
        if ($flag) {
            self::$active = $flag;
            return;
        }
        self::$active = MenuItem::getIdMenuevel0($name, $path);
    }
    
    /**
     * remove active menu
     */
    public static function removeActive()
    {
        self::$active = null;
    }


    /**
     * get active menu
     * 
     * @return string
     */
    public static function getActive()
    {
        return self::$active;
    }
    
    /**
     * check menu is active
     * 
     * @param string $id
     * @return boolean
     */
    public static function isActive($id)
    {
        if($id == self::$active) {
            return true;
        }
        return false;
    }
    
    /**
     * get menu html
     * 
     * @param int $menuId id of menus
     * @return string
     */
    public static function get($menuId = null, $level = 0)
    {
        if (! $menuId) {
            $menuId = MenuModel::getMenuDefault();
            if(! $menuId) {
                return;
            }
            $menuId = $menuId->id;
        }
        return self::getChildMenu($menuId, null, $level);
    }
    
    /**
     * get html menu tree
     *  call recursive
     * 
     * @param array $menu
     * @return string
     */
    protected static function getChildMenu($menuId, $parentId = null, $level = 0)
    {
        $html = '';
        $menuItems = MenuItem::getChildMenuItems($parentId, $menuId);
        if (! count($menuItems)) {
            return;
        }
        foreach ($menuItems as $item) {
            if ($item->state != MenuItem::STATE_ENABLE) {
                continue;
            }
            //check permission menu of current user logged
            if ($item->action_id) {
                if (! Permission::getInstance()->isAllow($item->action_id)  ) {
                    continue;
                }
            }
            $hasChild = $item->hasChild();
            $classLi = self::isActive($item->id) ? ' active' : '';
            $classA = '';
            $optionA = '';
            if ($hasChild) {
                $htmlMenuChild = self::getChildMenu($menuId, $item->id, $level+1);
                 if (! e($htmlMenuChild)) {
                     continue;
                 }
                $classLi .= ' dropdown';
                $classA .= 'dropdown-toggle';
                $optionA .= ' data-toggle="dropdown"';
                if ($level > 0) {
                    $classLi .= ' dropdown-submenu';
                }
            }
            $classLi = $classLi ? " class=\"{$classLi}\"" : '';
            $classA = $classA ? " class=\"{$classA}\"" : '';
            $urlMenu = '#';
            if($item->url && $item->url != '#') {
                if (preg_match('/^http(s)?:\/\//', $item->url)) {
                    $urlMenu = $item->url;
                } else {
                    $urlMenu = URL::to($item->url);
                }
            }
            
            $html .= "<li{$classLi}>";
            $html .= "<a href=\"{$urlMenu}\"{$classA}{$optionA}>";
            $html .= $item->name;
            $html .= '</a>';
            if ($hasChild && e($htmlMenuChild)) {
                $html .= '<ul class="dropdown-menu" role="menu">';
                $html .= $htmlMenuChild;
                $html .= '</ul>';
            }
            $html .= '</li>';
        }
        return $html;
    }
}
