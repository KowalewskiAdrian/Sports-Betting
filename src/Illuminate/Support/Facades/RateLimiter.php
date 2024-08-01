<?php

namespace Illuminate\Support\Facades;

/**
 * @mixin \Illuminate\Cache\RateLimiter
 */
class RateLimiter extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Illuminate\Cache\RateLimiter::class;
    }
}
