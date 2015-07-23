<?php
class My_Icore_Block_Page_Html_Topmenu extends Mage_Page_Block_Html_Topmenu
{
    protected $_columnIndex;
    protected $_columnIdexItems;
    protected $_columnCount;

    public function _construct()
    {
        parent::_construct();
        $this->_columnIndex = 1;
        $this->_columnIdexItems = 1;
        $this->_columnCount = array();
        for($i = 1 ; $i<6 ; $i++) {
            $this->_columnCount[$i] = (int)Mage::getStoreConfig('catalog/navigation/column_'.$i);
        }
    }
    /**
     * Recursively generates top menu html from data that is specified in $menuTree
     *
     * @param Varien_Data_Tree_Node $menuTree
     * @param string $childrenWrapClass
     * @return string
     */
    protected function _getHtml(Varien_Data_Tree_Node $menuTree, $childrenWrapClass)
    {
        $html = '';
        $category = Mage::getSingleton('catalog/category');
        $uModel = Mage::getSingleton('core/url');

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = is_null($parentLevel) ? 0 : $parentLevel + 1;
        if($childLevel==0) {
            $this->resetColumnObject();
        }

        $counter = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        foreach ($children as $child) {

            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);
            $child->setUniqueClass('nav-' . $category->formatUrlKey($child->getName()));

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . ' ' . $category->formatUrlKey($menuTree->getName()) . '" ';
                $child->setClass($outermostClass);
            }

            $urModel = Mage::getModel('core/url_rewrite')->loadByIdPath($child->getIdPath());
            if($childLevel == 1) {
                if(isset($this->_columnCount[$this->_columnIndex]) && $this->_columnCount[$this->_columnIndex]) {
                    if($this->_columnIdexItems == 1) {
                        $html .= '<div class="sub-nav-column column-'.$this->_columnIndex.' count-items-'.$this->_columnCount[$this->_columnIndex].'">';
                    }
                    $this->_columnIdexItems++;
                }
            }

            if ($URL = $urModel->getRequestPathCustom()) {
                if ($URL == '#') {
                    $URL = $uModel->getDirectUrl($URL);
                    $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
                    $html .= '<a href="#" ' . $outermostClassCode . '><span>'
                        . $this->escapeHtml($child->getName()) . '</span></a>';
                } else {
                    $URL = $uModel->getDirectUrl($URL);
                    $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
                    $html .= '<a href="' . $URL . '" ' . $outermostClassCode . '><span>'
                        . $this->escapeHtml($child->getName()) . '</span></a>';
                }
            } else {
                $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
                $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span>'
                    . $this->escapeHtml($child->getName()) . '</span></a>';
            }

            if ($child->hasChildren()) {
                if (!empty($childrenWrapClass)) {
                    $html .= '<div class="' . $childrenWrapClass . '">';
                }
                $html .= '<ul class="level' . $childLevel . '">';
                $html .= $this->_getHtml($child, $childrenWrapClass);
                $html .= '</ul>';

                if (!empty($childrenWrapClass)) {
                    $html .= '</div>';
                }
            }
            $html .= '</li>';

            if($childLevel == 1) {
                if(isset($this->_columnCount[$this->_columnIndex]) && $this->_columnCount[$this->_columnIndex]) {
                    if($this->_columnIdexItems > $this->_columnCount[$this->_columnIndex]) {
                        $html .=  '</div>';
                        $this->_columnIndex++;
                        $this->_columnIdexItems = 1;
                    }
                    else if($child->getIsLast()) {
                        $html .=  '</div>';
                        $this->resetColumnObject();
                    }
                }
            }

            $counter++;
        }

        return $html;
    }

    /**
     * Returns array of menu item's classes
     *
     * @param Varien_Data_Tree_Node $item
     * @return array
     */
    protected function _getMenuItemClasses(Varien_Data_Tree_Node $item)
    {
        $classes = array();

        $classes[] = 'level' . $item->getLevel();
        $classes[] = $item->getPositionClass();
        $classes[] = $item->getUniqueClass();

        if ($item->getIsFirst()) {
            $classes[] = 'first';
        }

        if ($item->getIsActive()) {
            $classes[] = 'active';
        }

        if ($item->getIsLast()) {
            $classes[] = 'last';
        }

        if ($item->getClass()) {
            $classes[] = $item->getClass();
        }

        if ($item->hasChildren()) {
            $classes[] = 'parent';
        }

        return $classes;
    }

    protected function resetColumnObject()
    {
        $this->_columnIndex = 1;
        $this->_columnIdexItems = 1;
    }
}