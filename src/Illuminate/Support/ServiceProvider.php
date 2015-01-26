<?php namespace Illuminate\Support;

use BadMethodCallException;

abstract class ServiceProvider {

	/**
	 * The application instance.
	 *
	 * @var \Illuminate\Contracts\Foundation\Application
	 */
	protected $app;

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * The paths that should be published.
	 *
	 * @var array
	 */
	protected static $publishes = [];

	/**
	 * Create a new service provider instance.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @return void
	 */
	public function __construct($app)
	{
		$this->app = $app;
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	abstract public function register();

	/**
	 * Register the package defaults.
	 *
	 * @param  string  $key
	 * @param  string  $path
	 * @return void
	 */
	protected function loadConfigFrom($key, $path)
	{
		$defaults = $this->app['files']->getRequire($path);
		$config = $this->app['config']->get($key, []);
		$this->app['config']->set($key, config_merge($defaults, $config));
	}

	/**
	 * Register a view file namespace.
	 *
	 * @param  string  $namespace
	 * @param  string  $path
	 * @return void
	 */
	protected function loadViewsFrom($namespace, $path)
	{
		if (is_dir($appPath = $this->app->basePath().'/resources/views/vendor/'.$namespace))
		{
			$this->app['view']->addNamespace($namespace, $appPath);
		}

		$this->app['view']->addNamespace($namespace, $path);
	}

	/**
	 * Register a translation file namespace.
	 *
	 * @param  string  $namespace
	 * @param  string  $path
	 * @return void
	 */
	protected function loadTranslationsFrom($namespace, $path)
	{
		$this->app['translator']->addNamespace($namespace, $path);
	}

	/**
	 * Register paths to be published by the publish command.
	 *
	 * @param  array  $paths
	 * @return void
	 */
	protected function publishes(array $paths)
	{
		static::$publishes = array_merge(static::$publishes, $paths);
	}

	/**
	 * Get the paths to publish.
	 *
	 * @return array
	 */
	public static function pathsToPublish()
	{
		return static::$publishes;
	}

	/**
	 * Register the package's custom Artisan commands.
	 *
	 * @param  array  $commands
	 * @return void
	 */
	public function commands($commands)
	{
		$commands = is_array($commands) ? $commands : func_get_args();

		// To register the commands with Artisan, we will grab each of the arguments
		// passed into the method and listen for Artisan "start" event which will
		// give us the Artisan console instance which we will give commands to.
		$events = $this->app['events'];

		$events->listen('artisan.start', function($artisan) use ($commands)
		{
			$artisan->resolveCommands($commands);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}

	/**
	 * Get the events that trigger this service provider to register.
	 *
	 * @return array
	 */
	public function when()
	{
		return [];
	}

	/**
	 * Determine if the provider is deferred.
	 *
	 * @return bool
	 */
	public function isDeferred()
	{
		return $this->defer;
	}

	/**
	 * Get a list of files that should be compiled for the package.
	 *
	 * @return array
	 */
	public static function compiles()
	{
		return [];
	}

	/**
	 * Dynamically handle missing method calls.
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		if ($method == 'boot') return;

		throw new BadMethodCallException("Call to undefined method [{$method}]");
	}

}
