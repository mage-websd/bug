<?php
namespace Rikkei\Core\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Rikkei\Core\View\Breadcrumb;
use Illuminate\Support\Facades\URL;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;
    
    /**
     * constructor
     */
    public function __construct()
    {
        Breadcrumb::add('Home', URL::to('/'), '<i class="fa fa-dashboard"></i>');
        $this->_construct();
    }
    
    /**
     * construct more
     * 
     * @return \Rikkei\Core\Http\Controllers\Controller
     */
    protected function _construct()
    {
        return $this;
    }
}
