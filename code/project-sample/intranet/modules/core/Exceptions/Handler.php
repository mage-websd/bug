<?php

namespace Rikkei\Core\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Log;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Exception\HttpResponseException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        //model not found (while use findOrFail function)
        if ($e instanceof ModelNotFoundException) {
            if (auth()->check()) {
                return view('core::errors.404', ['message' => trans('core::message.Not found entity')]);
            }
            return view('core::errors.no_entity');
        }
        if ($this->isHttpException($e)) {
            $status = $e->getStatusCode();
            switch ($status) {
                case 404:
                    return redirect()->route('core::no.route');
            }
        }
        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }
        Log::info($e);
        switch ($e->getCode()) {
            case \Rikkei\Core\Model\CoreModel::ERROR_CODE_EXCEPTION;
                return redirect()->back()->withErrors($e->getMessage());
            default:
                break;
        }
        if (trim(App::environment()) == 'production') {
            return redirect(Config::get('general.errors.general'));
        }
        //error page full default
        return parent::render($request, $e); 
    }
}
