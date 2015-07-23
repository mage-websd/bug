<?php
    public function getAttributeSetIdFromName()
    {
        $attributeSetName = 'Default';
        if($this->isRentProduct()) {
            $attributeSetName = $this->helper('emosys_custom')->getRentName(); 
        }
        $attributeSet = Mage::getModel("eav/entity_attribute_set")
            ->getCollection()
            ->addFieldToFilter("attribute_set_name", $attributeSetName)
            ->addFieldToFilter('entity_type_id',4)
            ->getFirstItem();
        return $attributeSet->getAttributeSetId();
    }

    public function getAttributeOptions($code)//get all option of configuration attribute: color, size....
    {
        $attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter($code)->getFirstItem();
        $attributeId = $attributeInfo->getAttributeId();
        $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
        $attributeOptions = $attribute ->getSource()->getAllOptions(false);
        return $attributeOptions;
    }

    public function getCountryOptions()
    {
        return Mage::getResourceModel('directory/country_collection')
            ->loadData()
            ->toOptionArray(false);
    }

    public function getClassTax()
    {
        return Mage::getModel('tax/class')
            ->getCollection()
            ->addFieldToFilter('class_type','PRODUCT');
    }

