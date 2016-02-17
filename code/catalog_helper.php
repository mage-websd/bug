<?php
class Emosys_Custom_Helper_Data extends Mage_Core_Helper_Abstract {
	protected function _getStoreId() {
        $storeId = Mage::app()->getStore()->getId();
        return $storeId;
    }
    protected function _getCustomerGroupId() {
        $custGroupID = null;
        if ($custGroupID == null) {
            $custGroupID = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }
        return $custGroupID;
    }

    public function getBestSell($limit=20) {
        $storeId = $this->_getStoreId();
        $products = Mage::getResourceModel('reports/product_collection')
        	->addOrderedQty()
        	->addAttributeToSelect(array('small_image'))
        	->setPageSize($limit)
        	->setStoreId($storeId)
        	->addStoreFilter($storeId)->setOrder('ordered_qty', 'desc');

        $this->prepareProductCollection($products);
        return $products;
    }

    public function getSale($limit = 20,$productIdFilter = null) {
        $storeId = $this->_getStoreId();
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        $custGroup = $this->_getCustomerGroupId();
        $product = Mage::getModel('catalog/product');
        $todayDate = $product->getResource()->formatDate(time(), false);
        $rulePriceWhere = "({{table}}.rule_date is null) or " . "({{table}}.rule_date='$todayDate' and " . "{{table}}.website_id='$websiteId' and " . "{{table}}.customer_group_id='$custGroup')";
        $specials = $product->setStoreId($storeId)->getResourceCollection()
        	->addAttributeToFilter('special_price', array('gt' => 0), 'left')
        	->addAttributeToFilter('special_from_date', array('date' => true, 'to' => $todayDate), 'left')
        	->addAttributeToFilter(array(array('attribute' => 'special_to_date', 'date' => true, 'from' => $todayDate), array('attribute' => 'special_to_date', 'is' => new Zend_Db_Expr('null'))), '', 'left')
        	->addAttributeToSort('special_from_date', 'desc')
        	->setPageSize($limit)
        	->joinTable('catalogrule/rule_product_price', 'product_id=entity_id', array('rule_price' => 'rule_price', 'rule_start_date' => 'latest_start_date', 'rule_date' => 'rule_date', 'rule_end_date' => 'earliest_end_date'), $rulePriceWhere, 'left');
        if ($productIdFilter) {
            $specials = $specials->addAttributeToFilter('entity_id', $productIdFilter);
        }
        $rulePriceCollection = Mage::getResourceModel('catalogrule/rule_product_price_collection')->addFieldToFilter('website_id', $websiteId)->addFieldToFilter('customer_group_id', $custGroup)->addFieldToFilter('rule_date', $todayDate);
        $productIds = $rulePriceCollection->getProductIds();
        if (!empty($productIds)) {
            $specials->getSelect()->orWhere('e.entity_id in (' . implode(',', $productIds) . ')');
        }
        $this->prepareProductCollection($specials);
        if ($productIdFilter) {
          foreach ($specials as $special) {
            if($special->getData('entity_id') == $productIdFilter) {
              return $special;
            }
          }
          return null;
        }
        return $specials;
    }

    public function getNew($limit = 20)
    {
    	$todayStartOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('00:00:00')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $todayEndOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('23:59:59')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
        $this->prepareProductCollection($collection);

        $collection
            ->addStoreFilter()
            ->addAttributeToFilter('news_from_date', array('or'=> array(
                0 => array('date' => true, 'to' => $todayEndOfDayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left')
            ->addAttributeToFilter('news_to_date', array('or'=> array(
                0 => array('date' => true, 'from' => $todayStartOfDayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left')
            ->addAttributeToFilter(
                array(
                    array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
                    array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
                    )
              )
            ->addAttributeToSort('news_from_date', 'desc')
            ->setPageSize($limit)
            ->setCurPage(1)
        ;

        return $collection;
    }

    public function getProductCategory($category, $limit=20)
    {
    	if(!$category) {
    		return null;
    	}
        if(!is_object($category)) {
            $category = Mage::getModel('catalog/category')->load($category);
        }
        if(!$category->getId()) {
            return null;
        }
    	$store = $this->_getStoreId();
        $productCollection = Mage::getResourceModel('catalog/product_collection')
            ->setStoreId($store)
            ->addCategoryFilter($category)
            ->addAttributeToSelect('small_name')
            ->addUrlRewrite($category->getId())
            ->setPageSize($limit)
        ;
        $this->prepareProductCollection($productCollection);
        return $productCollection;
    }

    public function getMostViewed($limit=20)
    {
        $collection = Mage::getResourceModel('reports/product_collection')
            ->addOrderedQty()
            ->setStoreId($storeId)
            ->addStoreFilter($this->_getStoreId())
            ->addViewsCount()
            ->setPageSize($limit)->setCurPage(1);
        $this->prepareProductCollection($collection,false);
        return $collection;
    }

    public function prepareProductCollection($collection,$price = true) {
        $collection->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes());
        if($price) {
            $collection->addMinimalPrice()->addFinalPrice()->addTaxPercents();
        }
        //->addUrlRewrite($this->getCurrentCategory()->getId());
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        if($visiblility) {
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        }
        return $this;
    }

    public function getPercentSale($product)
    {
        $price = $product->getPrice();
        $finalPrice = $product->getFinalPrice();
        if(!$finalPrice || ($price == $finalPrice)) {
            return null;
        }
        return (int)(100 * ($price-$finalPrice) / $price);
    }
}