<?php namespace Aether\Factory;

use Aether\App;
use Aether\Event;
use Aether\Router;
use Aether\View\LayoutView;
use Aether\View\TemplateView;
use FastRoute\DataGenerator\GroupCountBased as GCBGenerator;
use FastRoute\Dispatcher\GroupCountBased as GCBDispatcher;
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
class ContainerFactory implements ServiceProviderInterface
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
        $pimple[App::D_ERROR_LEVEL] = E_ALL | E_STRICT;

        $pimple[App::D_EVENT_DISPATCHER] = function () {
            return new EventDispatcher();
        };
        $this->addDefaultListener($pimple);

        $pimple[TemplateView::D_TPL_EXTENSION] = '.phtml';
        $pimple[LayoutView::D_LAYOUT_DEFAULT] = 'layout/default';

        $this->registerRouter($pimple);
    }

    protected function addDefaultListener(Container $pimple)
    {
        $pimple->extend(
            App::D_EVENT_DISPATCHER,
            function (EventDispatcher $eventDispatcher) {
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
            }
        );
    }

    /**
     * @param Container $pimple
     */
    protected function registerRouter(Container $pimple)
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
            return new GCBGenerator();
        };

        $pimple[Router::D_DISPATCHER_FACTORY] = $pimple->protect(
            function ($data) {
                return new GCBDispatcher($data);
            }
        );
    }
}
