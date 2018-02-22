<?php

namespace Rikkei\Core\View;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class CoreQB
{
    protected $data;
    protected $lastPage;
    protected $currentPage;
    protected $perPage;
    protected $total;
    protected $query;
    protected $bindings;
    protected $colCount;
    protected $paramPager;
    protected $paramLimit;
    protected $fullResult;

    /**
     * construct
     */
    public function __construct(array $data = []) {
        $arrayDefault = [
            'data' => null,
            'lastPage' => 0,
            'currentPage' => 1,
            'perPage' => 50,
            'total' => 0,
            'query' => [],
            'bindings' => [],
            'colCount' => 'COUNT(*)',
            'paramPager' => 'page',
            'paramLimit' => 'limit',
            'fullResult' => false
        ];
        $this->setDataPager(array_merge($arrayDefault, $data));
        
        // current page
        if ($this->paramPager && Input::get($this->paramPager)) {
            $this->currentPage = (int) Input::get($this->paramPager);
        }
        
        // per page
        if ($this->paramLimit && Input::get($this->paramLimit)) {
            $this->perPage = (int) Input::get($this->paramLimit);
        }
        
        return $this;
    }
    
    /**
     * set data pager
     * 
     * @param array $arrayDataPager
     */
    public function setDataPager(array $arrayDataPager)
    {
        foreach ($arrayDataPager as $key => $item) {
            $this->{$key} = $item;
        }
        return $this;
    }
    
    /**
     * get value of data pager
     * 
     * @param string $key
     * @return type
     */
    public function getDataPager($key)
    {
        if (isset($this->{$key})) {
            return $this->{$key};
        }
        return null;
    }
    
    /**
     * exec query
     */
    public function execQuery()
    {
        // reset limit
        $this->query['limit'] = '';
        
        // get total item
        $collectionTotal = DB::select(
            self::getQueryCount($this->query, $this->colCount),
            $this->bindings
        );
        if (isset($collectionTotal[0])) {
            $this->total = $collectionTotal[0]->count;
        }
        
        // last page
        if ($this->total) {
            $this->lastPage = intval($this->total / $this->perPage) + 1;
        }
        
        if (!is_numeric($this->currentPage) || $this->currentPage < 1) {
            $this->currentPage = 1;
        }
        // get item data
        $this->query['limit'] = $this->perPage . ' OFFSET ' . 
            $this->perPage * ($this->currentPage - 1);
        $this->data = DB::select(
            self::getQuery($this->query),
            $this->bindings
        );
        return $this;
    }
    
    /**
     * get full result
     */
    public function getFullResult()
    {
        $this->query['limit'] = '';
        return DB::select(
            self::getQuery($this->query),
            $this->bindings
        );
    }
    
    /**
     * render data pager collection
     * 
     * @return array
     */
    public function renderPager()
    {   
        return [
            'data' => $this->data,
            'last_page'=> $this->lastPage,
            'current_page' => $this->currentPage,
            'total' => $this->total,
            'per_page' => $this->perPage
        ];
    }
    
    /**
     * reset query in query builder
     * 
     * @return array
     */
    public static function resetQuery()
    {
        return [
            'select' => '',
            'from' => '',
            'join' => '',
            'where' => '',
            'order' => '',
            'group' => '',
            'limit' => '',
        ];
    }
    
    
    
    /**
     * get query string
     * 
     * @param array $query
     * @return string
     */
    public static function getQuery($query)
    {
        $result = 'SELECT ' . $query['select'] . ' FROM ' . $query['from'];
        if ($query['join']) {
            $result .= ' ' . $query['join'];
        }
        if ($query['where']) {
            $result .= ' WHERE 1 ' . $query['where'];
        }
        if ($query['group']) {
            $result .= ' GROUP BY ' . $query['group'];
        }
        if ($query['order']) {
            $result .= ' ORDER BY ' . $query['order'];
        }
        if ($query['limit']) {
            $result .= ' LIMIT ' . $query['limit'];
        }
        return $result;
    }
    
    /**
     * get number of record
     * 
     * @param array $query
     * @return string
     */
    public static function getQueryCount($query, $col = 'COUNT(*)')
    {
        $result = 'SELECT ' . $col . ' FROM ' . $query['from'];
        if ($query['join']) {
            $result .= ' ' . $query['join'];
        }
        if ($query['where']) {
            $result .= ' WHERE 1 ' . $query['where'];
        }
        if ($query['group']) {
            $result .= ' GROUP BY ' . $query['group'];
        }
        $result = 'SELECT COUNT(*) AS count FROM ('.$result.') AS tmp_tbl_count';
        return $result;
    }
    
    /**
     * array fill to ? symbol 
     * 
     * @param array $array
     * @return string
     */
    public static function convertArraySymPDO($array)
    {
        $str = '';
        for ($i = 0; $i < count($array); $i++) {
            $str .= '?,';
        }
        return substr($str, 0, -1);
    }
}
