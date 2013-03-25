<?php namespace Illuminate\Foundation\Testing;

use Illuminate\Auth\UserInterface;

class TestCase extends \PHPUnit_Framework_TestCase {

	/**
	 * The Illuminate application instance.
	 *
	 * @var Illuminate\Foundation\Application
	 */
	protected $app;

	/**
	 * The HttpKernel client instance.
	 *
	 * @var Illuminate\Foundation\Testing\Client
	 */
	protected $client;

	/**
	 * Setup the test environment.
	 *
	 * @return void
	 */
	public function setUp()
	{
		$this->refreshApplication();
	}

	/**
	 * Refresh the application instance.
	 *
	 * @return void
	 */
	protected function refreshApplication()
	{
		$this->app = $this->createApplication();

		$this->client = $this->createClient();
	}

	/**
	 * Call the given URI and return the Response.
	 *
	 * @param  string  $method
	 * @param  string  $uri
	 * @param  array   $parameters
	 * @param  array   $files
	 * @param  array   $server
	 * @param  string  $content
	 * @param  bool    $changeHistory
	 * @return Illuminate\Http\Response
	 */
	public function call()
	{
		call_user_func_array(array($this->client, 'request'), func_get_args());

		return $this->client->getResponse();
	}

	/**
	 * Call a controller action and return the Response.
	 *
	 * @param  string  $method
	 * @param  string  $action
	 * @param  array   $wildcards
	 * @param  array   $parameters
	 * @param  array   $files
	 * @param  array   $server
	 * @param  string  $content
	 * @param  bool    $changeHistory
	 * @return Illuminate\Http\Response
	 */
	public function action($method, $action, $wildcards = array(), $parameters = array(), $files = array(), $server = array(), $content = null, $changeHistory = true)
	{
		$uri = $this->app['url']->action($action, $wildcards, false);

		return $this->call($method, $uri, $parameters, $files, $server, $content, $changeHistory);
	}

	/**
	 * Call a named route and return the Response.
	 *
	 * @param  string  $method
	 * @param  string  $name
	 * @param  array   $routeParameters
	 * @param  array   $parameters
	 * @param  array   $files
	 * @param  array   $server
	 * @param  string  $content
	 * @param  bool    $changeHistory
	 * @return Illuminate\Http\Response
	 */
	public function route($method, $name, $routeParameters = array(), $parameters = array(), $files = array(), $server = array(), $content = null, $changeHistory = true)
	{
		$uri = $this->app['url']->route($name, $routeParameters, false);

		return $this->call($method, $uri, $parameters, $files, $server, $content, $changeHistory);
	}

	/**
	 * Assert that the client response has an OK status code.
	 *
	 * @return void
	 */
	public function assertResponseOk()
	{
		return $this->assertTrue($this->client->getResponse()->isOk());
	}

	/**
	 * Assert that the response view has a given piece of bound data.
	 *
	 * @param  string|array  $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function assertViewHas($key, $value = null)
	{
		if (is_array($key)) return $this->assertViewHasAll($key);

		$response = $this->client->getResponse()->original;

		if (is_null($value))
		{
			$this->assertArrayHasKey($key, $response->getData());
		}
		else
		{
			$this->assertEquals($value, $response->$key);
		}
	}

	/**
	 * Assert that the view has a given list of bound data.
	 *
	 * @param  array  $bindings
	 * @return void
	 */
	public function assertViewHasAll(array $bindings)
	{
		foreach ($bindings as $key => $value)
		{
			$this->assertViewHas($key, $value);
		}
	}

	/**
	 * Assert whether the client was redirected to a given URI.
	 *
	 * @param  string  $uri
	 * @param  array   $with
	 * @return void
	 */
	public function assertRedirectedTo($uri, $with = array())
	{
		$response = $this->client->getResponse();

		$this->assertInstanceOf('Illuminate\Http\RedirectResponse', $response);

		$this->assertEquals($this->app['url']->to($uri), $response->headers->get('Location'));

		$this->assertSessionHasAll($with);
	}

	/**
	 * Assert whether the client was redirected to a given route.
	 *
	 * @param  string  $name
	 * @param  array   $with
	 * @return void
	 */
	public function assertRedirectedToRoute($name, $with = array())
	{
		$this->assertRedirectedTo($this->app['url']->route($name), $with);
	}

	/**
	 * Assert whether the client was redirected to a given action.
	 *
	 * @param  string  $name
	 * @param  array   $with
	 * @return void
	 */
	public function assertRedirectedToAction($name, $with = array())
	{
		$this->assertRedirectedTo($this->app['url']->action($name), $with);
	}

	/**
	 * Assert that the session has a given list of values.
	 *
	 * @param  string|array  $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function assertSessionHas($key, $value = null)
	{
		if (is_array($key)) return $this->assertSessionHasAll($key);

		if (is_null($value))
		{
			$this->assertTrue($this->app['session']->has($key));
		}
		else
		{
			$this->assertEquals($value, $this->app['session']->get($key));
		}
	}

	/**
	 * Assert that the session has a given list of values.
	 *
	 * @param  array  $bindings
	 * @return void
	 */
	public function assertSessionHasAll(array $bindings)
	{
		foreach ($bindings as $key => $value)
		{
			if (is_int($key))
			{
				$this->assertSessionHas($value);
			}
			else
			{
				$this->assertSessionHas($key, $value);
			}
		}
	}

	/**
	 * Assert that the session has errors bound.
	 *
	 * @return void
	 */
	public function assertSessionHasErrors()
	{
		return $this->assertSessionHas('errors');
	}

	/**
	 * Set the currently logged in user for the application.
	 *
	 * @param  Illuminate\Auth\UserInterface  $user
	 * @param  string  $driver
	 * @return void
	 */
	public function be(UserInterface $user, $driver = null)
	{
		$this->app['auth']->driver($driver)->setUser($user);
	}

	/**
	 * Seed a given database connection.
	 *
	 * @param  string  $class
	 * @return void
	 */
	public function seed($class = 'DatabaseSeeder')
	{
		$this->app[$class]->run();
	}

	/**
	 * Create a new HttpKernel client instance.
	 *
	 * @param  array  $server
	 * @return Symfony\Component\HttpKernel\Client
	 */
	protected function createClient(array $server = array())
	{
		return new Client($this->app, $server);
	}

}
