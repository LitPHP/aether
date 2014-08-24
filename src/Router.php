<?php namespace Aether;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Pimple\Container;

/**
 * User: mcfog
 * Date: 14-8-23
 */

/**
 * FastRoute wrapper
 *
 * @package Aether
 */
class Router
{
    const D_COLLECTOR = 'aether.router.collector';
    const D_GENERATOR = 'aether.router.generator';
    const D_PARSER = 'aether.router.parser';
    const D_DISPATCHER_FACTORY = 'aether.router.dispatcher';

    protected $methods = array('GET', 'POST', 'PUT', 'DELETE');
    /**
     * @var Container
     */
    protected $dependency;
    /**
     * @var RouteCollector
     */
    protected $collector;

    public function __construct(Container $dependency)
    {
        $this->dependency = $dependency;
        $this->collector = $this->dependency[self::D_COLLECTOR];
    }

    public function get($route, $callback)
    {
        $this->addRoute('GET', $route, $callback);

        return $this;
    }

    public function post($route, $callback)
    {
        $this->addRoute('POST', $route, $callback);

        return $this;
    }

    public function put($route, $callback)
    {
        $this->addRoute('PUT', $route, $callback);

        return $this;
    }

    public function delete($route, $callback)
    {
        $this->addRoute('DELETE', $route, $callback);

        return $this;
    }

    public function any($route, $callback)
    {
        foreach ($this->methods as $method) {
            $this->addRoute($method, $route, $callback);
        }
        return $this;
    }

    public function addRoute($verb, $route, $callback)
    {
        if (isset($this->dispatcher)) {
            throw new \Exception('cannot add route after dispatch');
        }

        $this->collector->addRoute($verb, $route, $callback);

        return $this;
    }

    public function dispatch(Context $ctx)
    {
        $req = $ctx->getRequest();

        return $this->prepareDispatcher()
            ->dispatch($req->getMethod(), $req->getPathInfo());
    }

    /**
     * @return Dispatcher
     */
    protected function prepareDispatcher()
    {
        if (!isset($this->dispatcher)) {
            $this->dispatcher = $this->dependency[self::D_DISPATCHER_FACTORY]($this->collector->getData());
        }

        return $this->dispatcher;
    }
}
