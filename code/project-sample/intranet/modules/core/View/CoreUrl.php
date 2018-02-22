<?php

namespace Rikkei\Core\View;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

class CoreUrl
{
    /**
     * get url asset file with version file
     * 
     * @param string $pathFile
     * @return string
     */
    public static function asset($pathFile)
    {
        return URL::asset($pathFile . '?v=' . Config::get('view.assets_verson'));
    }
}
