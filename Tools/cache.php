<?php
require_once 'abstract.php';

/**
 * Magento Cache Shell Script
 */
class Mage_Shell_Cache extends Mage_Shell_Abstract
{
    /**
     * Run script
     */
    public function run() {
        if ($this->getArg('clean')) {
            $types = Mage::app()->getCacheInstance()->getTypes();

            try {
                echo "Cleaning data cache...\n";
                flush();
                foreach ($types as $type => $data) {
                    echo ">> Removing $type ... ";
                    echo Mage::app()->getCacheInstance()->clean($data["tags"]) ? "[OK]" : "[ERROR]";
                    echo "\n";
                    flush();
                }
            } catch (exception $e) {
                die("[ERROR:" . $e->getMessage() . "]");
            }

            echo "\n";

            try {
                echo "Cleaning stored cache...";
                flush();
                echo Mage::app()->getCacheInstance()->clean() ? "[OK]" : "[ERROR]";
                echo "\n";
            } catch (exception $e) {
                die("[ERROR:" . $e->getMessage() . "]");
            }

            echo "\n";

            try {
                echo "Cleaning merged JS/CSS...";
                flush();
                Mage::getModel('core/design_package')->cleanMergedJsCss();
                Mage::dispatchEvent('clean_media_cache_after');
                echo "[OK]\n";
            } catch (Exception $e) {
                die("[ERROR:" . $e->getMessage() . "]");
            }

            echo "\n";

            try {
                echo "Cleaning image cache... ";
                flush();
                echo Mage::getModel('catalog/product_image')->clearCache();
                echo "[OK]\n";
            } catch (exception $e) {
                die("[ERROR:" . $e->getMessage() . "]");
            }

            echo "Done.\n";
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f cache.php -- [options]
        php -f cache.php -- clean

  clean             Clean Cache
  status            Display statistics

USAGE;
    }
}

$shell = new Mage_Shell_Cache();
$shell->run();