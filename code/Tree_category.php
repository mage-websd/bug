<?php 
	$this->_categoiesProduct = array()
    /**
     * return string menu category format html
     *
     * @return string
     */
    public function getCategoryTree()
    {
        if($this->getProduct()) {
 = $this->getProduct()->getCategoryIds();
        }
        $rootcatID = Mage::app()->getStore()->getRootCategoryId();
        $html ='';
        $html .= '<ul class="categoy-tree">';
        $html .= $this->_getSubCategory($rootcatID,0);
        $html .= '</ul>';
        return $html;
    }

    /**
     * get category and all sub category
     *   format html
     * 
     * @param $id_category: id category
     * @param $no: #no category same level
     * @return string
     */
    protected function _getSubCategory($idCategory, $no)
    {
        $html = '';
        $category = $this->_getModelCategory()->load($idCategory);
        /*if(!$category->getData('include_in_menu') || !$category->getData('is_active'))
            return '';*/
        $level = $category->getData('level');

        if ($level > 1) {
            $checked = '';
            if(in_array($idCategory, $this->_categoiesProduct)) {
                $checked = ' checked';
            }
            $input = '<input type="checkbox" name="category_ids[] class="input-checkbox" value="'.$idCategory.'"'.$checked.' />';
            $optionTagli = ' class="level'.($level-2).' no-'.$no.'"'; // add class for li
            $html .= '<li' . $optionTagli . '>'.
                '<label>'.$input.'<span data-href="' . $category->getUrl() . '">'
                . $category->getData('name')
                . '</span></label>';
        }
        $categoriesSub = $category->getChildren();
        if ($categoriesSub) {
            if($level > 1) {
                $optionTagul = ' class="level'.($level-2).' no-'.$no.'"'; //add class for ul
                $html .= '<ul'.$optionTagul.' style="margin-left: '.(($level-1)*50).'px;">';
            }
            $noSub = 0;
            foreach (explode(',', $categoriesSub) as $chidId) {
                $html .= $this->_getSubCategory($chidId,$noSub); // call recursive with sub category
                $noSub++;
            }
            $html .= '</ul>';
        }
        if ($level > 1) {
            $html .= '</li>';
        }
        return $html;
    }
    
    /**
     * get model catalog/category
     *
     * @return false|Mage_Core_Model_Abstract
     */
    private function _getModelCategory()
    {
        return Mage::getModel('catalog/category');
    }
