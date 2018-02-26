<?php

namespace App\Models;

use App\Traits\DynamicFinderTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Input;

class BaseModel extends Model
{
    use DynamicFinderTrait;

    /**
     * get table name of model
     *
     * @return object
     */
    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    /**
     * pagination collection
     *
     * @param object $collection
     * @param int $limit
     * @param int $page
     * @return collection
     */
    public static function pager(&$collection, $limit, $page)
    {
        return $collection = $collection->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * filter collection with params from url
     *
     * @param collection $collection
     * @param array $alias alias to convert param to column db, allow filter in
     *      column db
     * @param array $operator = > < >= <= REGEXP
     * @return type
     */
    public static function filter(
        &$collection,
        $alias = [],
        $operator = []
    ) {
        $filters = (array) Input::get();
        if (!$filters || !count($filters)) {
            return $collection;
        }
        foreach ($filters as $key => $value) {
            // filter param start with s-
            if (!str_start($key, 's-')) {
                continue;
            }
            $key = substr($key, 2);
            // convert key param to col db, // filter param ignore
            if (isset($alias[$key])) {
                $key = $alias[$key];
            } else {
                continue;
            }
            if (is_array($value)) {
                $collection = $collection->whereIn($key, $value);
                continue;
            }
            if (isset($operator[$key])) {
                $collection = $collection
                    ->where($key, $operator[$key], addslashes($value));
                continue;
            }
            $collection = $collection
                ->where($key, 'like', '%' . addslashes($value) . '%');
        }
        return $collection;
    }

    /**
     * get array of id
     * @return array
     */
    public static function getArrayId()
    {
        return static::select('id')->get()->pluck('id')->toArray();
    }
}
