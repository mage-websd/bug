<?php
namespace Rikkei\Core\Seeds;

use Rikkei\Core\Model\CoreConfigData;

class AnnualHolidaysSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return true;
        }
        $value = '' .
            '01-01' . PHP_EOL .
            '04-30' . PHP_EOL .
            '05-01' . PHP_EOL .
            '09-02' . PHP_EOL;
        $key = 'project.annual_holidays';
        $item = CoreConfigData::getItem($key);
        if (!$item) {
            $item = new CoreConfigData();
            $item->key = $key;
        }
        $item->value = $value;
        $item->save();
        $this->insertSeedMigrate();
    }
}
