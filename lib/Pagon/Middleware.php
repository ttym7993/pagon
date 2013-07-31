<?php

namespace Pagon;

/**
 * Middleware
 * structure of base middleware
 *
 * @package Pagon
 */
abstract class Middleware extends EventEmitter
{
    const _CLASS_ = __CLASS__;

    /**
     * @var Http\Input|Cli\Input
     */
    protected $input;

    /**
     * @var Http\Output|Cli\Output
     */
    protected $output;

    /**
     * @var App
     */
    protected $app;

    /**
     * @var array Default options
     */
    protected $options = array();

    /**
     * @var callable
     */
    protected $next;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;
    }

    /**
     * Create new middleware or route
     *
     * @param string|\Closure $route
     * @param array           $options
     * @throws \InvalidArgumentException
     * @return bool|Route
     */
    public static function build($route, array $options = array())
    {
        if (is_object($route)) return $route;

        if (!is_string($route)) throw new \InvalidArgumentException('The parameter $route need string');

        // Try to use custom parser
        if (!is_subclass_of($class = $route, __CLASS__, true)
            && !is_subclass_of($class = __NAMESPACE__ . "\\Middleware\\" . $route, __CLASS__, true)
        ) {
            throw new \InvalidArgumentException("Non-exists route class '$route'");
        }

        return new $class($options);
    }

    /**
     * Graft the route inner
     *
     * @param \Closure|string $route
     * @param array           $option
     * @throws \RuntimeException
     * @return mixed
     */
    public function graft($route, array $option = array())
    {
        if (!$route = self::build($route, $option)) {
            throw new \RuntimeException("Graft \"$route\" fail");
        }

        return call_user_func_array($route, array(
            $this->input, $this->output, $this->next
        ));
    }

    /**
     * @return mixed
     */
    abstract function call();

    /**
     * @param $input
     * @param $output
     * @param $next
     */
    public function __invoke($input, $output, $next)
    {
        $this->input = $input;
        $this->output = $output;
        $this->app = $input->app;
        $this->next = $next;
        $this->call();
    }

    /**
     * Call next
     */
    public function next()
    {
        call_user_func($this->next);
    }
}
