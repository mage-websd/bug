<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Shell
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'abstract.php';

/**
 * Magento Sales Shell Script
 *
 * @category    Mage
 * @package     Mage_Shell
 * @author      Magento Core Team <core@magentocommerce.com>
 * Update increment id for site
 */
class Mage_Shell_Sales extends Mage_Shell_Abstract
{
    protected $_entityStore;
    protected $_entityType;

    protected $_resource;
    protected $_read;
    protected $_write;

    /**
     * Run script
     *
     */
    public function run()
    {
        $this->_getTable();
        $query = "SELECT * FROM {$this->_entityStore}";
        $entityStoreRecords = $this->_read->fetchAll($query); //get record need update
        if(count($entityStoreRecords) > 0) {
            foreach ($entityStoreRecords as $entityStoreRecord) {
                $query = "SELECT entity_table FROM {$this->_entityType} " .
                    "JOIN {$this->_entityStore} ON {$this->_entityStore}.entity_type_id = {$this->_entityType}.entity_type_id " .
                    "WHERE {$this->_entityStore}.entity_type_id = '{$entityStoreRecord['entity_type_id']}'";
                $resultEntityTable = $this->_read->fetchOne($query); //get Table entity have record need update
                if($resultEntityTable) {
                    $entityTable = $this->_resource->getTableName($resultEntityTable);
                    $query = "SELECT max(increment_id) FROM {$entityTable} WHERE store_id='{$entityStoreRecord['store_id']}'";
                    $resultLastId = $this->_read->fetchOne($query);
                    if(is_numeric($resultLastId)) {
                        $query = "UPDATE {$this->_entityStore} SET increment_last_id = '{$resultLastId}' WHERE " .
                            "entity_store_id = '{$entityStoreRecord['entity_store_id']}'";
                        $this->_write->query($query);
                        echo "Success - ".$entityTable.PHP_EOL;
                    }
                }
            }
        }
        echo PHP_EOL;
    }

    protected function _getTable()
    {
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_read = $this->_resource->getConnection('core_read');
        $this->_write = $this->_resource->getConnection('core_write');
        $this->_entityStore = $this->_resource->getTableName('eav/entity_store');
        $this->_entityType = $this->_resource->getTableName('eav/entity_type');
    }
}

$shell = new Mage_Shell_Sales();
$shell->run();
