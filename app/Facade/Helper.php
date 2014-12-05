<?php

namespace Lycee\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Lycee\Facade/Helper
 */
class Helper extends Facade {

    public static function getFacadeAccessor()
    {
        return 'Lycee\Tool\Helper';
    }
}