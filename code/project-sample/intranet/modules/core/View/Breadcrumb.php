<?php

namespace Rikkei\Core\View;

class Breadcrumb
{
    /**
     * breacdcrumb
     * @var array
     */
    protected static $path = array();
    
    /**
     * Set root node
     */
    public static function adds($paths)
    {
        foreach ($paths as list($url, $text, $preText)) {
            self::add($text, $url, $preText);
        }
    }

    /**
     * Add a node
     */
    public static function add($text, $url = null, $pre_text = null)
    {
        self::$path[] = [
            'url' => $url,
            'text' => $text,
            'pre_text' => $pre_text,
        ];
    }

    /**
     * Get list nodes to render
     */
    public static function get() 
    {
        return self::$path;
    }
}
