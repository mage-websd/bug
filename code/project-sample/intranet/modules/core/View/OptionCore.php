<?php

namespace Rikkei\Core\View;

class OptionCore
{
    /**
     * option yes no 
     * 
     * @param type $null
     */
    public static function yesNo($null = true, $shortLabel = false)
    {
        $option = [];
        if ($null) {
            $option[''] = '&nbsp';
        }
        if ($shortLabel) {
            $option[1] = 'Y';
            $option[2] = 'N';
        } else {
            $option[1] = 'Yes';
            $option[2] = 'No';
        }
        return $option;
    }
}
