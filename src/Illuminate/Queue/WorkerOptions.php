<?php

namespace Illuminate\Queue;

class WorkerOptions
{
    /**
     * The name of the worker.
     *
     * @var int
     */
    public $name;

    /**
     * The number of seconds to wait before retrying a job that encountered an uncaught exception.
     *
     * @var int
     */
    public $backoff;

    /**
     * The maximum amount of RAM the worker may consume.
     *
     * @var int
     */
    public $memory;

    /**
     * The maximum number of seconds a child worker may run.
     *
     * @var int
     */
    public $timeout;

    /**
     * The number of seconds to wait in between polling the queue.
     *
     * @var int
     */
    public $sleep;

    /**
     * The maximum amount of times a job may be attempted.
     *
     * @var int
     */
    public $maxTries;

    /**
     * Indicates if the worker should run in maintenance mode.
     *
     * @var bool
     */
    public $force;

    /**
     * Indicates if the worker should stop when queue is empty.
     *
     * @var bool
     */
    public $stopWhenEmpty;

    /**
     * Create a new worker options instance.
     *
     * @param  string  $name
     * @param  int  $backoff
     * @param  int  $memory
     * @param  int  $timeout
     * @param  int  $sleep
     * @param  int  $maxTries
     * @param  bool  $force
     * @param  bool  $stopWhenEmpty
     * @return void
     */
    public function __construct($name = 'default', $backoff = 0, $memory = 128, $timeout = 60, $sleep = 3, $maxTries = 1, $force = false, $stopWhenEmpty = false)
    {
        $this->name = $name;
        $this->backoff = $backoff;
        $this->sleep = $sleep;
        $this->force = $force;
        $this->memory = $memory;
        $this->timeout = $timeout;
        $this->maxTries = $maxTries;
        $this->stopWhenEmpty = $stopWhenEmpty;
    }
}
