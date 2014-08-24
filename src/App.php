<?php namespace Aether;

use Aether\Factory\ContainerFactory;
use FastRoute\Dispatcher;
use Pimple\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * User: mcfog
 * Date: 14-8-23
 */

/**
 * represents the app.
 * wraps a \Pimple\Container to maintain dependencies
 *
 * @package Aether
 */
class App
{
    const D_APP = 'aether.app';
    const D_ROUTER = 'aether.router';
    const D_EVENT_DISPATCHER = 'aether.event-dispatcher';
    const D_CONFIG_OB_CONTENT = 'aether.config.ob-content';
    const D_ERROR_LEVEL = 'aether.error_level';

    /**
     * @var Container
     */
    protected $dependency;

    public function __construct(Container $dependency = null)
    {
        if (is_null($dependency)) {
            $dependency = ContainerFactory::makeContainer();
        }

        $this->dependency = $dependency;

        $this->dependency->offsetSet(self::D_APP, $this);
    }

    /**
     * create an app and run it
     *
     * @param Container $dependency
     * @param Context $context
     */
    public static function main(Container $dependency = null, Context $context = null)
    {
        /**
         * @var App $app
         */
        $app = new static($dependency);

        $app->run($context);
    }

    /**
     * run the app
     *
     * @param Context $context
     * @throws \Exception
     */
    public function run(Context $context = null)
    {
        if (is_null($context)) {
            $context = Context::current($this);
        }

        set_exception_handler(
            function (\Exception $exception) use ($context) {
                $context->triggerEvent(Event::INTERNAL_ERROR, $exception);
                $context->getApp()->output($context);
            }
        );
        set_error_handler(
            function ($errno, $errstr, $errfile, $errline) {
                throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
            },
            $this->get(self::D_ERROR_LEVEL)
        );

        $context->triggerEvent(Event::BEFORE_DISPATCH);

        $routeInfo = $this->getRouter()->dispatch($context);

        if ($this->get(App::D_CONFIG_OB_CONTENT)) {
            ob_start();
        }

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $context->triggerEvent(Event::NOT_FOUND);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $context->triggerEvent(Event::ACCESS_DENY);
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                if (!is_callable($handler)) {
                    throw new \Exception("route not callable");
                } else {
                    $context->triggerEvent(Event::BEFORE_LOGIC);
                    $handler($context, $vars);
                    $context->triggerEvent(Event::AFTER_LOGIC);
                }
                break;
        }

        $this->output($context);
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->get(self::D_ROUTER);
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->get(self::D_EVENT_DISPATCHER);
    }

    /**
     * get a dependency entry
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->dependency->offsetGet($key);
    }

    /**
     * @return Container
     */
    public function getDependency()
    {
        return $this->dependency;
    }

    /**
     * @param Context $context
     */
    protected function output(Context $context)
    {
        if ($this->get(App::D_CONFIG_OB_CONTENT)) {
            $content = ob_get_clean();
            if (!empty($content)) {
                $context->getResponse()->setContent($content);
            }
        }

        $context->triggerEvent(Event::BEFORE_OUTPUT);

        $context->getResponse()
            ->prepare($context->getRequest())
            ->send();

        restore_exception_handler();
        restore_error_handler();

        $context->triggerEvent(Event::BEFORE_END);
    }
}
