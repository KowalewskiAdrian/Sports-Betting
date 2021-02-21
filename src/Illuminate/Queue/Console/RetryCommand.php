<?php

namespace Illuminate\Queue\Console;

use DateTimeInterface;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;

class RetryCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'queue:retry
                            {id?* : The ID of the failed job or "all" to retry all jobs}
                            {--range=* : Range of job IDs (numeric) to be retried}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry a failed queue job';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->getJobIds() as $id) {
            $job = $this->laravel['queue.failer']->find($id);

            if (is_null($job)) {
                $this->error("Unable to find failed job with ID [{$id}].");
            } else {
                $this->retryJob($job);

                $this->info("The failed job [{$id}] has been pushed back onto the queue!");

                $this->laravel['queue.failer']->forget($id);
            }
        }
    }

    /**
     * Get the job IDs to be retried.
     *
     * @return array
     */
    protected function getJobIds()
    {
        $ids = (array) $this->argument('id');

        if (count($ids) === 1 && $ids[0] === 'all') {
            return Arr::pluck($this->laravel['queue.failer']->all(), 'id');
        }

        if ($ranges = (array) $this->option('range')) {
            $ids = array_merge($ids, $this->getJobIdsByRanges($ranges));
        }

        return array_values(array_filter(array_unique($ids)));
    }

    /**
     * Get the job IDs ranges, if applicable.
     *
     * @param  array  $ranges
     * @return array
     */
    protected function getJobIdsByRanges(array $ranges)
    {
        $ids = [];

        foreach ($ranges as $range) {
            if (preg_match('/^[0-9]+\-[0-9]+$/', $range)) {
                $ids = array_merge($ids, range(...explode('-', $range)));
            }
        }

        return $ids;
    }

    /**
     * Retry the queue job.
     *
     * @param  \stdClass  $job
     * @return void
     */
    protected function retryJob($job)
    {
        $this->laravel['queue']->connection($job->connection)->pushRaw(
            $this->refreshRetryUntil($this->resetAttempts($job->payload)),
            $job->queue
        );
    }

    /**
     * Reset the payload attempts.
     *
     * Applicable to Redis and other jobs which store attempts in their payload.
     *
     * @param  string  $payload
     * @return string
     */
    protected function resetAttempts($payload)
    {
        $payload = json_decode($payload, true);

        if (isset($payload['attempts'])) {
            $payload['attempts'] = 0;
        }

        return json_encode($payload);
    }

    /**
     * Refresh the "retry until" timestamp for the job.
     *
     * @param  string  $payload
     * @return string
     */
    protected function refreshRetryUntil($payload)
    {
        $payload = json_decode($payload, true);

        if (! isset($payload['data']['command'])) {
            return json_encode($payload);
        }

        if (Str::startsWith($payload['data']['command'], 'O:')) {
            $instance = unserialize($payload['data']['command']);
        }

        if (app()->bound(Encrypter::class)) {
            $instance = unserialize(app()->make(Encrypter::class)->decrypt($payload['data']['command']));
        }

        if (! isset($instance)) {
            throw new RuntimeException('Unable to extract job payload.');
        }

        if (is_object($instance) && method_exists($instance, 'retryUntil')) {
            $retryUntil = $instance->retryUntil();

            $payload['retryUntil'] = $retryUntil instanceof DateTimeInterface
                                        ? $retryUntil->getTimestamp()
                                        : $retryUntil;
        }

        return json_encode($payload);
    }
}
