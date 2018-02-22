<?php

namespace Rikkei\Core\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class ErrorController extends Controller
{
    /**
     * Display page 404
     *
     * @return \Illuminate\Http\Response
     */
    public function noRoute()
    {
        if (Auth::guest()) {
            return redirect('/');
        }
        return view('core::errors.404');
    }
    
    /**
     * error system
     */
    public function errors()
    {
        if (Auth::guest()) {
            return redirect('/');
        }
        return view('core::errors.exception');
    }
    
    /**
     * error general page
     */
    public function errorGeneral()
    {
        return view('core::errors.general');
    }
}
