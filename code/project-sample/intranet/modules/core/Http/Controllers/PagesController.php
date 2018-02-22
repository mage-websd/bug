<?php

namespace Rikkei\Core\Http\Controllers;

use Auth;
use Illuminate\Support\Facades\Session;
use Rikkei\News\Http\Controllers\PostController;

class PagesController extends Controller
{
    /**
     * Display home page
     *
     * @return \Illuminate\Http\Response
     */
    public function home()
    {
        if (Auth::guest()) {
            $errors = Session::get('errors', new \Illuminate\Support\MessageBag);
            if ($errors && count($errors) > 0) {
                return view('errors.general');
            }
            return view('core::welcome');
        }
        $post = new PostController();
        return $post->index();
    }
}
