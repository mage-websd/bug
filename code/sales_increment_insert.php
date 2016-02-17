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
    protected $_stores;
    protected $_typeSales;

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
        if(count($this->_stores) > 0) {
            foreach($this->_stores as $store) { //each store insert
                $lastId = $store * pow(10,9) + 1;
                if (count($this->_typeSales) > 0) {
                    foreach ($this->_typeSales as $typeSale) {
                        $query = "SELECT entity_type_id FROM {$this->_entityType} " .
                            "WHERE entity_type_code = '{$typeSale}'";
                        $resultEntityType = $this->_read->fetchOne($query);
                        $query = "SELECT entity_store_id FROM {$this->_entityStore} " .
                            "WHERE entity_type_id='{$resultEntityType}' AND " .
                            "store_id='{$store}'";
                        $resultEntityStore = $this->_read->fetchOne($query);
                        if(!$resultEntityStore) { //not exists increment if for store, then insert
                            $query = "INSERT INTO {$this->_entityStore} ".
                                "(entity_store_id,entity_type_id,store_id,increment_prefix,increment_last_id) ".
                                "VALUES (null, '{$resultEntityType}', '{$store}', '{$store}', {$lastId})";
                            $this->_write->query($query);
                            echo "Success - insert {$typeSale} of store {$store}".PHP_EOL;
                        }
                    }
                }
            }
        }
        echo PHP_EOL;
    }

    protected function _getTable()
    {
        $this->_stores = array(2); //store need insert increment id
        $this->_typeSales=array('order','invoice','creditmemo','shipment'); //type need insert

        $this->_resource = Mage::getSingleton('core/resource');
        $this->_read = $this->_resource->getConnection('core_read');
        $this->_write = $this->_resource->getConnection('core_write');
        $this->_entityStore = $this->_resource->getTableName('eav/entity_store');
        $this->_entityType = $this->_resource->getTableName('eav/entity_type');
    }
}

$shell = new Mage_Shell_Sales();
$shell->run();
