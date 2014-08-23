<?php namespace Aether;

use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * User: mcfog
 * Date: 14-8-23
 */
class DependencyProvider implements ServiceProviderInterface
{
    public static function makeContainer(array $extra = array())
    {
        $ctn = new Container();
        $ctn->register(new static(), $extra);

        return $ctn;
    }

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple An Container instance
     */
    public function register(Container $pimple)
    {
        $pimple[App::D_CONFIG_OB_CONTENT] = true;

        $pimple[App::D_EVENT_DISPATCHER] = function () {
            return new EventDispatcher();
        };

        $this->register_router($pimple);
    }

    /**
     * @param Container $pimple
     */
    protected function register_router(Container $pimple)
    {
        $pimple[App::D_ROUTER] = function (Container $dep) {
            return new Router($dep);
        };

        $pimple[Router::D_PARSER] = function () {
            return new Std();
        };

        $pimple[Router::D_COLLECTOR] = function (Container $dep) {
            return new RouteCollector($dep[Router::D_PARSER], $dep[Router::D_GENERATOR]);
        };

        $pimple[Router::D_GENERATOR] = function () {
            return new \FastRoute\DataGenerator\GroupCountBased();
        };

        $pimple[Router::D_DISPATCHER_FACTORY] = $pimple->protect(
            function ($data) {
                return new \FastRoute\Dispatcher\GroupCountBased($data);
            }
        );
    }
}
