<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use DB;
use Exception;
use Lang;
use Rikkei\Core\View\CacheHelper;

class LibsFolk extends CoreModel
{
    protected $table = 'libs_folk';
    const KEY_CACHE = 'libs_folk';
    
    /**
     * get Gridview Data LibFolk
     * @return Collection Description
     */
    public static function gridViewData()
    {
        $thisTbl = self::getTableName();
        $collection = self::select('id', 'name');
        $pager = Config::getPagerData(null, ['order' => "{$thisTbl}.updated_at", 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir'])->orderBy("{$thisTbl}.updated_at", 'ASC');
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        
        return $collection;
    }
    
    /**
     * get item folk by Id
     * @param type $id
     * @return Model Description
     */
    public static function getItemById($id)
    {
        return self::where("id", $id)->select('id', 'name')
                ->first();
    }
}
