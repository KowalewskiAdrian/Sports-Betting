<?php namespace Illuminate\Events;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;

class ListenerServiceProvider extends ServiceProvider {

	/**
	 * Register the application's event listeners.
	 *
	 * @param  \Illuminate\Contracts\Events\Dispatcher  $events
	 * @return void
	 */
	public function boot(DispatcherContract $events)
	{
		foreach ($this->listen as $event => $listeners)
		{
			foreach ($listeners as $listener)
			{
				$events->listen($event, $listener);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		//
	}

}
