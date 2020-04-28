<?php

namespace Illuminate\View;

use ArrayIterator;
use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Support\DeferringDisplayableValue;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Str;
use IteratorAggregate;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

abstract class Component
{
    /**
     * The cache of public property names, keyed by class.
     *
     * @var array
     */
    protected static $propertyCache = [];

    /**
     * The cache of public method names, keyed by class.
     *
     * @var array
     */
    protected static $methodCache = [];

    /**
     * The properties / methods that should not be exposed to the component.
     *
     * @var array
     */
    protected $except = [];

    /**
     * The component alias name.
     *
     * @var string
     */
    public $componentName;

    /**
     * The component attributes.
     *
     * @var \Illuminate\View\ComponentAttributeBag
     */
    public $attributes;

    /**
     * Get the view / view contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    abstract public function render();

    /**
     * Resolve the Blade view or view file that should be used when rendering the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function resolveView()
    {
        $view = $this->render();

        if ($view instanceof View) {
            return $view;
        }

        $factory = Container::getInstance()->make('view');

        return $factory->exists($view)
                    ? $view
                    : $this->createBladeViewFromString($factory, $view);
    }

    /**
     * Create a Blade view with the raw component string content.
     *
     * @param  \Illuminate\Contracts\View\Factory  $factory
     * @param  string  $contents
     * @return string
     */
    protected function createBladeViewFromString($factory, $contents)
    {
        $factory->addNamespace(
            '__components',
            $directory = Container::getInstance()['config']->get('view.compiled')
        );

        if (! file_exists($viewFile = $directory.'/'.sha1($contents).'.blade.php')) {
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($viewFile, $contents);
        }

        return '__components::'.basename($viewFile, '.blade.php');
    }

    /**
     * Get the data that should be supplied to the view.
     *
     * @author Freek Van der Herten
     * @author Brent Roose
     *
     * @return array
     */
    public function data()
    {
        $this->attributes = $this->attributes ?: new ComponentAttributeBag;

        return array_merge($this->extractPublicProperties(), $this->extractPublicMethods());
    }

    /**
     * Extract the public properties for the component.
     *
     * @return array
     */
    protected function extractPublicProperties()
    {
        $class = get_class($this);

        if (! isset(static::$propertyCache[$class])) {
            $reflection = new ReflectionClass($this);

            static::$propertyCache[$class] = collect($reflection->getProperties(ReflectionProperty::IS_PUBLIC))
                ->reject(function (ReflectionProperty $property) {
                    return $this->shouldIgnore($property->getName());
                })
                ->map(function (ReflectionProperty $property) {
                    return $property->getName();
                })->all();
        }

        $values = [];

        foreach (static::$propertyCache[$class] as $property) {
            $values[$property] = $this->{$property};
        }

        return $values;
    }

    /**
     * Extract the public methods for the component.
     *
     * @return array
     */
    protected function extractPublicMethods()
    {
        $class = get_class($this);

        if (! isset(static::$methodCache[$class])) {
            $reflection = new ReflectionClass($this);

            static::$methodCache[$class] = collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
                ->reject(function (ReflectionMethod $method) {
                    return $this->shouldIgnore($method->getName());
                })
                ->map(function (ReflectionMethod $method) {
                    return $method->getName();
                });
        }

        $values = [];

        foreach (static::$methodCache[$class] as $method) {
            $values[$method] = $this->createVariableFromMethod(new ReflectionMethod($this, $method));
        }

        return $values;
    }

    /**
     * Create a callable variable from the given method.
     *
     * @param  \ReflectionMethod  $method
     * @return mixed
     */
    protected function createVariableFromMethod(ReflectionMethod $method)
    {
        return $method->getNumberOfParameters() === 0
                        ? $this->createInvokableVariable($method->getName())
                        : Closure::fromCallable([$this, $method->getName()]);
    }

    /**
     * Create an invokable, toStringable variable for the given component method.
     *
     * @param  string  $method
     * @return object
     */
    protected function createInvokableVariable(string $method)
    {
        return new class(function () use ($method) {
            return $this->{$method}();
        }) implements DeferringDisplayableValue, IteratorAggregate {
            protected $callable;

            public function __construct(Closure $callable)
            {
                $this->callable = $callable;
            }

            public function resolveDisplayableValue()
            {
                return $this->__invoke();
            }

            public function getIterator()
            {
                $result = $this->__invoke();

                return new ArrayIterator($result instanceof Enumerable ? $result->all() : $result);
            }

            public function __get($key)
            {
                return $this->__invoke()->{$key};
            }

            public function __call($method, $parameters)
            {
                return $this->__invoke()->{$method}(...$parameters);
            }

            public function __invoke()
            {
                return call_user_func($this->callable);
            }

            public function __toString()
            {
                return (string) $this->__invoke();
            }
        };
    }

    /**
     * Determine if the given property / method should be ignored.
     *
     * @param  string  $name
     * @return bool
     */
    protected function shouldIgnore($name)
    {
        return Str::startsWith($name, '__') ||
               in_array($name, $this->ignoredMethods());
    }

    /**
     * Get the methods that should be ignored.
     *
     * @return array
     */
    protected function ignoredMethods()
    {
        return array_merge([
            'data',
            'render',
            'resolveView',
            'shouldRender',
            'view',
            'withName',
            'withAttributes',
        ], $this->except);
    }

    /**
     * Set the component alias name.
     *
     * @param  string  $name
     * @return $this
     */
    public function withName($name)
    {
        $this->componentName = $name;

        return $this;
    }

    /**
     * Set the extra attributes that the component should make available.
     *
     * @param  array  $attributes
     * @return $this
     */
    public function withAttributes(array $attributes)
    {
        $this->attributes = $this->attributes ?: new ComponentAttributeBag;

        $this->attributes->setAttributes($attributes);

        return $this;
    }

    /**
     * Determine if the component should be rendered.
     *
     * @return bool
     */
    public function shouldRender()
    {
        return true;
    }
}
