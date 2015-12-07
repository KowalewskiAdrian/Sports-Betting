<?php

namespace Illuminate\Foundation\Testing;

use Exception;

trait WithoutEvents
{
    /**
     * @before
     */
    public function disableEventsForAllTests()
    {
        $this->afterApplicationCreated(function () {
            if (method_exists($this, 'withoutEvents')) {
                $this->withoutEvents();
            } else {
                throw new Exception('Unable to disable middleware. ApplicationTrait not used.');
            }
        });
    }
}
