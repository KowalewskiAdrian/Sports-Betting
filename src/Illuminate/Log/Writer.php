<?php namespace Illuminate\Log;

use Closure;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Support\JsonableInterface;
use Illuminate\Contracts\Support\ArrayableInterface;
use Illuminate\Contracts\Logging\Logger as LoggerContract;

class Writer implements LoggerContract {

	/**
	 * The Monolog logger instance.
	 *
	 * @var \Monolog\Logger
	 */
	protected $monolog;

	/**
	 * All of the error levels.
	 *
	 * @var array
	 */
	protected $levels = array(
		'debug',
		'info',
		'notice',
		'warning',
		'error',
		'critical',
		'alert',
		'emergency',
	);

	/**
	 * The event dispatcher instance.
	 *
	 * @var \Illuminate\Contracts\Events\Dispatcher
	 */
	protected $dispatcher;

	/**
	 * Create a new log writer instance.
	 *
	 * @param  \Monolog\Logger  $monolog
	 * @param  \Illuminate\Contracts\Events\Dispatcher  $dispatcher
	 * @return void
	 */
	public function __construct(MonologLogger $monolog, Dispatcher $dispatcher = null)
	{
		$this->monolog = $monolog;

		if (isset($dispatcher))
		{
			$this->dispatcher = $dispatcher;
		}
	}

	/**
	 * Log an alert message to the logs.
	 *
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function alert($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}

	/**
	 * Log a critical message to the logs.
	 *
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function critical($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}

	/**
	 * Log an error message to the logs.
	 *
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function error($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}

	/**
	 * Log a warning message to the logs.
	 *
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function warning($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}

	/**
	 * Log a notice to the logs.
	 *
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function notice($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}

	/**
	 * Log an informational message to the logs.
	 *
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function info($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}

	/**
	 * Log a debug message to the logs.
	 *
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function debug($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}

	/**
	 * Log a message to the logs.
	 *
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function log($level, $message, array $context = array())
	{
		return $this->writeLog($level, $message, $context);
	}

	/**
	 * Dynamically pass log calls into the writer.
	 *
	 * @param  string  $level
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function write($level, $message, array $context = array())
	{
		return $this->log($level, $message, $context);
	}

	/**
	 * Write a message to Monolog.
	 *
	 * @param  string  $level
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	protected function writeLog($level, $message, $context)
	{
		$this->fireLogEvent($level, $message = $this->formatMessage($message), $context);

		$this->monolog->{$level}($message, $context);
	}

	/**
	 * Register a file log handler.
	 *
	 * @param  string  $path
	 * @param  string  $level
	 * @return void
	 */
	public function useFiles($path, $level = 'debug')
	{
		$this->monolog->pushHandler($handler = new StreamHandler($path, $this->parseLevel($level)));

		$handler->setFormatter($this->getDefaultFormatter());
	}

	/**
	 * Register a daily file log handler.
	 *
	 * @param  string  $path
	 * @param  int     $days
	 * @param  string  $level
	 * @return void
	 */
	public function useDailyFiles($path, $days = 0, $level = 'debug')
	{
		$this->monolog->pushHandler(
			$handler = new RotatingFileHandler($path, $days, $this->parseLevel($level))
		);

		$handler->setFormatter($this->getDefaultFormatter());
	}

	/**
	 * Register an error_log handler.
	 *
	 * @param  string  $level
	 * @param  integer $messageType
	 * @return void
	 */
	public function useErrorLog($level = 'debug', $messageType = ErrorLogHandler::OPERATING_SYSTEM)
	{
		$this->monolog->pushHandler(
			$handler = new ErrorLogHandler($messageType, $this->parseLevel($level))
		);

		$handler->setFormatter($this->getDefaultFormatter());
	}

	/**
	 * Register a new callback handler for when
	 * a log event is triggered.
	 *
	 * @param  \Closure  $callback
	 * @return void
	 *
	 * @throws \RuntimeException
	 */
	public function listen(Closure $callback)
	{
		if ( ! isset($this->dispatcher))
		{
			throw new \RuntimeException("Events dispatcher has not been set.");
		}

		$this->dispatcher->listen('illuminate.log', $callback);
	}

	/**
	 * Fires a log event.
	 *
	 * @param  string  $level
	 * @param  string  $message
	 * @param  array   $context
	 * @return void
	 */
	protected function fireLogEvent($level, $message, array $context = array())
	{
		// If the event dispatcher is set, we will pass along the parameters to the
		// log listeners. These are useful for building profilers or other tools
		// that aggregate all of the log messages for a given "request" cycle.
		if (isset($this->dispatcher))
		{
			$this->dispatcher->fire('illuminate.log', compact('level', 'message', 'context'));
		}
	}

	/**
	 * Format the parameters for the logger.
	 *
	 * @param  mixed  $parameters
	 * @return void
	 */
	protected function formatMessage($message)
	{
		if (is_array($message))
		{
			return var_export($message, true);
		}
		elseif ($message instanceof JsonableInterface)
		{
			return $message->toJson();
		}
		elseif ($message instanceof ArrayableInterface)
		{
			return var_export($message->toArray(), true);
		}

		return $message;
	}

	/**
	 * Parse the string level into a Monolog constant.
	 *
	 * @param  string  $level
	 * @return int
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function parseLevel($level)
	{
		switch ($level)
		{
			case 'debug':
				return MonologLogger::DEBUG;

			case 'info':
				return MonologLogger::INFO;

			case 'notice':
				return MonologLogger::NOTICE;

			case 'warning':
				return MonologLogger::WARNING;

			case 'error':
				return MonologLogger::ERROR;

			case 'critical':
				return MonologLogger::CRITICAL;

			case 'alert':
				return MonologLogger::ALERT;

			case 'emergency':
				return MonologLogger::EMERGENCY;

			default:
				throw new \InvalidArgumentException("Invalid log level.");
		}
	}

	/**
	 * Get the underlying Monolog instance.
	 *
	 * @return \Monolog\Logger
	 */
	public function getMonolog()
	{
		return $this->monolog;
	}

	/**
	 * Get a defaut Monolog formatter instance.
	 *
	 * @return \Monolog\Formatter\LineFormatter
	 */
	protected function getDefaultFormatter()
	{
		return new LineFormatter(null, null, true);
	}

	/**
	 * Get the event dispatcher instance.
	 *
	 * @return \Illuminate\Contracts\Events\Dispatcher
	 */
	public function getEventDispatcher()
	{
		return $this->dispatcher;
	}

	/**
	 * Set the event dispatcher instance.
	 *
	 * @param  \Illuminate\Contracts\Events\Dispatcher
	 * @return void
	 */
	public function setEventDispatcher(Dispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

}
