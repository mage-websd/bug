<?php

namespace Rikkei\Core\View;

use Illuminate\Support\Facades\Validator;

class ValidatorExtendCore
{
    /**
     * add validator after or equal date
     */
    public static function addEmailRK()
    {
        Validator::extend('email_rk', function ($attribute, $value, $parameters) {
            return in_array(
                preg_replace('/^.*@/', '', $value),
                (array) config('domain_logged')
            );
        });
    }
}