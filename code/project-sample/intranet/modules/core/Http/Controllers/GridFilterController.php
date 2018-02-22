<?php
namespace Rikkei\Core\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Rikkei\Core\View\CookieCore;

class GridFilterController extends Controller
{
    /**
     * add data grid filter follow url
     */
    public function request()
    {
        if (!Input::get('current_url') ||
            !app('request')->ajax()
        ) {
            return response()->json([
                'error' => 1
            ]);
        }
        $urlEncode = Input::get('current_url');
        CookieCore::forgetRaw('filter.' . $urlEncode);
        CookieCore::forgetRaw('filter_pager.' . $urlEncode);
        CookieCore::setRaw('filter.' . $urlEncode, Input::get('filter'));
        return response()->json([
            'success' => 1
        ]);
    }
    
    /**
     * add data grid filter follow url
     */
    public function pager()
    {
        if (!Input::get('current_url') ||
            !app('request')->ajax()
        ) {
            return response()->json([
                'error' => 1
            ]);
        }
        $urlEncode = Input::get('current_url');
        CookieCore::forgetRaw('filter_pager.' . $urlEncode);
        CookieCore::setRaw('filter_pager.' . $urlEncode, Input::get('filter_pager'));
        return response()->json([
            'success' => 1
        ]);
    }
    
    /**
     * remove filter data
     * 
     * @return type
     */
    public function remove()
    {
        if (!Input::get('current_url') ||
            !app('request')->ajax()
        ) {
            return response()->json([
                'error' => 1
            ]);
        }
        $urlEncode = Input::get('current_url');
        CookieCore::forgetRaw('filter.' . $urlEncode);
        CookieCore::forgetRaw('filter_pager.' . $urlEncode);
        return response()->json([
            'success' => 1
        ]);
    }
    
    /**
     * flush filter data
     * 
     * @return type
     */
    public function flush()
    {
        return $this->remove();
    }
    
    /**
     * remove session pager data
     */
    public function forgerPager()
    {
        return $this->remove();
    }
}
