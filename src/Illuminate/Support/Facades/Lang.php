<?php

namespace Illuminate\Support\Facades;

/**

 *
 * @mixin \Illuminate\Translation\Translator
 */
class Lang extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'translator';
    }
}
