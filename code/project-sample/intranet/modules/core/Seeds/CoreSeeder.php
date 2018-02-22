<?php
namespace Rikkei\Core\Seeds;

use Illuminate\Database\Seeder;
use DB;
use Exception;

abstract class CoreSeeder extends Seeder
{
    
    protected $tableMigrate = 'migrations';
    protected $key = null;
    /**
     * check seed exists in table migrate
     * 
     * @param string|null $key
     * @return boolean
     */
    protected function checkExistsSeed($key = null)
    {
        // set key to insert to migrate table
        if (! $key) {
            $key = get_called_class();
            $key = explode('\\', $key);
            if (count($key)) {
                $key = (string) $key[count($key) - 1];
            }
        }
        $key = 'seed-' . $key;
        $this->key = $key;
        if (count(
                DB::table($this->tableMigrate)
                    ->where('migration', $key)->get()
        )) {
            return true;
        }
        return false;
    }
    
    /**
     * insert data seed check into migrate table
     * 
     * @return null
     * @throws Exception
     */
    protected function insertSeedMigrate()
    {
        if (! $this->key) {
            return;
        }
        try {
            DB::table($this->tableMigrate)->insert([
                'migration' => $this->key,
                'batch' => 1
            ]);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}

