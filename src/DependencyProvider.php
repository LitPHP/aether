<?php namespace Aether;

use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;

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
            $eventDispatcher = new EventDispatcher();
            $eventDispatcher->addListener(
                Event::ACCESS_DENY,
                function (Event $event) {
                    $event->getContext()->getResponse()
                        ->setStatusCode(Response::HTTP_FORBIDDEN)
                        ->setContent('access denied');

                    $event->stopPropagation();
                },
                -0xFFFF
            );

            $eventDispatcher->addListener(
                Event::NOT_FOUND,
                function (Event $event) {
                    $event->getContext()->getResponse()
                        ->setStatusCode(Response::HTTP_NOT_FOUND)
                        ->setContent('not found');

                    $event->stopPropagation();
                },
                -0xFFFF
            );

            $eventDispatcher->addListener(
                Event::INTERNAL_ERROR,
                function (Event $event) {
                    $event->getContext()->getResponse()
                        ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)
                        ->setContent('error');

                    $event->stopPropagation();
                },
                -0xFFFF
            );

            return $eventDispatcher;
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
