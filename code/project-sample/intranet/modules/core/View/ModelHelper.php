<?php

namespace Rikkei\Core\View;

use Illuminate\Support\Facades\DB;

class ModelHelper
{
    /**
     * check exists key of table 
     *  primary, foreinkey, ....
     * 
     * @param string $table
     * @param string $key
     * @param string $column
     * @return boolean
     */
    public static function existsKey($table, $key = 'PRIMARY', $column = null)
    {
        $query = "SHOW KEYS FROM {$table} WHERE `Key_name` = '{$key}'";
        if ($column) {
            $query .= " AND `Column_name` = '{$column}'";
        }
        if (DB::select(DB::raw($query))) {
            return true;
        }
        return false;
    }
    
    /**
     * uncheck key for mysql
     */
    public static function uncheckKey()
    {
        DB::statement('SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;');
        DB::statement('SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;');
        DB::statement("SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';");
    }
    
    /**
     * check / reset key for mysql
     */
    public static function checkKey()
    {
        DB::statement('SET SQL_MODE=@OLD_SQL_MODE;');
        DB::statement('SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;');
        DB::statement('SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;');
    }
}
