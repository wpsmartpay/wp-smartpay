<?php

namespace SmartPay\Framework\View;

use InvalidArgumentException;
use SmartPay\Framework\Application;
use SmartPay\Framework\Exception\UnResolveableEntityException;

class View
{
    protected $app;

    protected $path;

    protected $data = [];

    protected static $sharedData = [];

    protected static $composerCallbacks = [];

    /**
     * Create a new view instance.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function addNamespace($namespace, $path)
    {
        $key = 'path.view.extras';

        $path = str_replace('.', DIRECTORY_SEPARATOR, $path);

        try {
            $this->app[$key] = array_merge(
                $this->app[$key],
                [$namespace => $path]
            );
        } catch (UnResolveableEntityException $e) {
            $this->app[$key] = array($namespace => $path);
        }
    }

    /**
     * Generate and echo/print a view file
     * @param  string $path
     * @param  array  $data
     * @return void
     */
    public function render($path, $data = [])
    {
        echo $this->make($path, $data);
    }

    /**
     * Generate a view file
     * @param  string $path
     * @param  array  $data
     * @return string [generated html]
     * @throws InvalidArgumentException
     */
    public function make($path, $data = [])
    {
        if (file_exists($this->path = $this->resolveFilePath($path))) {
            $this->data = $data;
            return $this;
        }

        throw new InvalidArgumentException("The view file [{$this->path}] doesn't exists!");
    }

    /**
     * Resolve the view file path
     * @param  string $path
     * @return string
     */
    protected function resolveFilePath($path)
    {
        $path = str_replace('.', DIRECTORY_SEPARATOR, $path);

        if (strpos($path, '::') !== false) {
            list($namespace, $path) = explode('::', $path);
            $viewName = $this->app['path.view.extras'][$namespace] . DIRECTORY_SEPARATOR . $path;
        } else {
            $viewName = $this->app->viewPath($path);
        }

        return $viewName . '.php';
    }

    /**
     * Evaluate the view file
     * @param  string $path
     * @param  string $data
     * @return $this
     */
    protected function renderContent()
    {
        $this->callComposerCallbacks();

        $renderOutput = function ($app) {
            ob_start() && extract(
                $this->gatherData(),
                EXTR_SKIP
            );

            include $this->path;

            return ltrim(ob_get_clean());
        };

        return $renderOutput($this->app);
    }

    /**
     * Call registered composer callbacks for this view
     * @return void
     */
    protected function callComposerCallbacks()
    {
        if (array_key_exists($this->path, static::$composerCallbacks)) {
            foreach (static::$composerCallbacks[$this->path] as $callback) {
                $callback($this);
            }
        }
    }

    /**
     * Gether shared & view data
     * @return array
     */
    protected function gatherData()
    {
        return array_merge(static::$sharedData, $this->data);
    }

    /**
     * Share global data for any view
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function share($key, $value)
    {
        static::$sharedData[$key] = $value;
    }

    /**
     * Register view composer calbacks for specific view
     * @param  string $viewName
     * @param  closure $callback
     * @return void
     */
    public function composer($viewName, $callback)
    {
        $path = $this->resolveFilePath($viewName);
        static::$composerCallbacks[$path][] = $callback;
    }

    /**
     * Provides a fluent interface to set data
     * @param  mixed $key
     * @param  mixed $data
     * @return $this
     */
    public function with($name, $data = [])
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->__set($key, $value);
            }
        } else {
            $this->__set($name, $data);
        }

        return $this;
    }

    /**
     * Setter for the view
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Dump the view result
     * @return string
     */
    public function __toString()
    {
        return $this->renderContent();
    }
}
